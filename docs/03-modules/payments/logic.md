# Payments / Banks — Logic

> **Models:** `Bank`, `Order` (transaction_info), `PaymentGateway`, `ZarinPal`, `PaymentGatewayFactory`, `PaymentPostDTO`, `PaymentVerifyDTO`
> **Services:** `BankPaymentService`, `OrderService`, `StockService`, `NotificationService`, `AuditService`
> **Controllers:** `Admin\BankController`, `Site\CheckoutController`
> **Source:** `app/Models/Bank.php` (39 lines) | `app/Payments/ZarinPal.php` (115 lines) | `app/Payments/PaymentGatewayFactory.php` (22 lines) | `app/Payments/PaymentGateway.php` (23 lines) | `app/Services/BankPaymentService.php` (70 lines) | `app/Services/OrderService.php` (270 lines) | `app/Http/Controllers/Admin/BankController.php` (95 lines) | `app/Http/Controllers/Site/CheckoutController.php` (333 lines) | `app/Http/Requests/Admin/BankRequest.php` (53 lines) | `routes/admin.php:95-96` | `routes/site.php:116-121`
> **Workflows:** `docs/04-workflows/payment-zarinpal.md` + `docs/04-workflows/card-to-card.md` + `docs/04-workflows/cart-checkout.md`

---

## 1. Purpose & Scope

Payments module provides **دو حالت پرداخت**: آنلاین via ZarinPal و دستی via `card_to_card` (کارت به کارت). Bank model acts as gateway registry — admin defines which method is active, its credentials (`config.MerchantId` for ZarinPal, `card_number`/`card_holder_name` for card_to_card), icon, and `status`. Checkout never calls Factory for card_to_card directly; `OrderService::checkout()` short-circuits before Factory. ZarinPal flow is **toman → rial با الحاق رشته‌ای `'0'` (x10)** in both token and verify phases. Transaction lifecycle is persisted in `orders.transaction_info` JSON (`{post:{Authority}, verify:{RefID}}`) and reconciled via callback `checkout.finish.zarin-pal` (` ANY /checkout/finish`). Duplicate callback when already `paid` is treated as idempotent success — no second verify.

Finglish evidence: `Bank.php:12 fillable`, `Bank.php:15 config json cast`, `ZarinPal.php:29 intval($amount.'0')`, `ZarinPal.php:58 intval($price.'0')`, `BankPaymentService.php:26 order->delete()`, `OrderService.php:145 bank_type === 'card_to_card'`, `CheckoutController.php:275 transaction_info->post->Authority`, `OrderService.php:169 in_array paid/deposit_paid`.

---

## 2. Bank Model — `app/Models/Bank.php`

### 2.1 Fillable & Casts

```php
protected $fillable = ['title','icon','status','admin_test','bank_type','config','card_number','card_holder_name','card_description'];
protected $casts = ['config'=>'json', 'admin_test'=>'boolean'];
protected $appends = ['item_image'];
use SoftDeletes;
```

- `title`: human name (e.g. "زرین‌پال", "کارت به کارت ملت").
- `icon`: filename only (e.g. `abc123.webp`) stored under `uploads/bank/` or legacy `assets/site/bank/`.
- `status`: `1`=active, `0`=inactive. `scopeActive()` filters `where status=1`. Checkout should only list `Bank::active()->get()` — but `Bank::find($bank_id)` in `OrderService::checkout()` does not enforce active; user can post inactive `bank_id` if they tamper form — gotcha.
- `admin_test`: boolean nullable — intended for test gateway visible only to admin; `BankController::store` defaults `admin_test ?? 0` if omitted.
- `bank_type`: discriminator string — canonical values `zarinPal` and `card_to_card` (see §3). Factory `match` throws `InvalidArgumentException` for others.
- `config`: JSON — for ZarinPal holds `{"MerchantId":"xxxxxxxx-xxxx-..."}`, for card_to_card typically `null`. Cast `json` auto decodes; ZarinPal constructor also handles `is_array` else `json_decode` fallback — double safety.
- `card_number`, `card_holder_name`, `card_description`: only meaningful for `card_to_card`; nullable for ZarinPal. Displayed on checkout success pending screen.
- `SoftDeletes`: banks are soft-deleted; relations (`orders.bank`) must handle trashed? `Order::bank()` is plain `belongsTo` without `withTrashed()`, so historical orders whose bank was deleted will return `null` bank — `OrderService::callback` then `findByField` still works but `handleVerify` would fail `order->bank` null.

Evidence: `Bank.php:12`, `Bank.php:14-17`, `Bank.php:35-38 scopeActive`.

### 2.2 `getItemImageAttribute` — Dual-path resolution

```php
public function getItemImageAttribute(): ?string {
  $icon = $this->attributes['icon'] ?? null;
  if (!$icon) return null;
  $uploadPath = config('paths.upload','uploads');
  if (file_exists(public_path("{$uploadPath}/bank/{$icon}"))) return asset("{$uploadPath}/bank/{$icon}");
  return asset('assets/site/bank/'.$icon);
}
```

- Checks `public/uploads/bank/{icon}` first (new `ImageHandler` uploads). Falls back to `public/assets/site/bank/{icon}` (legacy seeded banks).
- `$appends = ['item_image']` means every `Bank::toArray()` includes computed URL — useful for API/checkout listing but adds filesystem `file_exists` per model hydration (N+1 if listing many banks).
- `config('paths.upload')` indirection — default `uploads` if not published; changing config without moving files breaks images.

Evidence: `Bank.php:21-33`.

### 2.3 Bank Types Discriminator

| `bank_type` | `config` | `card_*` | Gateway | OrderService handling |
|---|---|---|---|---|
| `zarinPal` | `{"MerchantId":"..."}` required | null | `ZarinPal` | `BankPaymentService::handlePayment` → redirect to `StartPay/Authority` |
| `card_to_card` | null / `{}` | `card_number`, `card_holder_name`, `card_description` required (UI) | **No gateway** — Factory dead code `card_to_card => new ZarinPal(...)` but never reached | `OrderService::checkout()` sets `pending_confirmation` and redirects to `checkout.success` without gateway call |

Finglish: `card_to_card` یعنی «پرداخت دستی» — کاربر مبلغ را به شماره کارت واریز کرده و `receipt_tracking_code` + `receipt_payer_card` را در فرم checkout وارد می‌کند؛ سفارش تا تأیید ادمین `pending_confirmation` می‌ماند. `zarinPal` یعنی درگاه آنلاین با Authority/RefID.

