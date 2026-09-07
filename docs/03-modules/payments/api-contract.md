# Payments / Banks — API Contract

> **Source:** `routes/admin.php:95-96`, `routes/site.php:88-121`, `app/Http/Controllers/Admin/BankController.php`, `app/Http/Controllers/Site/CheckoutController.php`, `app/Http/Requests/Admin/BankRequest.php`
> **Base URL:** `https://{host}` — canonical (no locale prefix) + duplicate `/{locale}/...` variants (see §3)

---

## 1. Admin Banks — `routes/admin.php:24-121` group `prefix admin`, `name admin.`, `middleware [auth, adminPermission, log.admin]`

All admin banks routes live **inside** `Route::middleware(['auth','adminPermission','log.admin'])->group(...)` at `routes/admin.php:34`. Outside this group, route would be unprotected (guard comment خط 33). `auth` is Laravel session guard (admin is `users` where `is_admin=1` + Gate). `adminPermission` checks `adminPermission` middleware permission tree for `banks` resource (index/create/store/edit/update/destroy + toggleStatus). `log.admin` is `LogAdminRequests` (audit log).

### 1.1 Resource `banks` — `Route::resource('banks', BankController::class)` — `routes/admin.php:95`

| # | Method | URI | Name | Middleware | Controller@Method | Auth | Input | Output |
|---|--------|-----|------|------------|-------------------|------|-------|--------|
| 1 | GET | `/admin/banks` | `admin.banks.index` | `auth,adminPermission,log.admin` | `BankController@index` | admin | — | `view admin.banks.index` with `banks=Bank::latest()->get()` |
| 2 | GET | `/admin/banks/create` | `admin.banks.create` | `auth,adminPermission,log.admin` | `BankController@create` | admin | — | `view admin.banks.create` empty form |
| 3 | POST | `/admin/banks` | `admin.banks.store` | `auth,adminPermission,log.admin` | `BankController@store` | admin | `BankRequest` fields (§2) + `icon` file | `302 redirect admin.banks.index` flash `success: درگاه پرداخت با موفقیت ایجاد شد` |
| 4 | GET | `/admin/banks/{bank}/edit` | `admin.banks.edit` | `auth,adminPermission,log.admin` | `BankController@edit` | admin | `bank` id route-model binding | `view admin.banks.edit` with `bank` |
| 5 | PUT/PATCH | `/admin/banks/{bank}` | `admin.banks.update` | `auth,adminPermission,log.admin` | `BankController@update` | admin | `BankRequest` fields (§2) + `icon` file optional | `302 redirect admin.banks.index` flash `success: درگاه پرداخت با موفقیت بهروزرسانی شد` |
| 6 | DELETE | `/admin/banks/{bank}` | `admin.banks.destroy` | `auth,adminPermission,log.admin` | `BankController@destroy` | admin | — | `302 redirect admin.banks.index` flash `success: درگاه پرداخت حذف شد` + `ImageHandler::delete('bank',icon)` + softDelete |

> `Route::resource` also registers `show` (`GET /admin/banks/{bank}` → `admin.banks.show`) but `BankController` has no `show()` — hitting `show` returns 405 or routes to empty. Some resources use `->except('show')` (hero-slides etc) but banks **does not** exclude show — dead route exists but unused. Frontend never links to show.

### 1.2 Custom Toggle Status — `Route::post('banks/{bank}/toggle-status', [BankController::class,'toggleStatus'])->name('banks.toggle-status')` — `routes/admin.php:96`

| Method | URI | Name | Middleware | Controller@Method | Auth | Input | Output |
|--------|-----|------|------------|-------------------|------|-------|--------|
| POST | `/admin/banks/{bank}/toggle-status` | `admin.banks.toggle-status` | `auth,adminPermission,log.admin` | `BankController@toggleStatus` | admin | — | `302 back()` flash `success: وضعیت تغییر کرد`, flips `status 1↔0` |

- No body; `bank` resolved via `Bank $bank`. Updates `status` single column, no audit log beyond `log.admin` middleware.
- Used in admin banks index table as toggle switch (AJAX POST or form POST).

### 1.3 Admin Request Validation — `BankRequest` `app/Http/Requests/Admin/BankRequest.php`

All store/update go through `BankRequest`:

```
authorize(): true  (permission via adminPermission middleware)
rules():
  title              => required|string|max:255           — error: عنوان الزامی است
  icon               => nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048 — error: فایل باید تصویر باشد / فرمتهای مجاز ... / حداکثر 2 مگ
  bank_type          => required|string|max:100           — error: نوع درگاه الزامی است   (should be in:zarinPal,card_to_card but free string)
  status             => required|boolean                  — error: وضعیت الزامی است
  admin_test         => nullable|boolean
  config             => nullable|string                   — raw JSON string; not validated as json!
  card_number        => nullable|string|max:30
  card_holder_name   => nullable|string|max:255
  card_description   => nullable|string|max:1000
```

