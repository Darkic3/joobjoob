<?php

namespace App\Payments;

use App\Models\Order;
use App\Services\PaymentLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * درگاه پرداخت زرین‌پال — REST v4.
 *
 * مرجع رسمی: https://www.zarinpal.com/docs/paymentGateway/connectToGateway
 *
 * قرارداد واحد پولی:
 *  - مبالغ فروشگاه (Order::payment_price و پارامترهای ورودی) به «تومان» است.
 *  - زرین‌پال مبلغ را به «ریال» می‌خواهد؛ پس هنگام ارسال ×۱۰ و همراه
 *    با currency=IRR ارسال می‌شود.
 */
class ZarinPal implements PaymentGateway
{
    /** هر تومان = ۱۰ ریال */
    public const TOMAN_TO_RIAL = 10;

    /** حداقل مبلغ مجاز درگاه (ریال) طبق مستندات */
    public const MIN_AMOUNT_RIAL = 10000;

    public const CURRENCY_IRR = 'IRR';

    private const PROD_BASE = 'https://payment.zarinpal.com';

    private const SANDBOX_BASE = 'https://sandbox.zarinpal.com';

    private const CALLBACK_ROUTE_NAME = 'checkout.finish.zarin-pal';

    private const USER_AGENT = 'ZarinPal Rest Api v1';

    private string $merchant;

    private string $siteName;

    private bool $sandbox;

    private string $currency;

    public function __construct($bank, string $siteName = '')
    {
        $bankConfig = is_array($bank->config ?? null)
            ? $bank->config
            : json_decode($bank->config ?? '{}', true) ?? [];

        $this->sandbox = (bool) config('services.zarinpal.sandbox', false);
        $this->currency = (string) config('services.zarinpal.currency', self::CURRENCY_IRR);

        // اولویت: .env ‏(services.zarinpal) ← سپس config قدیمی بانک ← در sandbox مرچنت تستی
        $this->merchant = (string) (
            config('services.zarinpal.merchant_id')
            ?: ($bankConfig['MerchantId'] ?? $bankConfig['merchant_id'] ?? '')
        );

        if ($this->sandbox && $this->merchant === '') {
            $this->merchant = (string) config(
                'services.zarinpal.sandbox_merchant_id',
                'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'
            );
        }

        if ($this->merchant === '') {
            throw new \InvalidArgumentException(
                'ZarinPal merchant_id تنظیم نشده است. مقدار ZARINPAL_MERCHANT_ID را در .env وارد کنید.'
            );
        }

        $this->siteName = $siteName !== '' ? $siteName : (string) config('app.name', 'Shop');
    }

    private function baseUrl(): string
    {
        return $this->sandbox ? self::SANDBOX_BASE : self::PROD_BASE;
    }

    private function requestUrl(): string
    {
        return $this->baseUrl().'/pg/v4/payment/request.json';
    }

    private function verifyUrl(): string
    {
        return $this->baseUrl().'/pg/v4/payment/verify.json';
    }

    public function startPayUrl(string $authority): string
    {
        return $this->baseUrl().'/pg/StartPay/'.$authority;
    }

    public function getRedirectUrl(PaymentPostDTO $postData): string
    {
        return $this->startPayUrl((string) ($postData->getPostData()['Authority'] ?? ''));
    }

    /**
     * @param  int  $amountToman  مبلغ به تومان
     */
    public function getBankToken(int $amount, Order $order): PaymentPostDTO
    {
        $amountRial = self::toRial($amount);

        if ($amount <= 0) {
            Log::warning('ZarinPal token skipped: non-positive amount', ['order_id' => $order->id, 'amount_toman' => $amount]);

            return PaymentPostDTO::fromData('failed', [
                'ErrorCode' => -17,
                'ErrorMessage' => ZarinPalErrorMapper::message(-17),
            ]);
        }

        $payload = [
            'merchant_id' => $this->merchant,
            'amount' => $amountRial,
            'currency' => $this->currency,
            'callback_url' => route(self::CALLBACK_ROUTE_NAME),
            'description' => "سفارش #{$order->id} — خرید از {$this->siteName}",
            'metadata' => $this->buildMetadata($order),
        ];

        $startTime = microtime(true);
        $resp = $this->sendJsonRequest($this->requestUrl(), $payload);
        $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);

        PaymentLogService::logTokenRequest(
            $order,
            'zarinpal',
            $this->logSafePayload($payload),
            $resp['json'] ?? [],
            $responseTimeMs,
            $resp['ok'] ? 'success' : 'failed'
        );

        if (! $resp['ok']) {
            $error = ZarinPalErrorMapper::firstError($resp['json'] ?? []);
            $code = $error['code'] ?? $resp['http_status'] ?? 0;

            return PaymentPostDTO::fromData('failed', [
                'ErrorCode' => $code,
                'ErrorMessage' => $error['message'] ?? ZarinPalErrorMapper::message((int) $code),
            ]);
        }

        $result = $resp['json'];
        $code = (int) ($result['data']['code'] ?? ZarinPalErrorMapper::firstError($result)['code'] ?? -9);

        if ($code === 100 && ! empty($result['data']['authority'])) {
            return PaymentPostDTO::fromData('success', [
                'Authority' => $result['data']['authority'],
                'AmountRial' => $amountRial,
                'AmountToman' => $amount,
                'OrderId' => $order->id,
            ]);
        }

        Log::warning('ZarinPal request rejected', [
            'order_id' => $order->id,
            'code' => $code,
            'response' => $result,
        ]);

