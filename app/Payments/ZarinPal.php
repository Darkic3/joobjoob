<?php

namespace App\Payments;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZarinPal implements PaymentGateway
{
    private string $merchant;
    private string $siteName;

    private const EP_REQUEST  = 'https://payment.zarinpal.com/pg/v4/payment/request.json';
    private const EP_VERIFY   = 'https://payment.zarinpal.com/pg/v4/payment/verify.json';
    private const EP_REDIRECT = 'https://payment.zarinpal.com/pg/StartPay/';
    private const CALLBACK_ROUTE_NAME = 'checkout.finish.zarin-pal';
    private const USER_AGENT = 'ZarinPal Rest Api v1';

    public function __construct($bank, string $siteName = '')
    {
        $config = is_array($bank->config) ? $bank->config : json_decode($bank->config ?? '{}', true);
        $this->merchant = $config['MerchantId'] ?? '';
        $this->siteName = $siteName;
    }

    public function getBankToken(int $amount, Order $order): PaymentPostDTO
    {
        $amountRial = intval($amount . '0');

        $payload = [
            'merchant_id' => $this->merchant,
            'amount' => $amountRial,
            'callback_url' => route(self::CALLBACK_ROUTE_NAME),
            'description' => $this->siteName . ' خرید از ' . $this->siteName,
            'metadata' => [],
        ];

        $resp = $this->sendJsonRequest(self::EP_REQUEST, $payload);

        if (! $resp['ok']) {
            return PaymentPostDTO::fromData('failed');
        }

        $result = $resp['json'];

        if (isset($result['data']['code']) && (int) $result['data']['code'] === 100) {
            return PaymentPostDTO::fromData('success', [
                'Authority' => $result['data']['authority'] ?? null,
            ]);
        }

        return PaymentPostDTO::fromData('failed');
    }

    public function verifyTransaction(array $callbackData, int $price, ?Order $order = null): PaymentVerifyDTO
    {
        $amountRial = intval($price . '0');

        if (isset($callbackData['Status']) && $callbackData['Status'] !== 'OK') {
            return PaymentVerifyDTO::fromData('failed');
        }

        $payload = [
            'merchant_id' => $this->merchant,
            'authority' => $callbackData['Authority'] ?? '',
            'amount' => $amountRial,
        ];

        $resp = $this->sendJsonRequest(self::EP_VERIFY, $payload);

        if (! $resp['ok']) {
            return PaymentVerifyDTO::fromData('failed');
        }

        $result = $resp['json'];

        if (isset($result['data']['code']) && ((int) $result['data']['code'] === 100 || (int) $result['data']['code'] === 101)) {
            return PaymentVerifyDTO::fromData('success', [
                'RefID' => $result['data']['ref_id'] ?? null,
            ]);
        }

        return PaymentVerifyDTO::fromData('failed');
    }

    public function redirect(PaymentPostDTO $postData, Order $order): void
    {
        $authority = $postData->getPostData()['Authority'] ?? '';

        header('Location: ' . self::EP_REDIRECT . $authority);
        exit;
    }

    private function sendJsonRequest(string $url, array $payload = []): array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => self::USER_AGENT,
            ])->timeout(30)->post($url, $payload);

            if ($response->failed()) {
                return ['ok' => false, 'json' => null];
            }

            $json = $response->json();

            return is_array($json) ? ['ok' => true, 'json' => $json] : ['ok' => false, 'json' => null];
        } catch (\Throwable $e) {
            Log::error('ZarinPal request failed', ['message' => $e->getMessage()]);
            return ['ok' => false, 'json' => null];
        }
    }
}