- `PUT/PATCH` vs `POST` rules currently identical (match on method reserves future divergence).
- `config` expected like `{"MerchantId":"xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"}` for `zarinPal`; for `card_to_card` can be null/empty. Invalid JSON silently decoded to null in controller (`json_decode` returns null), persists as null — MerchantId lost without error.
- `icon` is nullable — bank can be created without image; `BankController::store` handles `handleImageUpload(null)` → `null`.

---

## 2. Inputs Detail — `config MerchantId`, `bank_type` etc.

### 2.1 Admin `config` field — `MerchantId`

- Only consumed by `ZarinPal::__construct`:
  ```php
  $config = is_array($bank->config) ? $bank->config : json_decode($bank->config ?? '{}', true);
  $this->merchant = $config['MerchantId'] ?? '';
  ```
  Key is **exactly** `MerchantId` capital M; lowercase `merchantId` → empty merchant → always fail.
- Stored as JSON string in form → decoded to array in controller → cast `json` re-encodes in DB. Example store payload:
  ```http
  POST /admin/banks
  title=زرین‌پال&bank_type=zarinPal&status=1&config={"MerchantId":"aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee"}&admin_test=0
  ```
- For `card_to_card`, `config` typically empty; card fields used instead:
  ```
  title=کارت ملت&bank_type=card_to_card&status=1&card_number=6037-...&card_holder_name=علی احمدی&card_description=لطفا فیش را آپلود کنید
  ```

### 2.2 Admin `bank_type` — discriminator

- Values: `zarinPal` (online), `card_to_card` (manual). Free string in validation — typo creates bank that `Factory::create` will throw `InvalidArgumentException` for at checkout.
- Checkout UI (`cart` payment step) lists `Bank::active()->get()` but checkout POST trusts `bank_id` from client — tamper can send inactive or wrong type.

---

## 3. Site Checkout & Callbacks — `routes/site.php:88-121`

### 3.1 Route file structure — dual registration

```php
$siteRoutes = function(){ ... checkout routes ... };
$siteRoutes(); // 1) canonical without prefix, names checkout.index, checkout.finish.zarin-pal, etc.
Route::prefix('{locale}')->where(['locale'=>$localePattern])->name('locale.')->group($siteRoutes); // 2) locale-prefixed, names locale.checkout.*
```

- `$localePattern` built from `config('languages')` + `locale()->codes()` — e.g. `^(?:en|ar)$`.
- Result: each checkout route exists twice:
  - `GET /checkout/finish` → `checkout.finish.zarin-pal`
  - `GET /{locale}/checkout/finish` → `locale.checkout.finish.zarin-pal` (e.g. `/en/checkout/finish`)
- `route('checkout.finish.zarin-pal')` generates canonical `/checkout/finish` (non-locale) used as `callback_url` for ZarinPal.

### 3.2 Checkout flow (auth required) — `Route::middleware('auth')->prefix('checkout')->name('checkout.')->group(...)` `site.php:89-113`

| Method | URI | Name | Middleware | Controller@Method | Auth | Purpose |
|--------|-----|------|------------|-------------------|------|---------|
| GET | `/checkout` | `checkout.index` | `auth` | `CheckoutController@index` | user | Step 1 cart redirect → `cart.index` |
| GET | `/checkout/address` | `checkout.address` | `auth` | `CheckoutController@address` | user | Step 2 address selection redirect |
| GET | `/checkout/cities` | `checkout.cities` | `auth` | `CheckoutController@cities` | user | AJAX `?state_id=` → `CityService::findActiveByState` → `{cities:[]}` |
| POST | `/checkout/shipments` | `checkout.shipments` | `auth` | `CheckoutController@shipments` | user | AJAX `address_id` → `ShippingCalculator::getAvailableMethods` → `{shipping_methods:[]}` + session `checkout_address_id` |
| POST | `/checkout/set-shipment` | `checkout.set-shipment` | `auth` | `CheckoutController@setShipment` | user | AJAX `shipping_method_id, shipping_price` → session `checkout_shipping_*` |
| GET | `/checkout/address-price` | `checkout.address-price` | `auth` | `CheckoutController@addressPrice` | user | Returns `final_price_sum, price_discount, price_shipping, price_cart` |
| GET | `/checkout/payment` | `checkout.payment` | `auth` | `CheckoutController@payment` | user | Step 3 payment redirect → `cart.index` step 3 |
| POST | `/checkout/apply-discount` | `checkout.apply-discount` | `auth` | `CheckoutController@applyDiscount` | user | `discount_code` → `CouponService::apply` → `{success,message}` |
| POST | `/checkout/remove-discount` | `checkout.remove-discount` | `auth` | `CheckoutController@removeDiscount` | user | Removes `session coupon` |
| POST | `/checkout/validate-stock` | `checkout.validate-stock` | `auth` | `CheckoutController@validateStock` | user | Checks `StockService::validateCartItems` → `{success, unavailable, insufficient}` |
| GET | `/checkout/order-price` | `checkout.order-price` | `auth` | `CheckoutController@orderPrice` | user | Cart price JSON |
| POST | `/checkout/create` | `checkout.create` | `auth` | `CheckoutController@create` | user | Creates `Order` via `OrderService::create` + `checkout` → redirect bank or success; inputs `bank_id, user_description, receipt_tracking_code?, receipt_payer_card?` (see §4) |
| GET | `/checkout/success/{order}` | `checkout.success` | `auth` | `CheckoutController@success` | user | Shows `checkout.success` view if `order.user_id===auth()->id() && order_status in [paid,pending_confirmation]` |
| GET | `/checkout/failed` | `checkout.failed` | `auth` | `CheckoutController@failed` | user | Shows `checkout.failed` view |