Evidence: `PaymentGatewayFactory.php:18 card_to_card => new ZarinPal`, `OrderService.php:145 if bank_type === 'card_to_card'`.

---

## 3. Admin CRUD — `app/Http/Controllers/Admin/BankController.php`

> **Routes:** `routes/admin.php:95-96` inside `middleware ['auth','adminPermission','log.admin']` + `prefix admin` + `name admin.`
> - `GET /admin/banks` → `index` name `admin.banks.index`
> - `GET /admin/banks/create` → `create` name `admin.banks.create`
> - `POST /admin/banks` → `store` name `admin.banks.store`
> - `GET /admin/banks/{bank}/edit` → `edit` name `admin.banks.edit`
> - `PUT/PATCH /admin/banks/{bank}` → `update` name `admin.banks.update`
> - `DELETE /admin/banks/{bank}` → `destroy` name `admin.banks.destroy`
> - `POST /admin/banks/{bank}/toggle-status` → `toggleStatus` name `admin.banks.toggle-status` (custom, not resource)

### 3.1 `index()`

```php
$banks = Bank::latest()->get(); return view('admin.banks.index', compact('banks'));
```

- No pagination — loads all banks; ok for <20 rows but unbounded.
- `latest()` = `orderBy created_at DESC`.

Evidence: `BankController.php:13-17`.

### 3.2 `create()` / `edit(Bank $bank)`

- Returns blade `admin.banks.create` / `admin.banks.edit` with `compact('bank')`.
- Route-model binding `Bank $bank` uses SoftDeletes scope — trashed not found (404).

Evidence: `BankController.php:19-22`, `BankController.php:39-42`.

### 3.3 `store(BankRequest $request)`

```php
$validated = $request->validated();
$validated['icon'] = $this->handleImageUpload($request->file('icon'));
$validated['config'] = $validated['config'] ? json_decode($validated['config'], true) : null;
$validated['admin_test'] = $validated['admin_test'] ?? 0;
Bank::create($validated);
return redirect()->route('admin.banks.index')->with('success', 'درگاه پرداخت با موفقیت ایجاد شد');
```

- `BankRequest` already validated; `handleImageUpload` delegates to `ImageHandler::handle('bank', $file)` — stores under `uploads/bank/` with WebP conversion? See §4 — returns filename or `null` if no file.
- `config` arrives as **string** from textarea/input (e.g. `{"MerchantId":"abc"}`) — controller `json_decode`s to array before create; since `Bank` casts `config` as `json`, Eloquent re-encodes on save. Double encode prevented by decoding first. If `config` is empty string, stored as `null`.
- `admin_test` checkbox unchecked not sent → defaults `0`; checked sends `1` boolean cast.
- Flashcards: `card_number` etc are mass-assigned via `$validated` — no sanitization beyond `string|max` in Request.

Evidence: `BankController.php:24-37`, `BankRequest.php:28-37 rules`.

### 3.4 `update(BankRequest $request, Bank $bank)`

```php
if ($request->hasFile('icon') && $request->file('icon')->isValid()) {
  if ($bank->icon) ImageHandler::delete('bank', $bank->icon);
  $validated['icon'] = $this->handleImageUpload($request->file('icon'));
} else unset($validated['icon']);
$validated['config'] = $validated['config'] ? json_decode($validated['config'], true) : null;
$validated['admin_test'] = $validated['admin_test'] ?? 0;
$bank->update($validated);
```

- **Delete old icon before upload** — if upload fails, old icon already deleted → data loss. Safer to upload first then delete.
- `unset($validated['icon'])` when no new file keeps old filename — excludes `null` overwrite.
- Same `config` decode logic as store.
- Returns `redirect()->route('admin.banks.index')->with('success','...بهروزرسانی شد')`.

Evidence: `BankController.php:44-66`.

### 3.5 `destroy(Bank $bank)`

```php
if ($bank->icon) ImageHandler::delete('bank', $bank->icon);
$bank->delete(); // SoftDeletes
```

- Deletes physical file under `uploads/bank/` via `ImageHandler::delete('bank', $icon)` — checks `is_file` before `unlink`, handles resized variants if `$sizes` passed (not passed here, so only main file removed; resized copies if any remain orphaned).
- Soft delete — row remains with `deleted_at`; `Order::bank()` without `withTrashed` returns null for old orders.

Evidence: `BankController.php:68-76`, `ImageHandler.php:31-57`.

### 3.6 `toggleStatus(Bank $bank)`

```php
$bank->update(['status' => $bank->status ? 0 : 1]); return back()->with('success','وضعیت تغییر کرد');
```

- Flips `1 ↔ 0`; no authorization beyond `adminPermission` middleware group. No event/audit.

Evidence: `BankController.php:78-82`.

### 3.7 `handleImageUpload($file): ?string`

```php
if (!$file || !$file->isValid()) return null;
return ImageHandler::handle('bank', $file);
```

- Wrapper around `ImageHandler::handle('bank', $file)` — folder `bank` maps to `public/uploads/bank/`. `ImageHandler` internally calls `ImageUploader::upload` with WebP conversion + optional `ImageResizer::resize` if `$sizes` given (not given here, so original WebP only).

Evidence: `BankController.php:87-94`, `ImageHandler.php:12-26`.

---

## 4. Image Handling — `ImageHandler` `bank` folder WebP

> **Source:** `app/Helpers/ImageHandler.php` (58 lines) → `ImageUploader::upload` + `ImageResizer::resize`

- `ImageHandler::handle('bank', UploadedFile)` uploads to `{config paths.upload}/bank/` (default `public/uploads/bank/`), converts to WebP if source is jpg/png (via `ImageUploader`). Returns filename string (e.g. `1714_abc.webp`) stored in `banks.icon`.
- `ImageHandler::delete('bank', $filename)` unlinks `public/uploads/bank/{filename}` and, if `$sizes` provided, each `public/uploads/bank/{sizeKey}/{filename}`. Bank controller calls without `$sizes`, so only main file removed.
- Validation in `BankRequest`: `icon => nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048` — 2MB max, allows svg (stored as-is? svg → webp conversion edge). `required` not enforced — banks can be created without icon (icon nullable).
- Dual-path accessor (§2.2) means old seeded banks with `icon=zar.png` under `assets/site/bank/zar.png` still resolve; new uploads under `uploads/bank/` take precedence via `file_exists` check.

