# Payments / Banks — Dependencies

> **Source:** `app/Models/Bank.php`, `app/Payments/*`, `app/Services/BankPaymentService.php`, `app/Services/OrderService.php`, `app/Helpers/ImageHandler.php`, `routes/*.php`, `config/paths.php`

---

## 1. Direct dependencies — Call graph

```
BankPaymentService
  ├─→ PaymentGatewayFactory::create(Bank $bank, string $siteName)
  │     └─→ ZarinPal::__construct($bank, $siteName)
  │           ├─ Bank->config json (MerchantId)
  │           └─ config('app.name')
  ├─→ ZarinPal::getBankToken(int $amount, Order)   → Http::withHeaders()->timeout(30)->post(request.json)
  ├─→ ZarinPal::verifyTransaction(array $data, int $price, ?Order) → Http::post(verify.json)
  ├─→ ZarinPal::redirect(PostDTO, Order) → header Location StartPay
  ├─→ Auth::loginUsingId(int $user_id)    (IlluminateAuth)
  ├─→ Order::update(['transaction_info'=>[]]) + Order::delete()
  └─→ route('checkout.failed') / response()

OrderService::checkout(Order)
  ├─→ Bank::find(bank_id)
  ├─→ if card_to_card → Order::update(pending_confirmation) (no gateway)
  └─→ else BankPaymentService::handlePayment

OrderService::callback(field,value,data)
  ├─→ Order::findByField(field,value)  (where JSON path)
  └─→ BankPaymentService::handleVerify → OrderService::handleSuccessOrderPayment / handleFailedPayment
        ├─→ StockService::restoreStock
        ├─→ AuditService::orderStatusChanged
        └─→ NotificationService::notifyUser/notifyAdmins

BankController
  ├─→ BankRequest (validation)
  ├─→ ImageHandler::handle('bank', UploadedFile) → ImageUploader::upload (+ WebP) + ImageResizer (optional)
  ├─→ ImageHandler::delete('bank', filename)
  ├─→ Bank::create/update/delete/toggleStatus
  └─→ Bank model (fillable, casts, accessor, scopeActive)

Bank model
  ├─→ config('paths.upload','uploads')  (filesystem path indirection)
  ├─→ public_path() + file_exists() → asset()  (dual-path accessor)
  └─→ SoftDeletes trait (deleted_at)

ZarinPal
  ├─→ Bank->config (MerchantId key)
  ├─→ route('checkout.finish.zarin-pal')  (callback_url)
  ├─→ Http facade (Illuminate\Support\Facades\Http) → Guzzle, timeout 30, Ssl verify true
  └─→ Log::error on Throwable

CheckoutController::finishZarinPal
  └─→ OrderService::callback('transaction_info->post->Authority', Authority, input)

Config/FileSystem
  ├─→ config('paths.upload') — base upload dir, default 'uploads'
  ├─→ public/uploads/bank/{icon}  (new uploads)
  └─→ public/assets/site/bank/{icon}  (legacy fallback)
```

---

## 2. Service → Service

| Caller | Callee | Method | Notes |
|--------|--------|--------|-------|
| `OrderService::checkout` | `BankPaymentService::handlePayment` | `handlePayment(Bank, Order, int price)` | Only for `zarinPal`; card_to_card bypasses |
| `OrderService::callback` | `BankPaymentService::handleVerify` | `handleVerify(Order, array data, int price): bool` | After Authority lookup |
| `OrderService::handleSuccessOrderPayment` | `AuditService::orderStatusChanged` | `orderStatusChanged(Order, old, new, reason)` | Try/catch log |
| `OrderService::handleFailedPayment` | `AuditService` + `StockService::restoreStock` | — | On unpaid |
| `OrderService::create` | `StockService::reserveStock` | — | Before checkout; forceDelete on fail |
| `OrderService::callback success` | `NotificationService::notifyUser/notifyAdmins` | — | `order.paid` / `order.paid_admin` |
| `CheckoutController::create` | `OrderService::create` + `checkout` + `CouponService::recordUsage` + `CartService::clear` | — | Orchestrates full create→pay |
| `BankController::store/update` | `ImageHandler::handle/delete` | `handle('bank', file)` | Folder `bank` |
| `BankController::store/update` | `Bank::create/update` | — | Via `BankRequest validated` |

