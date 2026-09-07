# Jobs & Queues

> **Source of Truth:** `app/Jobs/` **vojoud nadarad**, hich `ShouldQueue` / `dispatch()` dar `app/` nist. `config/queue.php` default `database` ast vali hich job dispatch nemishavad. Hame flow-ha **SYNC EXECUTION** dar request lifecycle ejra mishavand. In file daghighan moshakhas mikonad che chizi queue nist, queue config chegoune ast, va agar Vibe Coding job jadid besazad che bayad raayat konad.

## 1. Natije Audit — Hich Job Vojoud Nadarad

| Check | Natije | Evidence |
|-------|--------|----------|
| `app/Jobs/*.php` | ❌ Directory vojoud nadarad | `ls app/Jobs → No such file` |
| `ShouldQueue` / `Dispatchable` / `InteractsWithQueue` / `Queueable` | ❌ 0 hit | `grep -rn ShouldQueue app/ → 0` |
| `dispatch(` / `dispatchSync` / `Bus::dispatch` | ❌ 0 hit | `grep -rn "dispatch(" app/ → 0` |
| `jobs` table | ✅ Migration vojoud darad (Laravel default) | `database/migrations/*_create_jobs_table.php` |
| `failed_jobs` table | ✅ Config shode | `config/queue.php: failed.driver=database-uuids` |
| Queue worker command | ⚠️ Niaz nist — hich job dispatch nemishavad | — |

## 2. Queue Config — Che Tasmim Gerefte Shode

**File:** `config/queue.php`

```php
'default' => env('QUEUE_CONNECTION', 'database'), // .env: QUEUE_CONNECTION=database
'connections' => [
  'sync'     => ['driver'=>'sync'],
  'database' => ['driver'=>'database', 'table'=>'jobs', 'queue'=>'default', 'retry_after'=>90, 'after_commit'=>false],
  'redis'    => ['driver'=>'redis', ...],
]
'failed' => ['driver'=>'database-uuids', 'table'=>'failed_jobs']
```

**File:** `.env` / `.env.example`

```
QUEUE_CONNECTION=database
```

**Tahlil:** Queue driver `database` entekhab shode vali `after_commit=false` → agar زمانی transaction dashte bashid va job dispatch konid, job ghabl az commit dispatch mishavad (risk). Felan mohem nist chon dispatch vojoud nadarad. `retry_after=90` seconds.

**Execution mode felan:** Hame chiz `sync` — yani `database` queue config shode vali hich worker (`php artisan queue:work`) niaz nist va hich job dar `jobs` table gharar nemigirad.

## 3. Har Job-e Manteghi — Dar Vaghe SYNC Service Call

> Har radif yek "job"e manteghist ke dar Laravel-e queue-mehvar ba `ShouldQueue` mishod, vali injā be surat **SYNC EXECUTION** tavassote service method-e mostaghim ejra mishavad. Jadval queue/connection/retry/timeout ra bar asas config-e fe'li + pishnahad-e Vibe Coding por karde.

| # | Job-e Manteghi (Service Method) | Queue (felan) | Connection (felan) | Retry / Timeout (felan) | `handle()` Logic — Step-by-Step | `failed()` Logic |
|---|----------------------------------|---------------|--------------------|--------------------------|----------------------------------|------------------|
| 1 | **ReserveStock** — `StockService::reserveStock(Order $order)` | — (SYNC) | — | — / — | 1) `$order->items()->get()` 2) `DB::transaction` + loop `decreaseItemStock` 3a) `Product::lockForUpdate()->decrement('stock', qty)` ya `ProductVariant::lockForUpdate()->decrement` + `products.stock = sum(variants.stock)` 4) `InventoryTransaction::create(product_id, variant_id, qty, type=subtract, order_id, description)` 5) `AuditService::productStockChanged` (try/catch) | `RuntimeException` agar `stock < qty` → caller (`OrderService::create`) `forceDelete()` order + `Log::warning` + `throw` |
| 2 | **RestoreStock** — `StockService::restoreStock(Order $order)` | — (SYNC) | — | — / — | 1) `DB::transaction` + loop `increaseItemStock` 2) `lockForUpdate()->increment('stock', qty)` + `InventoryTransaction type=add` 3) `AuditService::productStockChanged` | `Log::error` dar caller + Observer — stock restore fail nemishkanad flow ra |
| 3 | **SendOtpSms** — `OtpService::send()` → `Sms::sendLookup()` | — (SYNC cURL) | — | timeout 30s / connect 15s | 1) `normalizeMobile` + `isValidMobile` 2) `User::where(mobile)` — agar jadid: `User::create` + `WelcomeDiscountService::createForUser` + `NotificationService::notifyUser('discount.welcome')` 3) `generateCode()` 4 random digits + `user->update(confirm_code, confirm_code_expires_at=now+2min)` 4) `Sms::sendLookup(template, [token=>code, token2=>siteName], mobile)` → `curl GET https://api.kavenegar.com/v1/{token}/verify/lookup.json?...` | `return ['success'=>false, 'message'=>__('messages.otp_sms_failed')]` + `Log::error` |
| 4 | **CreateNotification (user)** — `NotificationService::notifyUser()` | — (SYNC DB) | — | — / — | 1) `TYPES[$type] ?? [icon=>🔔,color=>accent]` 2) `Notification::create(notifiable_type=User::class, notifiable_id, type, title, body, icon, link, data, is_read=false)` | `try/catch Throwable → Log::error` dar caller — notification fail nemishkanad checkout ra |
| 5 | **CreateNotification (admins)** — `NotificationService::notifyAdmins()` | — (SYNC DB loop) | — | — / — | 1) `User::where(is_admin,is_active)->get()` 2) loop + `Notification::create` per admin 3) `return $created` count | Hamān try/catch |
| 6 | **AuditLog** — `AuditService::orderCreated / orderStatusChanged / productStockChanged` | — (SYNC DB) | — | — / — | `AuditLog::create(event, auditable_type, auditable_id, user_id=Auth::id(), old_values, new_values, meta, description)` | `try/catch → Log::error` |
| 7 | **OrderHistory** — `OrderObserver::created/updated` | — (SYNC DB) | — | — / — | `OrderHistory::create(order_id, order_status, shipping_status_id)` | — |
| 8 | **Expired Order Cancel** — `CancelExpiredOrders` Command (Schedule) | — (SYNC via Scheduler, na queue) | — | — / — | Section 4 — `Order::where(order_status=paying, created_at < now-15min)->get()` → loop `update(cancelled)` + `StockService::restoreStock` | `Log::error` per order + `$failed++` |