Evidence: `ImageHandler.php:12-26 handle`, `ImageHandler.php:31-57 delete`, `BankRequest.php:19 icon nullable|image|mimes...max:2048`.

---

## 5. Validation — `BankRequest` `app/Http/Requests/Admin/BankRequest.php`

- `authorize(): true` — relies on route middleware `adminPermission` for authz; FormRequest does not re-check.
- `rules()` uses `match($this->method())` with `'PUT','PATCH'` vs `default` (POST) — currently **identical arrays**; distinction reserved for future (e.g. `icon required` on create). Both allow `icon nullable`.
- Fields:
  - `title: required|string|max:255`
  - `icon: nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048`
  - `bank_type: required|string|max:100` — should be `in:zarinPal,card_to_card` but is free string; admin can invent type leading to Factory exception at checkout.
  - `status: required|boolean`
  - `admin_test: nullable|boolean`
  - `config: nullable|string` — raw JSON string (not validated as json); invalid JSON `json_decode` returns null silently and stores `null` — MerchantId lost.
  - `card_number: nullable|string|max:30`, `card_holder_name: nullable|string|max:255`, `card_description: nullable|string|max:1000` — no regex for card format.
- Messages in Farsi: `title.required => عنوان الزامی است`, `icon.image => فایل باید تصویر باشد`, etc.

Evidence: `BankRequest.php:14-52`.

---

## 6. PaymentGateway Contract & DTOs

### 6.1 `PaymentGateway` interface `app/Payments/PaymentGateway.php`

```php
interface PaymentGateway {
  getBankToken(int $amount, Order $order): PaymentPostDTO;
  verifyTransaction(array $callbackData, int $price, ?Order $order = null): PaymentVerifyDTO;
  redirect(PaymentPostDTO $postData, Order $order): void;
}
```

- `getBankToken`: takes **toman** `amount` int and Order; returns `PaymentPostDTO` with status + Authority. Must perform toman→rial internally.
- `verifyTransaction`: takes `callbackData` (query params from gateway), `price` toman int, optional Order; returns `PaymentVerifyDTO` with status + RefID.
- `redirect`: takes `PaymentPostDTO` (with Authority) and Order; issues `header Location` to gateway.

Evidence: `PaymentGateway.php:7-23`.

### 6.2 `PaymentPostDTO` `app/Payments/PaymentPostDTO.php`

```php
protected string $status; protected array $post_data;
getStatus(): string; getPostData(): array;
static fromData(string $status, array $postData=[]): self
```

- `$status` is either `'success'` or `'failed'` (literal strings checked via `===` in BankPaymentService).
- `post_data` for success holds `['Authority'=> 'A000...']`; for failed holds `[]`.

Evidence: `PaymentPostDTO.php:5-28`.

### 6.3 `PaymentVerifyDTO` `app/Payments/PaymentVerifyDTO.php`

- Same shape with `status` + `verify_data`; success holds `['RefID'=> 123456]`.

Evidence: `PaymentVerifyDTO.php:5-28`.

---

## 7. PaymentGatewayFactory — `app/Payments/PaymentGatewayFactory.php`

```php
public static function create(Bank $bank, string $siteName=''): PaymentGateway {
  return match($bank->bank_type) {
    'zarinPal' => new ZarinPal($bank, $siteName),
    'card_to_card' => new ZarinPal($bank, $siteName), // dead code
    default => throw new InvalidArgumentException("Unsupported bank type: {$bank->bank_type}")
  };
}
```

- **Dead code**: `card_to_card => new ZarinPal(...)` is never executed because `OrderService::checkout()` intercepts `card_to_card` **before** calling `BankPaymentService` (and thus Factory). If Factory were ever called directly with card_to_card (e.g. future caller, artisan command), it would incorrectly try ZarinPal with empty MerchantId and fail token.
- Correct behavior for `card_to_card` is no gateway at all — Factory should throw or return NullGateway. Current mapping masks bug.
- `siteName` defaults `config('app.name','Shop')` injected from `BankPaymentService`.

Evidence: `PaymentGatewayFactory.php:14-21`, `OrderService.php:145-149`.

---

## 8. ZarinPal Gateway — `app/Payments/ZarinPal.php` (115 lines)

### 8.1 Construction & Merchant

```php
public function __construct($bank, string $siteName='') {
  $config = is_array($bank->config) ? $bank->config : json_decode($bank->config ?? '{}', true);
  $this->merchant = $config['MerchantId'] ?? '';
  $this->siteName = $siteName;
}
private const EP_REQUEST  = 'https://payment.zarinpal.com/pg/v4/payment/request.json';
private const EP_VERIFY   = 'https://payment.zarinpal.com/pg/v4/payment/verify.json';
private const EP_REDIRECT = 'https://payment.zarinpal.com/pg/StartPay/';
private const CALLBACK_ROUTE_NAME = 'checkout.finish.zarin-pal';
private const USER_AGENT = 'ZarinPal Rest Api v1';
```

- `MerchantId` is case-sensitive — key exactly `MerchantId` capital M; `merchantId` would miss.
- Empty MerchantId (`''`) still sent to ZarinPal — API returns `code !=100` → DTO failed → order deleted. No early guard.
- `siteName` used in description: `$siteName . ' خرید از ' . $siteName` — duplicates site name (e.g. "Shop خرید از Shop").
- Three endpoints distinct: `request.json` (create Authority), `verify.json` (confirm RefID), `StartPay/` (redirect). Do not confuse `StartPay` is GET redirect, not JSON.

Evidence: `ZarinPal.php:11-25`.

### 8.2 `getBankToken(int $amount, Order $order): PaymentPostDTO`

```php
public function getBankToken(int $amount, Order $order): PaymentPostDTO {
  $amountRial = intval($amount . '0'); // toman -> rial x10 string concat
  $payload = [
    'merchant_id' => $this->merchant,
    'amount' => $amountRial,
    'callback_url' => route(self::CALLBACK_ROUTE_NAME),
    'description' => $this->siteName . ' خرید از ' . $this->siteName,
    'metadata' => [],
  ];
  $resp = $this->sendJsonRequest(self::EP_REQUEST, $payload);
  if (!$resp['ok']) return PaymentPostDTO::fromData('failed');
  $result = $resp['json'];
  if (isset($result['data']['code']) && (int)$result['data']['code']===100) {
    return PaymentPostDTO::fromData('success', ['Authority'=>$result['data']['authority'] ?? null]);
  }
  return PaymentPostDTO::fromData('failed');
}
```