### 3.3 Callback / Finish routes (guest, no auth) — `Route::prefix('checkout')->name('checkout.')->group(...)` + `Route::any` `site.php:116-121`

| Method | URI | Name | Canonical | Locale variant | Middleware | Controller@Method | Auth | Input params | Behavior |
|--------|-----|------|-----------|---------------|------------|-------------------|------|--------------|----------|
| ANY | `/checkout/finish` | `checkout.finish.zarin-pal` | `/checkout/finish` | `/{locale}/checkout/finish` | `web` only | `CheckoutController@finishZarinPal` | guest (callback) | `Authority` (string), `Status` (`OK` or `NOK`) `?Authority=...&Status=OK` | `OrderService::callback('transaction_info->post->Authority', Authority, all)` → verify via ZarinPal → redirect `checkout.success` or `checkout.failed` |
| ANY | `/checkout/finish-saman` | `checkout.finish.saman-bank` | `/checkout/finish-saman` | `/{locale}/...` | `web` | `CheckoutController@finishSaman` | guest | `Token` | `callback('transaction_info->post->token', Token, all)` legacy |
| ANY | `/checkout/finish-sadad` | `checkout.finish.sadad-bank` | `/checkout/finish-sadad` | `/{locale}/...` | `web` | `CheckoutController@finishSadad` | guest | `OrderId` | `callback('id', OrderId, all)` legacy direct id |
| ANY | `/checkout/finish-zibal` | `checkout.finish.zibal-bank` | `/checkout/finish-zibal` | `/{locale}/...` | `web` | `CheckoutController@finishZibal` | guest | `trackId` | `callback('transaction_info->post->trackId', trackId, all)` legacy |

- `Route::any` = `GET|POST|PUT|PATCH|DELETE|OPTIONS` — ZarinPal uses GET redirect, Saman may POST, so `any` tolerates both.
- `checkout.finish.zarin-pal` is the `CALLBACK_ROUTE_NAME` constant in `ZarinPal.php:17` used to generate `callback_url` for request.json.
- Lookup field `transaction_info->post->Authority` is JSON path — `Order::where('transaction_info->post->Authority', $value)` translates to `JSON_EXTRACT`.
- Duplicate locale variants mean callback URL must be exact canonical; locale variant is valid but not used by ZarinPal config; if user hits `/en/checkout/finish?Authority=...` still works but `route()` generation ignores locale.

---

## 4. Site Checkout `POST /checkout/create` — Detailed Contract

> **Controller:** `CheckoutController::create(CardToCardCheckoutRequest $request)` `checkout.create` `site.php:108`

**Middleware:** `auth` (session).
**Request class:** `CardToCardCheckoutRequest` (validates `bank_id` required, `user_description` nullable, card_to_card receipt fields conditionally required).
**Session prerequisites:** `checkout_address_id`, `checkout_shipping_method_id` must exist, else `redirect back with error اطلاعات تکمیل نیست.`

**Body params:**

| Field | Required | Type | Rule | Note |
|-------|----------|------|------|------|
| `bank_id` | yes | int | `exists:banks,id` | Tamper can send inactive id — no `active()` check |
| `user_description` | no | string | nullable | Stored `orders.user_description` |
| `receipt_tracking_code` | conditional | string | nullable, required_if card_to_card | Card-to-card tracking code, stored `orders.receipt_tracking_code`, only if bank is card_to_card |
| `receipt_payer_card` | conditional | string | nullable | Payer last 4/card number, stored `receipt_payer_card` |

