# Kavenegar SMS Integration

> **Source:** `app/Helpers/Sms.php`, `config/kavenegar.php`, `app/Services/OtpService.php`
> **Audit:** 2026-09-07 — Pure Audit, hich code fix nashod.

## Config
- `config/kavenegar.php`: base_url `https://api.kavenegar.com/v1`, local_mobile, test_number, otp_ttl_minutes=2
- Settings: `kavenegar_key` (token), `kavenegar_sender` (sender line), `otp_template` (default verify)
- `.env`: KAVENEGAR_BASE_URL, LOCAL_MOBILE, SMS_TEST_NUMBER
- Token az `setting('kavenegar_key')` (DB), na env — Sms::__construct getSetting()

## Sms class
- Methods: `sendLookup(template, tokens, mobile, type=sms)`, `sendSms(text,mobile)`, `sendSmsArray(messages)`, `execute(url)` GET, `executePost(url,data)` POST
- `execute` uses curl GET to `$baseUrl/$token/$url` with **CURLOPT_SSL_VERIFYPEER=true + VERIFYHOST=2 — FIXED 2026-09-07 (P0-08)** — `Sms.php:77-78`
- Local env: if `app()->environment('local')` then receptor replaced by `config(kavenegar.local_mobile)` else skip if not set; sendSms uses test_number.
- Returns: json string on success, false on network error, RedirectResponse if token missing.
- Error logging: Log::error on curl_error or http_status !=200.

## OTP usage
`OtpService::send` -> `sms->sendLookup(template, [token=>code, token2=>cleanSiteName], normalizedMobile)` -> check json return.status===200.

## P0-08 Fix — SSL (2026-09-07)

**Ghabli (STALE):** `CURLOPT_SSL_VERIFYPEER=0 + VERIFYHOST=0` — HIGH MITM, `threats.md#1` HIGH.

**Feli (FIXED):** `Sms.php:77-78`
```php
CURLOPT_SSL_VERIFYPEER => true,
CURLOPT_SSL_VERIFYHOST => 2,
```
> Chapar hanuz HTTP ast — dar `chapar.md` T11 mostanad shode, tafkik az Sms.

## AI gotcha
- Sms SSL alan FIXED — Chapar hanuz HTTP MITM (chapar.md).
- Local override prevents real SMS — test with LOCAL_MOBILE.
- Token from settings table (DB), not env.
