<?php

namespace App\Payments;

/**
 * نگاشت کدهای خطای زرین‌پال به پیام فارسی.
 *
 * مرجع: https://www.zarinpal.com/docs/paymentGateway/connectToGateway
 * کد 100 = موفق، 101 = قبلاً وریفای شده (موفق).
 * برای کدهای پوشش‌داده‌نشده، پیام عمومی همراه با خودِ کد برمی‌گردد تا
 * همیشه قابل ردیابی باشد (به‌جای حدس زدن).
 */
class ZarinPalErrorMapper
{
    /**
     * @return array{fa: string, retryable: bool}
     */
    public static function describe(int $code): array
    {
        return match ($code) {
            100 => ['fa' => 'پرداخت با موفقیت تأیید شد.', 'retryable' => false],
            101 => ['fa' => 'این تراکنش قبلاً تأیید شده است.', 'retryable' => false],

            // ── خطاهای درخواست (request) ──
            -9 => ['fa' => 'اطلاعات ارسالی به درگاه معتبر نیست (مبلغ، مرچنت‌کد یا آدرس بازگشت را بررسی کنید).', 'retryable' => false],
            -10 => ['fa' => 'مرچنت‌کد معلق شده یا آی‌پی سرور با پنل زرین‌پال مغایرت دارد.', 'retryable' => false],
            -11 => ['fa' => 'مرچنت‌کد یافت نشد؛ مقدار ZARINPAL_MERCHANT_ID را بررسی کنید.', 'retryable' => false],
            -12 => ['fa' => 'حساب پذیرنده فعال نیست؛ وضعیت درگاه را در پنل زرین‌پال بررسی کنید.', 'retryable' => false],
            -15 => ['fa' => 'ترمینال پرداخت معلق شده است.', 'retryable' => false],
            -16 => ['fa' => 'سطح حساب پذیرنده برای این تراکنش کافی نیست.', 'retryable' => false],
            -17 => ['fa' => 'مبلغ کمتر از حداقل مجاز درگاه است (حداقل ۱۰٬۰۰۰ ریال).', 'retryable' => false],
            -18 => ['fa' => 'آی‌پی سرور با آی‌پی ثبت‌شده در پنل زرین‌پال مغایرت دارد.', 'retryable' => false],

            // ── خطاهای تأیید (verify) ──
            -50 => ['fa' => 'مبلغ تراکنش با مبلغ پرداخت‌شده مغایرت دارد.', 'retryable' => false],
            -51 => ['fa' => 'پرداخت ناموفق بود (توسط بانک تأیید نشد).', 'retryable' => true],
            -52 => ['fa' => 'خطای غیرمنتظره از سمت بانک رخ داد؛ لطفاً دوباره تلاش کنید.', 'retryable' => true],
            -53 => ['fa' => 'تراکنش یافت نشد یا منقضی شده است (Authority نامعتبر).', 'retryable' => false],
            -55 => ['fa' => 'تراکنش یافت نشد.', 'retryable' => false],

            default => ['fa' => "خطای درگاه زرین‌پال (کد {$code}).", 'retryable' => true],
        };
    }

    public static function message(int $code): string
    {
        return self::describe($code)['fa'];
    }

    public static function isRetryable(int $code): bool
    {
        return self::describe($code)['retryable'];
    }

    /**
     * استخراج اولین خطا از آرایه errors پاسخ زرین‌پال.
     *
     * @return array{code: int|null, message: string|null}
     */
    public static function firstError(array $response): array
    {
        $errors = $response['errors'] ?? [];

        if (is_array($errors) && $errors !== []) {
            $first = reset($errors);
            if (is_array($first)) {
                return [
                    'code' => isset($first['code']) ? (int) $first['code'] : null,
                    'message' => $first['message'] ?? null,
                ];
            }
        }

        return ['code' => null, 'message' => null];
    }
}
