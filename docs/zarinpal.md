# درگاه پرداخت زرین‌پال (REST v4)

مرجع رسمی: https://www.zarinpal.com/docs/paymentGateway/connectToGateway

## قرارداد واحد پولی

- مبالغ فروشگاه (`payment_price`) به **تومان** ذخیره می‌شوند.
- هنگام ارسال به زرین‌پال، مبلغ **×۱۰** و به **ریال** همراه با `currency=IRR` ارسال می‌شود.
- حداقل مبلغ درگاه **۱۰٬۰۰۰ ریال** است.
- تفاوت `IRR` (ریال) و `IRT` (تومان): ما همیشه `IRR` می‌فرستیم تا تبدیلی سمت زرین‌پال رخ ندهد.

## تنظیمات (`.env`)

```env
ZARINPAL_MERCHANT_ID="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
ZARINPAL_SANDBOX=false
ZARINPAL_CURRENCY=IRR
ZARINPAL_SANDBOX_MERCHANT_ID="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
```

- اولویت مرچنت‌کد: `services.zarinpal.merchant_id` (از `.env`) ← فالبک `MerchantId` داخل `config` رکورد بانک.
- در حالت `sandbox=true` اندپوینت‌ها به `sandbox.zarinpal.com` می‌روند و Authority با `S` شروع می‌شود.

## فلو پرداخت

1. `OrderService::checkout` → `BankPaymentService::handlePayment` → `ZarinPal::getBankToken`
   - Payload: `merchant_id, amount (ریال), currency, callback_url, description, metadata{mobile, email, order_id}`.
   - `Authority` + مبلغ درخواستی در `orders.transaction_info.post` ذخیره و کاربر با `redirect()->away(.../pg/StartPay/{authority})` به درگاه می‌رود.
2. بازگشت به `checkout.finish.zarin-pal` با `?Authority=...&Status=OK|NOK`:
   - `Status=NOK` → لغو توسط کاربر؛ **verify صدا زده نمی‌شود** (طبق مستندات).
   - `Status=OK` → `ZarinPal::verifyTransaction` با **مبلغ خوانده‌شده از سفارش** (نه کال‌بک).
   - کد `100` موفق جدید، `101` موفقِ تکراری (idempotent — هر دو success).
3. نتیجه در `transaction_info.verify` ذخیره می‌شود (`RefID, CardPan, CardHash, Fee`) و پیام دقیق خطا در `ErrorMessage`.

## کدهای خطای مهم

| کد | معنی |
|----|------|
| 100 | موفق |
| 101 | قبلاً تأیید شده (موفق) |
| ‎-9 | پارامتر نامعتبر |
| ‎-11 | مرچنت یافت نشد (`ZARINPAL_MERCHANT_ID` را چک کنید) |
| ‎-17 | مبلغ کمتر از حداقل مجاز |
| ‎-50 | مغایرت مبلغ |
| ‎-51 | پرداخت ناموفق |
| ‎-53 | تراکنش یافت نشد/منقضی |

مپ کامل: `app/Payments/ZarinPalErrorMapper.php`.

## تست

```powershell
php artisan test --filter=ZarinPalGatewayTest
```