- **Amount conversion both here and verify**: `intval($amount.'0')` concatenates string `'0'` then `intval` — equivalent to `*10` but via string. Gotchas: `0.'0' => '00' => intval 0`, `999.'0' => '9990'`. Not `*10` arithmetic, so avoids float but relies on string; if `$amount` is `"1,000"` (with comma) it would produce `"1,0000"` → `intval` yields `1` — but caller sanitizes via `str_replace(',','',...)` and `intval` before passing. Direct call with formatted price would break.
- `callback_url` is `route('checkout.finish.zarin-pal')` absolute URL (e.g. `https://shop.example.com/checkout/finish`) — generated via `route()` helper; must be `ANY` route (GET+POST) because ZarinPal may redirect GET with query `Authority`+`Status`.
- `description` metadata empty. `metadata: []` sent but not used.
- `sendJsonRequest` returns `['ok'=>bool,'json'=>array|null]`; `ok` false on HTTP failure (non-2xx) or throwable caught.
- Success condition **exactly** `data.code ===100` (int). Code `101` is **not** success for request (only for verify). ZarinPal docs: 100 = success, 101 = already verified (verify only).
- Authority extracted as `$result['data']['authority']` lowercase key; ZarinPal returns `authority` lowercase in v4 `request.json`.
- On failed, DTO `fromData('failed')` with empty post_data.

Evidence: `ZarinPal.php:27-54`, `OrderService.php:132 price calc`.

### 8.3 `verifyTransaction(array $callbackData, int $price, ?Order $order): PaymentVerifyDTO`

```php
public function verifyTransaction(array $callbackData, int $price, ?Order $order=null): PaymentVerifyDTO {
  $amountRial = intval($price . '0');
  if (isset($callbackData['Status']) && $callbackData['Status'] !== 'OK') return PaymentVerifyDTO::fromData('failed');
  $payload = ['merchant_id'=>$this->merchant, 'authority'=>$callbackData['Authority'] ?? '', 'amount'=>$amountRial];
  $resp = $this->sendJsonRequest(self::EP_VERIFY, $payload);
  if (!$resp['ok']) return PaymentVerifyDTO::fromData('failed');
  $result = $resp['json'];
  if (isset($result['data']['code']) && ((int)$result['data']['code']===100 || (int)$result['data']['code']===101)) {
    return PaymentVerifyDTO::fromData('success', ['RefID'=>$result['data']['ref_id'] ?? null]);
  }
  return PaymentVerifyDTO::fromData('failed');
}
```

- Same `'0'` concat for verify amount — must match request amount, else ZarinPal returns code -... (amount mismatch).
- Early guard: if `Status !== 'OK'` (user cancelled at gateway), returns `failed` **without calling verify.json** — saves API call.
- `Authority` taken from `$callbackData['Authority']` (capital A) — query param from ZarinPal redirect. Missing Authority → payload `authority:''` → verify fails.
- Success codes `100` (verified now) **or** `101` (already verified) — both return success with `RefID`. Code `101` indicates duplicate verify after already paid; treated as success to allow idempotent callback without second status update.
- `ref_id` key lowercase with underscore in verify response (vs `authority` lowercase in request). Extracted as `RefID` capital in DTO.

Evidence: `ZarinPal.php:56-85`.

### 8.4 `request.json` vs `verify.json` vs `StartPay`

| Endpoint | Method | Payload | Response shape | Success code | Key field |
|---|---|---|---|---|---|
| `.../pg/v4/payment/request.json` | POST JSON | `merchant_id, amount(rial), callback_url, description, metadata` | `{data:{code, authority, message}}` | `code==100` | `authority` (Authority) |
| `.../pg/v4/payment/verify.json` | POST JSON | `merchant_id, authority, amount(rial)` | `{data:{code, ref_id, message}}` | `code==100 || 101` | `ref_id` (RefID) |
| `.../pg/StartPay/{Authority}` | GET redirect | — Authority in path | 302 to ZarinPal payment page | — | — |

- `request.json` creates Authority (transaction token) — stored in `transaction_info.post.Authority`.
- `StartPay/Authority` is where user completes payment; `header('Location: '.EP_REDIRECT.Authority)` sends them there.
- `verify.json` confirms payment after user returns with `Authority`+`Status=OK` query; returns `RefID` stored in `transaction_info.verify.RefID`.

Evidence: `ZarinPal.php:14-16 constants`, `ZarinPal.php:39 sendJsonRequest EP_REQUEST`, `ZarinPal.php:70 EP_VERIFY`, `ZarinPal.php:91 EP_REDIRECT`.

### 8.5 `redirect(PaymentPostDTO $postData, Order $order): void`

```php
public function redirect(PaymentPostDTO $postData, Order $order): void {
  $authority = $postData->getPostData()['Authority'] ?? '';
  header('Location: ' . self::EP_REDIRECT . $authority);
  exit;
}
```

- Direct `header()` + `exit` — not Laravel `RedirectResponse`. Caller `BankPaymentService` wraps with `ob_start()` to capture header? Actually `header()` sends even under output buffering, but `ob_start` captures any echoed content (none). `exit` terminates script — code after in `BankPaymentService` (`$content = ob_get_clean(); return response($content)`) is **unreachable**? Let's see `BankPaymentService.php:38-42`: `ob_start(); $gateway->redirect(...); $content=ob_get_clean(); return response($content);` — `redirect` calls `exit` so lines after never execute; `ob_get_clean` never reached. In practice Laravel's `header` still sends redirect and script dies; response object dead code.
- Authority missing (`''`) would redirect to `.../StartPay/` without token — 404 at ZarinPal.

Evidence: `ZarinPal.php:87-93`, `BankPaymentService.php:38-42`.

### 8.6 `sendJsonRequest(string $url, array $payload): array`