**Derived session → orderData mapping:**

```
address_id, state_id, city_id, state_name, city_name, full_address, receiptor_full_name/mobile/postal_code
shipping_method_id, shipping_price, freight_balance
total_price = cart.subtotal(), discount_price = cart.discount(), payment_price = cart.total() + shipping_price
discount_id = session('coupon.discount_id'), address snapshot JSON
```

**Response:** `OrderService::create` + `OrderService::checkout` result:
- If `card_to_card` → `302 redirect route('checkout.success', order.id)` flash `سفارش شما ثبت شد و پس از تأیید...` (`pending_confirmation`)
- If `zarinPal` and token success → script exits via `header Location https://payment.zarinpal.com/pg/StartPay/{Authority}` (no JSON)
- If token failed → `302 redirect route('checkout.failed')` flash `خطا در پرداخت، مجدد تلاش نمایید.` + order soft-deleted
- On exception → `302 redirect checkout.failed` log `Order creation failed`

**Evidence:** `CheckoutController.php:157-238`, `OrderService.php:130-152`.

---

## 5. Route Table — Consolidated (admin + site)

```
# Admin banks (prefix admin, middleware auth,adminPermission,log.admin)
GET    /admin/banks                      admin.banks.index
GET    /admin/banks/create               admin.banks.create
POST   /admin/banks                      admin.banks.store
GET    /admin/banks/{bank}/edit          admin.banks.edit
PUT    /admin/banks/{bank}               admin.banks.update
PATCH  /admin/banks/{bank}               admin.banks.update
DELETE /admin/banks/{bank}               admin.banks.destroy
GET    /admin/banks/{bank}               admin.banks.show          (exists, no controller method)
POST   /admin/banks/{bank}/toggle-status admin.banks.toggle-status

# Site checkout (canonical) — auth group
GET    /checkout                         checkout.index
GET    /checkout/address                 checkout.address
GET    /checkout/cities                  checkout.cities
POST   /checkout/shipments               checkout.shipments
POST   /checkout/set-shipment            checkout.set-shipment
GET    /checkout/address-price           checkout.address-price
GET    /checkout/payment                 checkout.payment
POST   /checkout/apply-discount          checkout.apply-discount
POST   /checkout/remove-discount         checkout.remove-discount
POST   /checkout/validate-stock          checkout.validate-stock
GET    /checkout/order-price             checkout.order-price
POST   /checkout/create                  checkout.create
GET    /checkout/success/{order}         checkout.success
GET    /checkout/failed                  checkout.failed
# Callbacks — any method, web only (no auth)
ANY    /checkout/finish                  checkout.finish.zarin-pal      (Authority, Status)
ANY    /checkout/finish-saman            checkout.finish.saman-bank     (Token)
ANY    /checkout/finish-sadad            checkout.finish.sadad-bank     (OrderId)
ANY    /checkout/finish-zibal            checkout.finish.zibal-bank     (trackId)
# Same 16 site routes duplicated with prefix {locale} → names locale.* (e.g. locale.checkout.finish.zarin-pal)
```

---

## 6. Notes — Auth, Throttle, Locale

- No throttle on checkout or callback — unlike OTP (`throttle:5,1`) and contact (`throttle:5,1`), payments callbacks are unthrottled; brute-force Authority guess possible but Authority is random string — enumeration via JSON path + `where` not indexed → slow.
- `locale` duplication means `route('checkout.finish.zarin-pal')` generates `/checkout/finish` (canonical); ZarinPal stores that. If site accessed via `/{locale}/checkout/...` the callback still hits canonical because ZarinPal calls absolute URL.
- `adminPermission` middleware checks permission tree key for `banks`; `is_admin` bypass scalar? Check `adminPermission` impl — usually `Gate::before` for `is_admin` allows all. Regular admin role needs `banks` permission.

---

## 7. Evidence — Filenames + Finglish

- `routes/admin.php:95 Route::resource('banks', BankController::class)` — resource-e admin banks.
- `routes/admin.php:96 Route::post('banks/{bank}/toggle-status' ...)` — toggleStatus custom.
- `routes/site.php:89 middleware auth prefix checkout` — checkout steps auth required.
- `routes/site.php:116-121 Route::any /finish /finish-saman /finish-sadad /finish-zibal` — callback-haye pardakht bedune auth, Authority lookup.
- `app/Http/Controllers/Admin/BankController.php:13-82 index/create/store/edit/update/destroy/toggleStatus` — tamame CRUD.
- `app/Http/Controllers/Site/CheckoutController.php:270-300 finishZarinPal etc` — map Authority → OrderService callback.
- `app/Http/Requests/Admin/BankRequest.php:16-27 rules` — validation-e config MerchantId va bank_type.
