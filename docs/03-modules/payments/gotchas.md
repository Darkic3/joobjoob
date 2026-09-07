# Payments / Banks — Gotchas & Known Bugs

> **Scope:** payments module gotchas, tampering risks, data-loss bugs, ZarinPal quirks
> **Source:** `app/Payments/ZarinPal.php`, `app/Services/BankPaymentService.php`, `app/Services/OrderService.php`, `app/Models/Bank.php`, `app/Http/Controllers/Admin/BankController.php`, `routes/site.php`

---

## 1. Amount string concat — toman→rial `intval($amount.'0')` both phases

- `ZarinPal::getBankToken` line 29: `$amountRial = intval($amount . '0');`
- `ZarinPal::verifyTransaction` line 58: `$amountRial = intval($price . '0');`
- Both use **string concatenation** not arithmetic `*10`. Reason: avoid float but relies on `$amount` being clean int string. If `price` contains commas (`"1,200"`) then `"1,2000"` → `intval` yields `1` — wildly wrong. Callers sanitize via `str_replace(',','',payment_price)` → `intval`, but direct `ZarinPal` usage with formatted price would instant amount bug. No type guard — `$amount` is `int` typed, so comma case only via indirect string cast.

**Fix:** use `intval($amount) * 10` or `$amount * 10` with explicit `int` multiplication, validate `is_int`.

---

## 2. Hard (soft) delete on failed token — data loss

```php
// BankPaymentService.php:26
if ($response->getStatus()==='failed'){ $order->delete(); return redirect(checkout.failed); }
```

- Order and items remain soft-deleted but vanish from UI (`admin/orders.index` excludes trashed, dashboard hides). Customer loses cart history, no audit `AuditService` log, `OrderObserver` not triggered for history? Stock already reserved (reserveStock) — if order deleted, reserved stock not restored (only `handleFailedPayment` restores, but this path deletes without restore). Stock over-reserved.
- `delete()` with `SoftDeletes` is soft, but spec calls "hard delete" — semantically data loss because soft-deleted orders not surfaced; `forceDelete` in `OrderService::create` stock fail path is truly hard.
- Better: `update(['order_status'=>'unpaid'])` + `restoreStock` + audit + notify, not delete.

---

## 3. MerchantId missing → silent loop to delete

- `ZarinPal::__construct` does `$this->merchant = $config['MerchantId'] ?? ''` — empty string valid.
- `getBankToken` sends `merchant_id: ''` → ZarinPal returns `code !=100` → DTO failed → order deleted → user sees `خطا در پرداخت`. No validation in `BankController::store` or `BankRequest` for `MerchantId` presence; `config` is nullable string, not validated json.
- Admin can create `zarinPal` bank with `config = {}` or `{"merchantId":"..."}` typo (lowercase) → every payment insta-fails+deletes.

**Mitigate:** `BankRequest` rule `config` should have custom `json` + `RequiredIf bank_type zarinPal` + check `MerchantId` exists.

---

## 4. Ssl ok via Http — no insecure flag

- `ZarinPal::sendJsonRequest` uses `Http::withHeaders(...)->timeout(30)->post(...)` with default SSL verify true. Correct — ZarinPal cert verified. Some legacy code used `Http::withoutVerifying()` (not here). Do not add `withoutVerifying` or `verify=>false`.

---

## 5. card_to_card no gateway — Factory dead code

- `PaymentGatewayFactory::create` maps `card_to_card => new ZarinPal($bank)` (dead code). Intended behavior is no gateway; `OrderService::checkout` handles `card_to_card` **before** calling Factory → Factory dead path never hit.
- Risk: future caller (job, artisan, alternative checkout) calling `Factory::create(card_to_card bank)` would erroneously attempt ZarinPal with empty merchant and mis-config. Factory should `throw` for card_to_card or return NullGateway.

---

## 6. Authority lookup via JSON path — DB compatibility

- Callback uses `Order::where('transaction_info->post->Authority', $value)` (JSON_EXTRACT). Works on MySQL 5.7+ with `json` column. Tests using SQLite in-memory — SQLite does not support `->` JSON path same way (uses `json_extract` but syntax differs); `findByField` will 0 rows → `فاکتور معتبر نمی باشد` false negative. No index on `transaction_info->post->Authority` — full table scan if many orders, slow.

---

## 7. Order price calc duplicated & fragile

- `OrderService::checkout` and `OrderService::callback` both recompute `price = intval(str_replace(',','',deposit_price)) ?: intval(str_replace(',','',payment_price))`. `deposit_price` fallback suggests partial payment, but `deposit_price` is often `0` or `"0"` string. `intval("0") ===0` triggers fallback to `payment_price` correct, but `intval("1,200")` after str_replace is fine, `intval("1200.50")` truncates decimal lose 50 toman. No validation price >0 before gateway.