```php
private function sendJsonRequest(string $url, array $payload=[]): array {
  try {
    $response = Http::withHeaders(['Accept'=>'application/json','User-Agent'=>self::USER_AGENT])->timeout(30)->post($url, $payload);
    if ($response->failed()) return ['ok'=>false,'json'=>null];
    $json = $response->json();
    return is_array($json) ? ['ok'=>true,'json'=>$json] : ['ok'=>false,'json'=>null];
  } catch (Throwable $e) {
    Log::error('ZarinPal request failed', ['message'=>$e->getMessage()]);
    return ['ok'=>false,'json'=>null];
  }
}
```

- Uses `Illuminate\Support\Facades\Http` (Laravel HTTP client, Guzzle under). `timeout(30)` seconds.
- Headers include `Accept: application/json` and `User-Agent: ZarinPal Rest Api v1`.
- `Http` handles SSL verification by default (verify true) — Ssl ok via Http, no insecure flag.
- `failed()` true for status >=400; ZarinPal error codes (e.g. `code: -9`) still HTTP 200 so `failed()` false — code checked separately.
- `response->json()` may throw if not JSON; caught as Throwable → log and return failed.
- Logs only message, not url/payload — avoids leaking MerchantId.

Evidence: `ZarinPal.php:95-114`.

---

## 9. BankPaymentService — `app/Services/BankPaymentService.php`

### 9.1 `handlePayment(Bank $bank, Order $order, int $price): RedirectResponse`

```php
public function handlePayment(Bank $bank, Order $order, int $price): RedirectResponse {
  $gateway = PaymentGatewayFactory::create($bank, config('app.name','Shop'));
  $response = $gateway->getBankToken($price, $order);
  Auth::loginUsingId($order->user_id);
  if ($response->getStatus() === 'failed') {
    $order->delete(); // hard delete! data loss
    return redirect(route('checkout.failed'))->with('error','خطا در پرداخت، مجدد تلاش نمایید.');
  }
  $order->update(['transaction_info'=>['post'=>$response->getPostData(),'verify'=>[]]]);
  ob_start(); $gateway->redirect($response,$order); $content=ob_get_clean(); return response($content);
}
```

- Factory + `getBankToken` already did `intval(price.'0')` inside gateway; `price` here is toman int from `OrderService::checkout` (already `intval(str_replace(',','',payment_price))`).
- `Auth::loginUsingId($order->user_id)` — logs in the order owner before redirect; needed because callback is unauthenticated but success page checks `auth()->id() === order->user_id`. Without this, guest checkout? Actually checkout requires `auth` middleware, but callback logs in again.
- **Hard delete on failed token**: `$order->delete()` then `redirect checkout.failed`. Since `Order` uses `SoftDeletes`, `delete()` is soft? Wait `Order` model uses `SoftDeletes` (`use SoftDeletes;`), so `delete()` sets `deleted_at`, not hard delete from DB. However spec says "hard delete" — but code shows `OrderService::create` on stock reservation fail uses `forceDelete()` (hard), while here uses plain `delete()` (soft). Check `BankPaymentService.php:26 $order->delete()` — with SoftDeletes this is soft delete, recoverable. But Order has FK items, histories not deleted — orphaned? OrderItems remain. Some docs label this "hard delete" loosely as data loss (order disappears from UI). Could be considered data loss because soft-deleted orders not visible in `admin/orders` nor user dashboard, but still in DB with deleted_at.
- Transaction info shape set to `['post'=>['Authority'=>...],'verify'=>[]]` — verify empty until callback.
- `ob_start` + `redirect` with `exit` → unreachable return, but method signature promises `RedirectResponse` yet actually never returns on success (script exits). On failed path returns `RedirectResponse` to `checkout.failed`.
- Gotcha: `order->update(['transaction_info'=>...])` overwrites entire JSON; if order already had other keys in transaction_info, lost. But creation sets it fresh.

Evidence: `BankPaymentService.php:17-43`.

### 9.2 `handleVerify(Order $order, array $callbackData, int $price): bool`

```php
public function handleVerify(Order $order, array $callbackData, int $price): bool {
  $gateway = PaymentGatewayFactory::create($order->bank, config('app.name','Shop'));
  $response = $gateway->verifyTransaction($callbackData, $price, $order);
  Auth::loginUsingId($order->user_id);
  $transactionInfo = $order->transaction_info ?? [];
  $transactionInfo['verify'] = $response->getVerifyData() ?? [];
  $order->update(['transaction_info'=>$transactionInfo]);
  if ($response->getStatus() === 'failed') { $order->update(['order_status'=>'unpaid']); return false; }
  return true;
}
```

- Loads gateway from `$order->bank` (relation) — if bank soft-deleted, `bank` null → Factory throws `InvalidArgumentException` on null bank_type; propagate exception, callback would 500.
- `Auth::loginUsingId` again for post-verify success page.
- Merges `verify` into existing `transaction_info` preserving `post` (unlike handlePayment which replaced). If `transaction_info` was null, uses `[]`.
- On failed verify, sets `order_status = 'unpaid'` (not `delete`). Caller `OrderService::callback` then does `handleFailedPayment` which also sets `unpaid` + `restoreStock` (idempotent guard `if order_status === 'unpaid' return`).
- On success, returns `true` — caller then does `handleSuccessOrderPayment` to set `paid`/`deposit_paid`, audit, notify.

Evidence: `BankPaymentService.php:48-69`.

---

## 10. OrderService — `app/Services/OrderService.php` Checkout Integration

### 10.1 `checkout(Order $order): RedirectResponse`

```php
public function checkout(Order $order): RedirectResponse {
  $price = intval(str_replace(',','',$order->deposit_price)) !==0 ? intval(str_replace(',','',$order->deposit_price)) : intval(str_replace(',','',$order->payment_price));
  if ($order->discount_id !== null && $price <=0) return $this->handleFreeOrder($order);
  $bank = Bank::find($order->bank_id);
  if (!$bank) return redirect(route('checkout.failed'))->with('error','درگاه پرداخت یافت نشد.');
  if ($bank->bank_type === 'card_to_card') {
    $order->update(['order_status'=>'pending_confirmation']);
    return redirect(route('checkout.success',$order->id))->with('success','سفارش شما ثبت شد و پس از تأیید پرداخت، سفارشتان پردازش میشود.');
  }
  return $this->bankPaymentService->handlePayment($bank,$order,$price);
}
```