        return PaymentPostDTO::fromData('failed', [
            'ErrorCode' => $code,
            'ErrorMessage' => ($result['data']['message'] ?? null)
                ?: (ZarinPalErrorMapper::firstError($result)['message'] ?? null)
                ?: ZarinPalErrorMapper::message($code),
        ]);
    }

    /**
     * @param  int  $price  مبلغ سفارش به تومان (همیشه از دیتابیس/مدل سفارش، نه از کال‌بک)
     */
    public function verifyTransaction(array $callbackData, int $price, ?Order $order = null): PaymentVerifyDTO
    {
        // طبق مستندات: اگر Status برابر OK نباشد (لغو توسط کاربر)، نباید verify صدا زده شود.
        if (($callbackData['Status'] ?? null) !== 'OK') {
            if ($order) {
                PaymentLogService::logVerify(
                    $order, 'zarinpal', $callbackData, [],
                    0, 'failed', null, 'پرداخت توسط کاربر لغو شد.'
                );
            }

            return PaymentVerifyDTO::fromData('failed', [
                'CancelledByUser' => true,
                'ErrorMessage' => 'پرداخت توسط شما لغو شد.',
            ]);
        }

        $amountRial = self::toRial($price);
        $authority = (string) ($callbackData['Authority'] ?? '');

        if ($authority === '' || $price <= 0) {
            return PaymentVerifyDTO::fromData('failed', [
                'ErrorCode' => -53,
                'ErrorMessage' => ZarinPalErrorMapper::message(-53),
            ]);
        }

        $payload = [
            'merchant_id' => $this->merchant,
            'authority' => $authority,
            'amount' => $amountRial,
        ];

        $startTime = microtime(true);
        $resp = $this->sendJsonRequest($this->verifyUrl(), $payload);
        $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);

        $referenceId = null;
        $errorMessage = null;
        $verifyData = [];
        $status = 'failed';

        if ($resp['ok']) {
            $data = $resp['json']['data'] ?? [];
            $code = (int) ($data['code'] ?? -52);

            if ($code === 100 || $code === 101) {
                $status = 'success';
                $referenceId = isset($data['ref_id']) ? (string) $data['ref_id'] : null;
                $verifyData = [
                    'RefID' => $referenceId,
                    'Authority' => $authority,
                    'AmountRial' => $amountRial,
                    'CardPan' => $data['card_pan'] ?? null,
                    'CardHash' => $data['card_hash'] ?? null,
                    'FeeType' => $data['fee_type'] ?? null,
                    'Fee' => $data['fee'] ?? null,
                    'AlreadyVerified' => $code === 101,
                    'GatewayCode' => $code,
                ];
            } else {
                $errorMessage = ($data['message'] ?? null) ?: ZarinPalErrorMapper::message($code);
                $verifyData = [
                    'Authority' => $authority,
                    'ErrorCode' => $code,
                    'ErrorMessage' => $errorMessage,
                ];
            }
        } else {
            $error = ZarinPalErrorMapper::firstError($resp['json'] ?? []);
            $code = (int) ($error['code'] ?? -52);
            $errorMessage = $error['message'] ?? ZarinPalErrorMapper::message($code);
            $verifyData = [
                'Authority' => $authority,
                'ErrorCode' => $code,
                'ErrorMessage' => $errorMessage,
            ];
        }

        if ($order) {
            PaymentLogService::logVerify(
                $order, 'zarinpal', $callbackData, $resp['json'] ?? [],
                $responseTimeMs, $status, $referenceId, $errorMessage
            );
        }

        return PaymentVerifyDTO::fromData($status, $verifyData);
    }

    public function redirect(PaymentPostDTO $postData, Order $order): RedirectResponse
    {
        $url = $this->getRedirectUrl($postData);

        if (($postData->getPostData()['Authority'] ?? '') === '') {
            throw new \RuntimeException('ZarinPal Authority خالی است؛ هدایت به درگاه ممکن نیست.');
        }

        return redirect()->away($url);
    }

    /**
     * تبدیل تومان به ریال (صریح و امن در برابر مقادیر منفی).
     */
    public static function toRial(int $amountToman): int
    {
        if ($amountToman <= 0) {
            return 0;
        }

        return $amountToman * self::TOMAN_TO_RIAL;
    }

    /**
     * متادیتای خریدار طبق مستندات (mobile / email / order_id).
     */
    private function buildMetadata(Order $order): array
    {
        $user = $order->user;
        $address = $order->location;

        $mobile = $address->receiptor_mobile ?? $user->mobile ?? $user->phone ?? null;
        $email = $user->email ?? null;

        $metadata = ['order_id' => (string) $order->id];

        if (is_string($mobile) && $mobile !== '') {
            $metadata['mobile'] = $mobile;
        }

        if (is_string($email) && $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $metadata['email'] = $email;
        }

        return $metadata;
    }

    /**
     * حذف مرچنت‌کد از payload قبل از ثبت در لاگ دیتابیسی.
     */
    private function logSafePayload(array $payload): array
    {
        $safe = $payload;
        unset($safe['merchant_id']);

        return $safe;
    }

    private function sendJsonRequest(string $url, array $payload = []): array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent' => self::USER_AGENT,
            ])->timeout(30)->post($url, $payload);

            $json = $response->json();

            return [
                'ok' => $response->successful() && is_array($json),
                'json' => is_array($json) ? $json : null,
                'http_status' => $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::error('ZarinPal request failed', ['url' => $url, 'message' => $e->getMessage()]);

            return ['ok' => false, 'json' => null, 'http_status' => 0];
        }
    }
}