---

## 8. `bank->bank_type` checked with `===` but BankRequest allows free string

- `BankRequest` `bank_type => required|string|max:100` — no `in:zarinPal,card_to_card`. Admin typo `zarinpal` lowercase p → `Factory match` default throws `InvalidArgumentException` uncaught → 500 at checkout. Should be `in:zarinPal,card_to_card`.

---

## 9. Icon delete order — old icon deleted before new upload succeeds

- `BankController::update` deletes old icon `ImageHandler::delete('bank', $bank->icon)` before `handleImageUpload` succeeds. If new file invalid/upload throws, bank record ends with `icon = null` (because exception) and old file already deleted — loss. Safer: upload first, then delete old on success.

---

## 10. `config` json_decode silently fails

- `BankController::store/update` does `$validated['config'] = $validated['config'] ? json_decode(...) : null;` — `json_decode` with invalid JSON returns null with no error check. Stores null MerchantId — same as #3. Should check `json_last_error()` or `json_validate`.

---

## 11. Redirect via `header()+exit` breaks testing & middleware

- `ZarinPal::redirect` does `header('Location: ...'); exit;` — bypasses Laravel's response lifecycle, middleware `log.admin`, `SetLocale`, session flash cannot run after `exit`. Testing via `php artisan test` expects `RedirectResponse` but gets `exit` → test process dies. In `BankPaymentService` the `ob_start()` wrapper is ineffective due to `exit`. Should `return redirect()->away(EP_REDIRECT.Authority)`.

---

## 12. No idempotency guard before `handleVerify` re-calls `verify.json`

- `OrderService::callback` checks `if paid/deposit_paid → redirect success` before handleVerify, but `handleVerify` itself updates `transaction_info.verify` even on failure then `OrderService::handleFailedPayment` sets `unpaid`. If ZarinPal verify code is `101` (already verified), treated as success — correct. But if verify json fails due to timeout (`resp not ok`), it returns failed → order set `unpaid` even though ZarinPal may have already verified on their side (funds captured). Should distinguish network error vs business failure.

---

## 13. `Bank::scopeActive` not enforced at checkout

- `OrderService::checkout` uses `Bank::find($order->bank_id)` not `Bank::active()->find(...)` — inactive banks can still be used if client tampers `bank_id` hidden input.

---

## 14. Dual image path `file_exists` per hydration cost

- `Bank::getItemImageAttribute` calls `file_exists(public_path(...))` per model serialize — N loans for listing. Also uses `config('paths.upload')` which may be `storage/app/public` symlink vs `public/uploads` — path mismatch returns legacy asset spuriously.

---

## 15. Missing indexes & FK

- `orders.bank_id` has FK but not indexed for lookup? Usually FK index auto. `transaction_info` JSON path not indexed — callback scan.
- `banks` softDeletes without index on `deleted_at` + `status` composite — filter `active` scans.

---

## 16. AI Checklist — before coding payments

- [ ] Checked `Bank` fillable/casts — MerchantId case sensitive.
- [ ] Used `amount.'0'` or explicit `*10` consistently in both token and verify; validated toman int not formatted string.
- [ ] Handled `card_to_card` **before** Factory — not via gateway.
- [ ] Updated `transaction_info` JSON shape `{post:{Authority}, verify:{RefID}}` without overwriting post on verify.
- [ ] Guarded duplicate callback if `already paid` → redirect success.
- [ ] Avoided `order->delete()` on failed token — use `unpaid` + restoreStock.
- [ ] Validated `config` JSON and MerchantId presence for zarinPal.
- [ ] Used `Http` with timeout 30 and no `withoutVerifying`.

---

## 17. Evidence

- `app/Payments/ZarinPal.php:29,58 intval(amount.'0')` — amount concat bug surface.
- `app/Services/BankPaymentService.php:26 $order->delete()` — data loss bug.
- `app/Payments/ZarinPal.php:22 $config['MerchantId']` — MerchantId missing risk.
- `app/Payments/ZarinPal.php:98 Http::withHeaders()->timeout(30)` — Ssl ok via Http.
- `app/Payments/PaymentGatewayFactory.php:18 card_to_card=>ZarinPal` — dead code.
- `app/Http/Controllers/Site/CheckoutController.php:275 transaction_info->post->Authority` — JSON path lookup.
- `routes/site.php:116 Route::any /checkout/finish` — callback without auth.
- `app/Models/Bank.php:12 fillable + config json` — bank schema.
- `app/Services/OrderService.php:169 in_array paid/deposit_paid` — duplicate guard.