---

## 3. Model → Model / Traits

| Model | Relation/Trait | Target | Description |
|-------|----------------|--------|-------------|
| `Bank` | `SoftDeletes` | `deleted_at` column | Soft delete; orders relation breaks without withTrashed |
| `Order` | `BelongsTo Bank` | `Bank $bank` via `bank_id` | `Order::bank()` plain belongsTo — null if bank soft-deleted |
| `Order` | casts `transaction_info=>array`, `address=>array` | JSON columns | Auto decode/encode |
| `Order` | `SoftDeletes` | `deleted_at` | Orders soft-deletable; BankPaymentService delete is soft |

---

## 4. External / Facades

| Dependency | Usage | File | Note |
|------------|-------|------|------|
| `Illuminate\Support\Facades\Http` | `Http::withHeaders(['Accept'=>'application/json','User-Agent'=>'ZarinPal Rest Api v1'])->timeout(30)->post(url, payload)` | `ZarinPal.php:98` | Ssl verify true by default (ok). No `withoutVerifying`. Timeout 30s. |
| `Illuminate\Support\Facades\Auth` | `Auth::loginUsingId($order->user_id)` | `BankPaymentService.php:23,54` | Logs in order owner for success page check `auth()->id()===order->user_id` |
| `Illuminate\Support\Facades\Log` | `Log::error('ZarinPal request failed',...)` | `ZarinPal.php:111` | Only on Throwable; not on `code !=100`. |
| `route()` helper | `route('checkout.finish.zarin-pal')` | `ZarinPal.php:34` | Generates absolute callback_url; must match `Route::any /checkout/finish` |
| `header()` + `exit` | `header('Location: '.EP_REDIRECT.Authority); exit;` | `ZarinPal.php:91` | Legacy redirect, bypasses Laravel response |
| `config('paths.upload','uploads')` | Upload base dir | `Bank.php:28`, `ImageHandler.php:37` | Indirection; default `uploads` |
| `config('app.name','Shop')` | Site name for description | `ZarinPal.php:35`, `BankPaymentService.php:19` | `Shop خرید از Shop` |

---

## 5. Config & Env

- `config/paths.upload` — base upload folder name, default `uploads`. Changing without moving files breaks `Bank::getItemImageAttribute` and `ImageHandler` paths.
- ZarinPal MerchantId is **not** env directly — stored per-bank in `banks.config->MerchantId` via admin form; seeded banks may pull from env `ZARINPAL_MERCHANT_ID` in seeder.
- No `services.zarinpal` config — ZarinPal endpoints are hardcoded constants `EP_REQUEST/VERIFY/REDIRECT`.

---

## 6. Coupling & Reusable

- `PaymentGateway` interface — swappable; but Factory currently hard-codes `ZarinPal` for both types.
- `ImageHandler` — reusable across modules (`Product`, `HeroSlide`, `Brand`, `Bank`) same `handle(folder,file)` pattern.
- `BankPaymentService` is single-responsibility gateway orchestrator; `OrderService` owns order lifecycle.
- Tight coupling: `OrderService` instantiates `Bank::find` directly — no repository; `BankPaymentService` calls `Auth::loginUsingId` coupling to auth.

---

## 7. Evidence — Finglish

- `app/Services/BankPaymentService.php:19 PaymentGatewayFactory::create` — vasate beyne OrderService va ZarinPal.
- `app/Payments/PaymentGatewayFactory.php:16 match zarinPal/card_to_card=>ZarinPal` — Factory be ZarinPal vabaste ast.
- `app/Payments/ZarinPal.php:98 Http::withHeaders ... timeout(30)` — Http facade baraye ZarinPal API.
- `app/Models/Bank.php:28 config('paths.upload')` — bank aks dar uploads/bank.
- `app/Http/Controllers/Admin/BankController.php:5 ImageHandler` — admin CRUD be ImageHandler vabaste ast.
- `app/Services/OrderService.php:22 BankPaymentService inject` — OrderService be BankPaymentService vabaste.
