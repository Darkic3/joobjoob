# Kavenegar SMS Integration

> **Source:** `app/Helpers/Sms.php`, `config/kavenegar.php`, `app/Services/OtpService.php`

## Config
- `config/kavenegar.php`: base_url `https://api.kavenegar.com/v1`, local_mobile, test_number, otp_ttl_minutes=2
- Settings: `kavenegar_key` (token), `kavenegar_sender` (sender line), `otp_template` (default verify)
- `.env`: KAVENEGAR_BASE_URL, LOCAL_MOBILE, SMS_TEST_NUMBER

## Sms class
- Methods: `sendLookup(template, tokens, mobile, type=sms)`, `sendSms(text,mobile)`, `sendSmsArray(messages)`, `execute(url)` GET, `executePost(url,data)` POST
- `execute` uses curl GET to `$baseUrl/$token/$url` with **CURLOPT_SSL_VERIFYPEER=0 + VERIFYHOST=0** (HIGH risk MITM)
- Local env: if `app()->environment('local')` then receptor replaced by `config(kavenegar.local_mobile)` else skip if not set; sendSms uses test_number.
- Returns: json string on success, false on network error, RedirectResponse if token missing.
- Error logging: Log::error on curl_error or http_status !=200.

## OTP usage
`OtpService::send` -> `sms->sendLookup(template, [token=>code, token2=>cleanSiteName], normalizedMobile)` -> check json return.status===200.

## AI gotcha
- SSL verification disabled — must document but not fix.
- Local override prevents real SMS — test with LOCAL_MOBILE.
- Token from settings table (DB), not env.