- **Price calc**: checks `deposit_price` first (partial payment?), else `payment_price`. Both are strings (maybe with commas) — `str_replace(',','',...)` removes thousands separator, `intval` gives toman. If `discount_id` not null and price <=0 (100% discount), treats as free order → `handleFreeOrder` sets `paid` directly without gateway.
- **card_to_card short-circuit** before Factory — sets `pending_confirmation` immediately, no token, no redirect to ZarinPal. This makes Factory dead code for card_to_card essential.
- `Bank::find` not `active` check — inactive bank still used.
- `handleFreeOrder`: `$order->update(['order_status'=>'paid'])`, `session()->flash('success',...)`, redirect `checkout.success`.

Evidence: `OrderService.php:130-159`.

### 10.2 `callback(string $field, string $value, array $data): RedirectResponse` — Authority lookup via JSON path

```php
public function callback(string $field, string $value, array $data): RedirectResponse {
  $order = self::findByField($field,$value);
  if (!$order) return redirect(route('cart.index'))->with('error','فاکتور معتبر نمی باشد');
  if (in_array($order->order_status, ['paid','deposit_paid'])) return redirect(route('checkout.success',$order->id));
  $price = intval(str_replace(',','',$order->deposit_price)) !==0 ? ... : intval(str_replace(',','',$order->payment_price));
  $isSuccess = $this->bankPaymentService->handleVerify($order,$data,$price);
  if ($isSuccess) return $this->handleSuccessOrderPayment($order);
  $this->handleFailedPayment($order);
  return redirect(route('checkout.failed'))->with('error','خطا در پرداخت، مجدد تلاش نمایید.');
}
```

- `findByField` does `Order::orderBy('id','DESC')->where($field,$value)->first()` — `$field` is JSON path `transaction_info->post->Authority`. Laravel translates to `JSON_EXTRACT(transaction_info, '$.post.Authority') = ?` (MySQL). Finds order by stored Authority.
- **Authority lookup via JSON path** is the link between ZarinPal redirect param and order — not via `id` in URL.
- If order not found, redirects to `cart.index` with `فاکتور معتبر نمی باشد` (invalid invoice).
- **Duplicate handling if already paid**: `in_array(['paid','deposit_paid'])` → immediate redirect to `checkout.success` without verify — idempotent, prevents double verify and stock double restore.
- Same price calc as checkout — must match token amount.
- `handleVerify` persists `verify` data regardless of success; then branch to `handleSuccessOrderPayment` or `handleFailedPayment`.
- `handleSuccessOrderPayment`: guards `if order_status === 'paid' already redirect` (extra idempotency), else sets `paid` or `deposit_paid` (`intval(deposit_price)!=0 ? deposit_paid : paid`), audits via `AuditService::orderStatusChanged`, notifies user + admins via `NotificationService`.
- `handleFailedPayment`: guards `if order_status === 'unpaid' return` (already failed), else sets `unpaid`, audits, `StockService::restoreStock($order)` (try/catch log).

Evidence: `OrderService.php:162-239`, `OrderService.php:37 findByField`, `OrderService.php:186 handleSuccessOrderPayment`.

### 10.3 Order Statuses

`Order::getStatusAttribute` maps `order_status` values: `paying` (در حال پرداخت), `paid`, `deposit_paid` (بیعانه), `wait_for_verification`, `pending_confirmation` (کارت به کارت), `unpaid`, `cancelled`. Also `getBankTrackingCodeAttribute` extracts RefID vs card code per bank type; `getIsCardToCardAttribute` helper.

Evidence: `Order.php:112-170`.

---

## 11. CheckoutController Callbacks — `app/Http/Controllers/Site/CheckoutController.php` + `routes/site.php`

> **Source:** `CheckoutController.php:270-300`, `site.php:116-121`

```php
Route::prefix('checkout')->name('checkout.')->group(function(){
  Route::any('/finish', [CheckoutController::class,'finishZarinPal'])->name('finish.zarin-pal');
  Route::any('/finish-saman', [CheckoutController::class,'finishSaman'])->name('finish.saman-bank');
  Route::any('/finish-sadad', [CheckoutController::class,'finishSadad'])->name('finish.sadad-bank');
  Route::any('/finish-zibal', [CheckoutController::class,'finishZibal'])->name('finish.zibal-bank');
});
```

- `Route::any` accepts GET|POST|PUT|etc — ZarinPal redirects GET with `?Authority=...&Status=OK`; other gateways may POST.
- **No middleware** (no `auth`) — callbacks are guest-accessible; order lookup via Authority is auth.
- Wrapped in `$siteRoutes` closure which is registered **twice**: once without prefix (canonical) and once under `{locale}` prefix (`Route::prefix('{locale}')->where(localePattern)->name('locale.')->group($siteRoutes)`). So effectively 8 routes: `/checkout/finish` + `/{locale}/checkout/finish`, each for 4 gateways. Named both `checkout.finish.zarin-pal` and `locale.checkout.finish.zarin-pal`.
- `CALLBACK_ROUTE_NAME = 'checkout.finish.zarin-pal'` used in `ZarinPal::getBankToken` generates canonical (non-locale) URL via `route()`.

Controller:

```php
public function finishZarinPal(Request $request){
  $input = $request->all();
  $orderService = app(OrderService::class);
  return $orderService->callback('transaction_info->post->Authority', $input['Authority'] ?? '', $input);
}
public function finishSaman(Request $request){ return $orderService->callback('transaction_info->post->token', $input['Token'] ?? '', $input); }
public function finishSadad(Request $request){ return $orderService->callback('id', $input['OrderId'] ?? '', $input); }
public function finishZibal(Request $request){ return $orderService->callback('transaction_info->post->trackId', $input['trackId'] ?? '', $input); }
```

- ZarinPal callback uses Authority lookup; Saman uses token; Zibal trackId; Sadad uses OrderId (direct `id` column). Only ZarinPal flow is currently wired in BankPaymentService.

Evidence: `site.php:115-121`, `CheckoutController.php:270-300`.

---

## 12. Database — Banks & Orders

### 12.1 `2026_08_26_000007_create_banks_table.php`

```php
Schema::create('banks', function(Blueprint $table){
  $table->id();
  $table->string('title');
  $table->string('icon')->nullable();
  $table->boolean('status')->default(1);
  $table->boolean('admin_test')->default(0);
  $table->string('bank_type');
  $table->json('config')->nullable();
  $table->string('card_number')->nullable();
  $table->string('card_holder_name')->nullable();
  $table->string('card_description')->nullable();
  $table->timestamps();
  $table->softDeletes();
});
```