### 3.1 Handle Detail — ReserveStock (Mesal-e Kamel)

```php
// app/Services/StockService.php: reserveStock()
public function reserveStock(Order $order): bool {
  $items = $order->items()->get();                    // Eager load OrderItem
  return DB::transaction(function() use($order,$items){
    foreach($items as $item) $this->decreaseItemStock($item->product_id, $item->product_variant_id, $item->quantity, $order->id);
  });
}
private function decreaseProductStock(int $productId, int $qty, int $orderId): void {
  $product = Product::where('id',$productId)->lockForUpdate()->first(); // Pessimistic lock
  if((int)$product->stock < $qty) throw new RuntimeException("موجودی کافی نیست ...");
  $product->decrement('stock', $qty);
  InventoryTransaction::create(['product_id'=>$productId,'quantity'=>$qty,'type'=>'subtract','order_id'=>$orderId,'description'=>"رزرو موجودی — سفارش #{$orderId}"]);
  AuditService::productStockChanged($product, $old, $new, "رزرو سفارش #{$orderId}");
}
```

**Tables touched:** `products.stock`, `product_variants.stock`, `inventory_transactions`, `audit_logs`

## 4. Failed Job Strategy — Felan Che Mishavad

Hich `failed()` method dar Job class vojoud nadarad chon Job class vojoud nadarad. Error handling be surat `try/catch` dar caller anjam mishavad:

| Caller | Catch | Action |
|--------|-------|--------|
| `OrderService::create()` — reserveStock fail | `catch RuntimeException` | `Log::warning` + `$order->forceDelete()` + `throw $e` (be controller) |
| `OrderService::create()` — Audit fail | `catch Throwable` | `Log::error` — flow edame |
| `OrderService::create()` — Notification fail | `catch Throwable` | `Log::error` — flow edame |
| `OrderObserver::updated()` — restoreStock fail | `catch Exception` | `Log::error('OrderObserver: Stock restoration failed...')` |
| `CancelExpiredOrders::handle()` — per order | `catch Exception` | `Log::error("Failed to auto-cancel order #{$id}")` + `$failed++` |
| `OtpService::send()` — Kavenegar fail | `catch Throwable` | `Log::error("Kavenegar OTP exception...")` + return `['success'=>false]` |

**`failed_jobs` table** config shode vali hich record dar an neveshte nemishavad — chon dispatch nist.

## 5. Vibe Coding — Agar Job Queue Mikhaid

> In bakhsh pishnahad ast, na mojoud — **hich code taghir nadade**.

Agar mikhahid `NotificationService` ya `Sms` ra async konid:

1. `php artisan make:job SendNotificationJob` → `implements ShouldQueue` + `use Dispatchable, InteractsWithQueue, Queueable, SerializesModels`
2. `public $tries = 3; public $backoff = [10, 30, 60]; public $timeout = 30; public $queue = 'notifications';`
3. `handle(NotificationService $svc)` → haman `notifyUser/notifyAdmins` logic
4. `failed(Throwable $e)` → `Log::error('SendNotificationJob failed', ['type'=>$this->type, 'error'=>$e->getMessage()])` + `Notification::create` baraye admin ya `Sms::sendLookup` fallback
5. Dispatch: `SendNotificationJob::dispatch($user, $type, $title, ...)->onQueue('notifications')` be jaye `app(NotificationService::class)->notifyUser(...)`
6. `.env`: `QUEUE_CONNECTION=database` hefz — `php artisan queue:work --queue=notifications,default` + `schedule: queue:work` ya supervisor
7. **Idempotency:** `restoreStock` ra idempotent konid — `InventoryTransaction` unique key `(order_id, product_id, variant_id, type)` ya check `wasChanged` ghabl az restore.

**Nokte:** `Sms::execute()` felan `CURLOPT_SSL_VERIFYPEER=0` va `curl` mostaghim — dar Job async mitavan `Http::withOptions(['verify'=>false])->timeout(30)` mesle `ZarinPal::sendJsonRequest` estefade kard.
