<?php

namespace App\Helpers;

use App\Services\SmsLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

/**
 * درگاه ارسال پیامک کاوه‌نگار (Kavenegar).
 *
 * لایه‌ی نازکی روی REST API کاوه‌نگار؛ کلید API و شماره‌ی ارسال‌کننده را از
 * تنظیمات سایت (جدول settings) می‌خواند. در محیط local همه‌ی پیامک‌ها به
 * شماره‌ی تست ارسال می‌شوند تا هزینه و اسپم واقعی نداشته باشیم.
 *
 * متدها:
 *  - sendLookup(): ارسال الگودار (OTP و اطلاع‌رسانی‌های قالب‌دار)
 *  - sendSms():    ارسال متن آزاد تکی
 *  - sendSmsArray(): ارسال گروهی (تا ۲۰۰ پیام در هر درخواست)
 *
 * منبع: documents/features-for-run/kavenegar-integration.md (بخش ۱۲)
 */
class Sms
{
    /** @var string|null کلید API کاوه‌نگار */
    private ?string $token = null;

    /** @var string آدرس پایه‌ی API کاوه‌نگار */
    private string $baseUrl;

    /** @var string|null شماره‌ی خط خدماتی (ارسال‌کننده) */
    private ?string $sender = null;

    public function __construct()
    {
        $this->baseUrl = config('kavenegar.base_url', 'https://api.kavenegar.com/v1');
        $this->token = $this->getSetting('kavenegar_key');
        $this->sender = $this->getSetting('kavenegar_sender');
    }

    /**
     * مقدار یک تنظیم را می‌خواند (جدول settings از طریق هلپر setting()).
     *
     * @param  string  $key  نام کلید تنظیم
     */
    private function getSetting(string $key): ?string
    {
        $value = setting($key);

        return $value === '' || $value === null ? null : (string) $value;
    }

    /**
     * اجرای درخواست GET به API کاوه‌نگار.
     *
     * @param  string  $url  انتهای آدرس (بعد از token)، مثلاً "verify/lookup.json?..."
     * @return string|false|RedirectResponse بدنه‌ی پاسخ JSON؛
     *                                       در نبود کلید API یک RedirectResponse خطا، در خطای شبکه false
     */
    public function execute(string $url)
    {
        if (! isset($this->token)) {
            return Redirect::back()->with('error', 'لطفا با پشتیبانی تماس بگیرید ');
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "$this->baseUrl/$this->token/$url",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            Log::error('cURL error ('.curl_error($curl).") during request to {$url}");
        }

        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($http_status != 200) {
            Log::error("HTTP status {$http_status} received from {$url}");
        }

        curl_close($curl);

        if ($response === false) {
            Log::error("Failed to get response from {$url}");

            return false;
        }

        return $response;
    }

    /**
     * ارسال پیامک الگودار (verify/lookup).
     *
     * برای الگوهای چندپارامتری از کلیدهای token، token2، token3 استفاده کنید:
     * ['token' => $a, 'token2' => $b]
     *
     * در محیط local گیرنده به LOCAL_MOBILE جایگزین می‌شود؛ اگر LOCAL_MOBILE
     * ست نشده باشد هیچ پیامکی ارسال نمی‌شود (از ارسال به شماره‌ی واقعی جلوگیری می‌شود).
     *
     * @param  string  $template  نام الگوی تعریف‌شده در پنل کاوه‌نگار
     * @param  array  $tokens  پارامترهای الگو (token، token2، ...)
     * @param  string  $mobile  شماره‌ی گیرنده
     * @param  string  $type  نوع ارسال: sms (پیش‌فرض) | call
     * @return string|false|RedirectResponse
     */
    public function sendLookup(string $template, array $tokens, string $mobile, string $type = 'sms')
    {
        $tokens_query = http_build_query($tokens);
        $originalMobile = $mobile;

        if (app()->environment('local')) {
            $localMobile = config('kavenegar.local_mobile');
            if (! $localMobile) {
                Log::info("Kavenegar lookup skipped in local (LOCAL_MOBILE not set) for {$mobile}");
                SmsLogService::recordSkipped('otp', $originalMobile, $template);
                return false;
            }
            $mobile = $localMobile;
        }

        $url = "verify/lookup.json?receptor=$mobile&type=$type&template=$template&$tokens_query";

        try {
            $result = $this->execute($url);
            if ($result === false) {
                SmsLogService::recordFailure('otp', $originalMobile, 'API request failed', $template);
            } else {
                SmsLogService::recordSuccess('otp', $originalMobile, $template);
            }
            return $result;
        } catch (\Exception $e) {
            Log::info($e);
            SmsLogService::recordFailure('otp', $originalMobile, $e->getMessage(), $template);
            return false;
        }
    }