- `bank_type` is plain string, not enum — free-form risk.
- `config` json nullable holds MerchantId.
- Later migrations add `card_number` etc? Actually all in single migration (per `migrations.md:80` summary shows `title/icon/status/admin_test/bank_type/config` — but `Bank.php fillable` includes `card_*`, so later migration adds them; `schema-dictionary` reflects final shape.
- Seeded via `DatabaseSeeder`? Usually ZarinPal bank seeded with `MerchantId` from env, status 1.

Evidence: `database/migrations/2026_08_26_000007_create_banks_table.php`, `docs/02-database/migrations.md:80`.

### 12.2 Orders `transaction_info` column

- `2026_08_26_000008_create_orders_table.php:41 $table->json('transaction_info')->nullable()->comment('اطلاعات تراکنش (post + verify)')`.
- Cast `array` in `Order.php:29`.
- Shape: `{"post":{"Authority":"000..."},"verify":{"RefID":123}}` for success, or `{"post":{"Authority":"..."},"verify":[]}` before verify, or `{}` if not yet bank.
- `Order::getBankTrackingCodeAttribute` switches on `bank_type` to return `verify.RefID` or card code.

Evidence: `Order.php:29 cast`, `docs/02-database/schema-dictionary.md:518 transaction_info json`.

---

## 13. TransactionInfo JSON Structure & Lifecycle

1. **After `BankPaymentService::handlePayment` success**: `transaction_info = {"post":{"Authority":"A000..."},"verify":[]}`
2. **After `handleVerify` success**: `{"post":{"Authority":"A000..."},"verify":{"RefID":123456}}`
3. **After `handleVerify` failed**: `{"post":{"Authority":"A000..."},"verify":[]}` (empty RefID) + `order_status=unpaid`
4. **Card_to_card**: `transaction_info` stays `null` or `{}` — no post/verify; tracking via `receipt_tracking_code` column.

JSON path queries: `transaction_info->post->Authority` used as WHERE; MySQL JSON_EXTRACT.

Evidence: `BankPaymentService.php:31-35`, `BankPaymentService.php:56-61`.

---

## 14. Duplicate Handling — Idempotency if Already Paid

- `OrderService::callback` first check: `if in_array(order_status, ['paid','deposit_paid']) return redirect success` — no second verify, no status overwrite, no stock restore.
- `handleSuccessOrderPayment` also guards: `if order_status === 'paid' return redirect success` — defensive.
- `handleFailedPayment` guards: `if order_status === 'unpaid' return` — avoids second restoreStock.
- ZarinPal verify code `101` (already verified) treated as success — supports replayed callback.
- Without this, user refreshing callback URL would trigger second `handleVerify` which would re-call ZarinPal verify and possibly set `unpaid` incorrectly if Authority already consumed.

Evidence: `OrderService.php:169-171`, `ZarinPal.php:78 code 101`, `OrderService.php:226 guard`.

---

## 15. Sequence — Happy Path vs Failure

```
Happy ZarinPal: cart -> CheckoutController::create -> OrderService::create (paying) -> OrderService::checkout
  -> BankPaymentService::handlePayment -> ZarinPal::getBankToken (amount.'0' -> request.json code100 Authority)
  -> Order.update transaction_info.post, Auth.loginUsingId, ob_start header Location StartPay/Authority exit
  -> User pays at ZarinPal -> redirect GET /checkout/finish?Authority=A000&Status=OK
  -> CheckoutController::finishZarinPal -> OrderService::callback Authority -> handleVerify
  -> ZarinPal::verifyTransaction (amount.'0' -> verify.json code100 RefID) -> Order.update verify.RefID
  -> handleSuccessOrderPayment -> order_status paid, Audit, Notification, session flash, view checkout.success

Failed token: getBankToken failed -> Order.delete (soft), redirect checkout.failed

Cancelled at gateway: Status!=OK -> handleVerify returns failed -> order_status unpaid -> restoreStock -> redirect failed
Already paid replay: callback finds order_status paid -> immediate redirect success (no verify)
Card-to-card: checkout sees bank_type card_to_card -> order_status pending_confirmation -> redirect success -> admin manual verify
```

Evidence: workflows `payment-zarinpal.md:18-41`.

---

## 16. Dependencies Chain

```
CheckoutController::create → OrderService::create → Order, OrderItem, StockService, NotificationService, AuditService
CheckoutController::create → OrderService::checkout
  ├─ Bank::find
  ├─ if card_to_card → Order.update pending_confirmation → Redirect
  └─ else → BankPaymentService::handlePayment
              ├─ PaymentGatewayFactory::create(Bank) ─┐
              │     └─ ZarinPal::__construct(Bank.config.MerchantId) │
              ├─ ZarinPal::getBankToken(amount) ────────────────┘
              │     └─ Http::withHeaders()->timeout(30)->post(request.json)
              ├─ Auth::loginUsingId
              ├─ Order.update transaction_info.post
              └─ ZarinPal::redirect → header Location StartPay/Authority

CheckoutController::finishZarinPal → OrderService::callback
  └─ BankPaymentService::handleVerify
        ├─ PaymentGatewayFactory::create(Order.bank)
        ├─ ZarinPal::verifyTransaction
        │     └─ Http::post(verify.json)
        ├─ Order.update transaction_info.verify
        └─ Order.update order_status unpaid on fail
```

- `Order` is central; `Bank` config is leaf.
- `Http` facade requires `illuminate/http` + Guzzle; Ssl verified (no `withoutVerifying`).

Evidence: `BankPaymentService.php:19 Factory::create`, `ZarinPal.php:98 Http::withHeaders`, `OrderService.php:140 Bank::find`.

---

## 17. Inputs — Bank Config & Checkout Form

- **Admin bank form** (`admin.banks.create/edit`): `title`, `icon` file, `bank_type` select (zarinPal/card_to_card), `status` checkbox, `admin_test` checkbox, `config` textarea (JSON string e.g. `{"MerchantId":"xxxxxxxx-...-xxxxxxxx"}`), `card_number`, `card_holder_name`, `card_description` (shown only if card_to_card selected via JS).
- **Site checkout form** (`POST /checkout/create` via `CardToCardCheckoutRequest`): `bank_id` required exists:banks,id, `user_description` nullable string, `receipt_tracking_code` nullable required_if bank_type card_to_card, `receipt_payer_card` nullable. Session must have `checkout_address_id`, `checkout_shipping_method_id`, `checkout_shipping_price`.
- **ZarinPal callback input**: `Authority` (string), `Status` (`OK` or `NOK`/`Cancel`), plus any ZarinPal query — only these two used.

Evidence: `BankRequest.php:18-27 rules`, `CheckoutController.php:157 CardToCardCheckoutRequest`, `ZarinPal.php:60 Status check`.

---

## 18. Evidence Index (Finglish)

- `app/Models/Bank.php:12 fillable` — title, icon, status, admin_test, bank_type, config, card_number/holder/description — model-e aslie pardakht.
- `app/Models/Bank.php:15 casts config json, admin_test boolean` — MerchantId dar config ZarinPal zakhire mishe.
- `app/Models/Bank.php:21-33 getItemImageAttribute` — file_exists public_path uploads/bank vs assets/site/bank — aks-e bank.
- `app/Models/Bank.php:35 scopeActive where status=1` — filter bank-haye fa'al.
- `app/Http/Controllers/Admin/BankController.php:24-37 store` — ImageHandler handle bank, json_decode config, admin_test fallback — CRUD-e admin.
- `app/Http/Controllers/Admin/BankController.php:44-66 update` — delete icon qadimi, handle jadid, unset icon agar file nabashe.
- `app/Http/Controllers/Admin/BankController.php:68-76 destroy` — ImageHandler delete + softDeletes.
- `app/Http/Controllers/Admin/BankController.php:78-82 toggleStatus` — flip status.
- `app/Http/Controllers/Admin/BankController.php:87-94 handleImageUpload` — ImageHandler bank folder WebP.
- `app/Helpers/ImageHandler.php:12-26 handle('bank',...)` — upload be uploads/bank WebP.
- `app/Http/Requests/Admin/BankRequest.php:16-39 rules` — title required, icon nullable image 2048, bank_type required, config nullable string — validation.
- `app/Payments/PaymentGateway.php:7-23 interface` — getBankToken, verifyTransaction, redirect.
- `app/Payments/PaymentPostDTO.php:20 fromData success/failed` — DTO post Authority.
- `app/Payments/PaymentVerifyDTO.php:20 fromData` — DTO verify RefID.
- `app/Payments/PaymentGatewayFactory.php:16-18 match zarinPal/card_to_card=>ZarinPal` — dead code card_to_card.
- `app/Payments/ZarinPal.php:20-25 construct MerchantId az config` — noghte-ye hasas (case MerchantId).
- `app/Payments/ZarinPal.php:29 amount.'0' toman->rial` — x10 string concat getBankToken.
- `app/Payments/ZarinPal.php:32-37 payload merchant_id, amount rial, callback checkout.finish.zarin-pal` — request.json.
- `app/Payments/ZarinPal.php:39 sendJsonRequest EP_REQUEST` — Http withHeaders Accept User-Agent timeout30.
- `app/Payments/ZarinPal.php:47 code===100 authority` — request movafagh.
- `app/Payments/ZarinPal.php:58 amount.'0' verify` — dobare x10 dar verifyTransaction.
- `app/Payments/ZarinPal.php:60 Status !== OK => failed` — cancel dar gateway.
- `app/Payments/ZarinPal.php:70 payload verify merchant, authority, amount rial` — verify.json.
- `app/Payments/ZarinPal.php:78 code 100||101 RefID` — verify movafagh (101 already verified).
- `app/Payments/ZarinPal.php:91 header Location StartPay/authority exit` — redirect.
- `app/Payments/ZarinPal.php:98 Http::withHeaders Accept User-Agent timeout30 post` — Ssl ok via Http.
- `app/Services/BankPaymentService.php:19 Factory create` — dependencies chain.
- `app/Services/BankPaymentService.php:26 order->delete() on failed token` — data loss (soft delete).
- `app/Services/BankPaymentService.php:31 transaction_info post verify` — JSON structure.
- `app/Services/BankPaymentService.php:38 ob_start redirect` — header+exit pattern.
- `app/Services/BankPaymentService.php:50 Factory create order.bank` — handleVerify.
- `app/Services/BankPaymentService.php:57 verify merge` — transaction_info verify update.
- `app/Services/OrderService.php:130-152 checkout` — price calc deposit fallback, card_to_card pending_confirmation, else handlePayment.
- `app/Services/OrderService.php:162-184 callback transaction_info->post->Authority, duplicate paid check` — Authority lookup, idempotency.
- `app/Services/OrderService.php:186-222 handleSuccessOrderPayment paid/deposit_paid, Audit, Notification` — success path.
- `app/Services/OrderService.php:224-239 handleFailedPayment unpaid, restoreStock` — failure path.
- `app/Http/Controllers/Site/CheckoutController.php:275 finishZarinPal transaction_info->post->Authority` — callback route.
- `routes/site.php:116-121 ANY /checkout/finish etc` — site checkout finish routes, no auth.
- `routes/admin.php:95-96 resource banks + toggle-status inside auth,adminPermission,log.admin` — admin routes.

---

## 19. Invariants & Gotchas Summary

- Amount must always be toman int via `str_replace(',','',price)` → `intval` → `intval(val.'0')` on gateway; never pass formatted string directly.
- Hard(?) delete on token failure — order soft-deleted, items orphaned, customer loses cart, no audit log — see `gotchas.md`.
- MerchantId missing → gateway sends `merchant_id:''` → always fails → order soft-deleted → data loss loop.
- `card_to_card` no gateway — Factory mapping misleading.
- Authority lookup via JSON path `transaction_info->post->Authority` — requires MySQL 5.7+ JSON support; SQLite in tests may need different syntax.
- Duplicate handling idempotent — already paid orders must not re-verify.
- `ob_start` + `header Location` + `exit` is legacy — breaks Laravel middleware/testing; better `return redirect()->away(EP_REDIRECT.Authority)`.

---

> **AI Checklist for payments:** [x] Bank model dual-type, [x] config MerchantId case, [x] ImageHandler bank WebP, [x] amount.'0' both flows, [x] request vs verify vs StartPay, [x] callback ANY route, [x] Factory dead code, [x] delete on failed token, [x] transaction_info {post,verify}, [x] duplicate paid guard, [x] dependencies Http Ssl ok.
