<?php

namespace App\Services;

use App\Jobs\LogPaymentJob;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/**
 * سرویس ثبت لاگ پرداخت — به صورت غیرهمزمان (Queue) اجرا می‌شود.
 *
 * هر درخواست/پاسخ به درگاه‌های پرداخت ثبت می‌شود تا در صورت اختلال،
 * بتوان دقیقاً فهمید چه اتفاقی افتاده.
 */
class PaymentLogService
{
    /**
     * ثبت لاگ درخواست توکن از درگاه پرداخت.
     *
     * @param  Order  $order
     * @param  string  $gateway  zarinpal, saman, sadad, zibal
     * @param  array  $payload  request payload (no sensitive data)
     * @param  array  $response  gateway response
     * @param  int  $responseTimeMs  response time in milliseconds
     * @param  string  $status  success, failed
     */
    public static function logTokenRequest(
        Order $order,
        string $gateway,
        array $payload,
        array $response,
        int $responseTimeMs,
        string $status = 'success'
    ): void {
        self::dispatch([
            'order_id'          => $order->id,
            'gateway'           => $gateway,
            'action'            => 'token_request',
            'status'            => $status,
            'request_payload'   => self::sanitizePayload($payload),
            'response_payload'  => self::sanitizeResponse($response),
            'amount'            => $payload['amount'] ?? null,
            'ip'                => Request::ip(),
            'response_time_ms'  => $responseTimeMs,
        ]);
    }

    /**
     * ثبت لاگ تأیید تراکنش.
     *
     * @param  Order  $order
     * @param  string  $gateway
     * @param  array  $callbackData  data from gateway callback
     * @param  array  $response  verify response
     * @param  int  $responseTimeMs
     * @param  string  $status  success, failed
     * @param  string|null  $referenceId  RefID from gateway
     * @param  string|null  $errorMessage
     */
    public static function logVerify(
        Order $order,
        string $gateway,
        array $callbackData,
        array $response,
        int $responseTimeMs,
        string $status = 'success',
        ?string $referenceId = null,
        ?string $errorMessage = null
    ): void {
        self::dispatch([
            'order_id'          => $order->id,
            'gateway'           => $gateway,
            'action'            => 'verify',
            'status'            => $status,
            'request_payload'   => ['authority' => $callbackData['Authority'] ?? ''],
            'response_payload'  => self::sanitizeResponse($response),
            'reference_id'      => $referenceId,
            'error_message'     => $errorMessage,
            'amount'            => $callbackData['Amount'] ?? null,
            'ip'                => Request::ip(),
            'response_time_ms'  => $responseTimeMs,
        ]);
    }

    /**
     * Dispatch logging job to queue (async).
     */
    private static function dispatch(array $data): void
    {
        try {
            if (config('monitoring.log_payments', true)) {
                LogPaymentJob::dispatch($data);
            }
        } catch (\Throwable $e) {
            // If queue dispatch fails, log to file as fallback
            Log::error('PaymentLog dispatch failed', [
                'data'    => $data,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * حذف اطلاعات حساس از payload قبل از ذخیره.
     */
    private static function sanitizePayload(array $payload): array
    {
        $sensitive = ['card_number', 'password', 'cvv2', 'expiry_date'];

        return collect($payload)
            ->except($sensitive)
            ->toArray();
    }

    /**
     * پاکسازی response — حذف اطلاعات حساس.
     */
    private static function sanitizeResponse(array $response): array
    {
        // Truncate long values to prevent DB bloat
        return collect($response)
            ->map(function ($value) {
                if (is_string($value) && mb_strlen($value) > 500) {
                    return mb_substr($value, 0, 500) . '…';
                }

                return $value;
            })
            ->toArray();
    }
}