    /**
     * ارسال پیامک متن آزاد تکی (sms/send).
     *
     * @param  string  $text  متن پیامک
     * @param  string  $mobile  شماره‌ی گیرنده
     * @return array{success: bool, message?: string} وضعیت ارسال
     */
    public function sendSms(string $text, string $mobile)
    {
        $testNumber = config('kavenegar.test_number');
        $message = urlencode($text);

        if (app()->environment('local')) {
            $receptor = $testNumber;
        } else {
            $receptor = $mobile;
        }

        $url = "sms/send.json?receptor=$receptor&message=$message&sender={$this->sender}";

        try {
            $res = $this->execute($url);
            $res_json = json_decode($res);

            if (isset($res_json->return) && $res_json->return->status == 200) {
                SmsLogService::recordSuccess('notification', $mobile);
                return ['success' => true];
            }

            $error_message = isset($res_json->return->message) ? $res_json->return->message : 'Unknown error';
            SmsLogService::recordFailure('notification', $mobile, $error_message);
            return ['success' => false, 'message' => $error_message];
        } catch (\Exception $e) {
            Log::error('Exception during sending SMS: '.$e->getMessage());
            SmsLogService::recordFailure('notification', $mobile, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * ارسال گروهی پیامک (sms/sendarray).
     *
     * محدودیت: حداکثر ۲۰۰ پیام در هر درخواست — برای تعداد بیشتر خودتان chunk کنید.
     *
     * @param  array<int, array{receptor: string, message: string}>  $messages  لیست پیام‌ها
     * @return array{success: bool, entries?: array, message?: string, status_code?: int}
     */
    public function sendSmsArray(array $messages)
    {
        if (empty($messages)) {
            return ['success' => false, 'message' => 'Empty data'];
        }

        $receptors = [];
        $senders = [];
        $texts = [];

        $isLocal = app()->environment('local');
        $testNumber = config('kavenegar.test_number');

        foreach ($messages as $row) {
            $receptors[] = $isLocal ? $testNumber : $row['receptor'];
            $texts[] = $row['message'];
            $senders[] = $this->sender;
        }

        // طبق مستندات: پارامترها باید آرایه‌ای به فرمت JSON باشند
        $postData = [
            'receptor' => json_encode($receptors),
            'sender' => json_encode($senders),
            'message' => json_encode($texts),
        ];

        $res = $this->executePost('sms/sendarray.json', $postData);
        if (! $res) {
            return ['success' => false, 'message' => 'Connection Error'];
        }

        $json = json_decode($res, true);

        if (isset($json['return']) && $json['return']['status'] == 200) {
            return ['success' => true, 'entries' => $json['entries'] ?? []];
        }

        return [
            'success' => false,
            'message' => $json['return']['message'] ?? 'API error',
            'status_code' => $json['return']['status'] ?? null,
        ];
    }

    /**
     * اجرای درخواست POST به API کاوه‌نگار (بدنه به‌صورت form-urlencoded).
     *
     * @param  string  $url  انتهای آدرس (بعد از token)
     * @param  array|null  $postData  داده‌های فرم؛ اگر null باشد درخواست GET است
     * @return string|false بدنه‌ی پاسخ JSON یا false در خطای شبکه
     */
    public function executePost(string $url, $postData = null)
    {
        if (! $this->token) {
            Log::error('Sms Helper: Token is missing.');

            return false;
        }

        $curl = curl_init();
        $options = [
            CURLOPT_URL => "$this->baseUrl/$this->token/$url",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 15,
        ];

        if ($postData) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($postData);
        }

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($error) {
            Log::error("Kavenegar cURL Error: {$error} | URL: {$url}");

            return false;
        }

        return $response;
    }

    /**
     * ارسال گروهی برای تست با ارسال‌کننده‌ی دلخواه (اعتبارسنجی خط خدماتی).
     *
     * برخلاف sendSmsArray گیرنده‌ها در لوکال جایگزین نمی‌شوند؛
     * از شماره‌ی واقعی ارسال‌شده (kavenegar_number_test) استفاده می‌کند.
     *
     * @param  array<int, array{receptor: string, message: string}>  $messages
     * @param  string|null  $overrideSender  شماره‌ی ارسال‌کننده‌ی جایگزین
     * @return array{success: bool, message?: string, status_code?: int}
     */
    public function sendSmsArrayTest(array $messages, ?string $overrideSender = null): array
    {
        if (empty($messages)) {
            return ['success' => false, 'message' => 'Empty data'];
        }

        $receptors = [];
        $senders = [];
        $texts = [];

        foreach ($messages as $row) {
            $receptors[] = $row['receptor'];
            $texts[] = $row['message'];
            $senders[] = $overrideSender ?: $this->sender;
        }

        $postData = [
            'receptor' => json_encode($receptors),
            'sender' => json_encode($senders),
            'message' => json_encode($texts),
        ];

        $res = $this->executePost('sms/sendarray.json', $postData);
        if (! $res) {
            return ['success' => false, 'message' => 'Connection Error'];
        }

        $json = json_decode($res, true);

        if (isset($json['return']) && $json['return']['status'] == 200) {
            return ['success' => true];
        }

        return [
            'success' => false,
            'message' => $json['return']['message'] ?? 'API error',
            'status_code' => $json['return']['status'] ?? null,
        ];
    }
}
