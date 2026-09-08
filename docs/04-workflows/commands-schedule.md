# Commands & Scheduling

> **Source of Truth:** `app/Console/Commands/*.php` , `routes/console.php` , `bootstrap/app.php` — `app/Console/Kernel.php` dar project **vojoud nadarad** (Laravel 11 skeleton). Tanzim-e schedule dar `routes/console.php` ba `Schedule` facade ast.

## 1. Inventory — Console Commands

| # | Command Class | File | Signature | Description | Queue? |
|---|---------------|------|-----------|-------------|--------|
| 1 | `CancelExpiredOrders` | `app/Console/Commands/CancelExpiredOrders.php` | `orders:cancel-expired {--minutes=15 : تعداد دقیقههای انتظار} {--dry-run : فقط نمایش بدون اجرا}` | لغو خودکار سفارشات `order_status=paying` ke az `minutes` ghabl sakhte shode + `StockService::restoreStock()` | **SYNC EXECUTION** — via Scheduler, na queue |
| 2 | `LangNew` | `app/Console/Commands/LangNew.php` | `lang:new {code : کد زبان} {--from=fa : زبان مبدأ}` | Sakht-e `resources/lang/{code}` ba copy az `resources/lang/{from}` — tooling-e tarjome, hich rabti be runtime nadarad | — (manual) |

### 1.1 `CancelExpiredOrders` — Logic-e Asli (Step-by-Step)

**File:** `app/Console/Commands/CancelExpiredOrders.php:16-65`

```
Step 1  $minutes = (int) $this->option('minutes')   // default 15
        $dryRun  = $this->option('dry-run')          // boolean
        $cutoff  = now()->subMinutes($minutes)

Step 2  $expiredOrders = Order::where('order_status','paying')
                          ->where('created_at','<',$cutoff)->get()

Step 3  agar empty → info("هیچ سفارش منقضی شدهای یافت نشد.") → SUCCESS

Step 4  $stockService = app(StockService::class); $cancelled=0; $failed=0

Step 5  loop har $order:
          line("#{$id} | user:{$user_id} | {$created_at} | مبلغ: {payment_price}")
          agar $dryRun → line("[DRY-RUN] لغو نشد.") → continue
          try:
            $previousStatus = $order->order_status
            $order->update(['order_status'=>'cancelled'])   // Observer fires → OrderHistory only (P0-02 fixed: no restoreStock in observer)
            $stockService->restoreStock($order)              // DB::transaction + InventoryTransaction type=add
            Log::info("Auto-cancelled expired order #{$id}", [previous_status, created_at, payment_price])
            $cancelled++
          catch(Exception $e):
            Log::error("Failed to auto-cancel order #{$id}", [message=>$e->getMessage()])
            failed++; error("خطا در لغو سفارش #{$id}: ...")

Step 6  info("نتیجه: {$cancelled} سفارش لغو شد" + failed? ", {$failed} خطا")
        return SUCCESS
```

**Tables touched:** `orders` (update), `inventory_transactions` (via StockService), `order_histories` (via Observer), `audit_logs` (via StockService→AuditService)

**Idempotency:** `where order_status='paying'` → agar order ghablan `cancelled` shode, dobare select nemishavad. `restoreStock` SINGULAR (Service only — P0-02 fixed: observer has no stock logic).

### 1.2 `LangNew` — Logic

**File:** `app/Console/Commands/LangNew.php:25-60`

```
validate code /^[a-z]{2,8}$/ → fail agar na
check is_dir(resources/lang/{from}) → fail agar na
agar is_dir(resources/lang/{code}) → warn "از قبل وجود دارد" → SUCCESS
File::makeDirectory(resources/lang/{code}, 0755, true)
foreach File::glob(resources/lang/{from}/*.php) → File::copy → $copied++
agar $copied===0 → deleteDirectory(target) → error "هیچ فایل ترجمهای یافت نشد" → FAILURE
info("پوشهی resources/lang/{code} ساخته شد ({copied} فایل از {from})")
```

Hich schedule nist — manual `php artisan lang:new de` .

## 2. Scheduling — `routes/console.php` (Laravel 11)

**File:** `routes/console.php` — kamel:

```php
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Inspiring;

Artisan::command('inspire', fn()=> $this->comment(Inspiring::quote()))->purpose('Display an inspiring quote')->hourly();

Schedule::command('orders:cancel-expired --minutes=15')->everyFiveMinutes()->withoutOverlapping();
```

| Cron Entry | Command | Expression | Cron | `withoutOverlapping` | File |
|------------|---------|------------|------|----------------------|------|
| `orders:cancel-expired --minutes=15` | `CancelExpiredOrders` | `everyFiveMinutes()` | `*/5 * * * *` | ✅ (mutex file, `schedule:clear-cache` baraye pak kardan) | `routes/console.php:10` |
| `inspire` | Closure | `hourly()` | `0 * * * *` | ❌ | `routes/console.php:6` |

**`app/Console/Kernel.php` vojoud nadarad** — dar Laravel 11 schedule dar `routes/console.php` tarif mishavad, na `Kernel::schedule()`. `bootstrap/app.php` ba `->withRouting(commands: __DIR__.'/../routes/console.php')` an ra load mikonad.

### 2.1 Ejraye Schedule — Che Bayad Ruye Server Bashad

```
# Crontab — har daghighe schedule:run ra seda bezan
* * * * * cd /path/to/shopora && php artisan schedule:run >> /dev/null 2>&1
# Ya: php artisan schedule:work (foreground worker, baraye dev)
```

`schedule:run` har daghighe check mikonad aya `everyFiveMinutes` due ast? Agar bale, `orders:cancel-expired --minutes=15` ra ejra mikonad. `withoutOverlapping` jaloye ejraye hamzaman ra migirad — agar yek run >5min tul bekeshad, run-e badi skip mishavad (dar in command run sari ast, <1s).

### 2.2 Taiming-e Vaghei-e Cancel — Gotcha

`created_at < now-15min` + `everyFiveMinutes` → order 15min baad cancel **nemishavad** daghighan:

- Order 14:59 sakhte → cron 15:00 `cutoff=14:45` → 14:59 > 14:45 → **select nemishavad**
- Cron 15:05 `cutoff=14:50` → 14:59 > 14:50 → hanooz na
- Cron 15:10 `cutoff=14:55` → 14:59 > 14:55 → hanooz na
- Cron 15:15 `cutoff=15:00` → 14:59 < 15:00 → **cancel**

→ Max 20min, avg ~17.5min. Dar `docs/03-modules/orders/gotchas.md` va `order-lifecycle.md` ham sabt shode.

## 3. Schedule List — Vaghti `php artisan schedule:list` Ejra Shavad

```
  0 * * * *  inspire ............................. Next Due: 13:00
  */5 * * * *  orders:cancel-expired --minutes=15 . Next Due: 12:35  Without Overlapping
```

## 4. Vibe Coding — Agar Command Jadid Mikhaid

- `php artisan make:command SyncInventory` → `signature='inventory:sync {--dry-run}'`, `description='...'`
- Dar `routes/console.php`: `Schedule::command('inventory:sync')->dailyAt('02:00')->withoutOverlapping()->onOneServer()` (agar multi-server)
- Baraye job sangin: dar `handle()` be jaye logic mostaghim, `MyJob::dispatch()->onQueue('low')` kon — vali queue worker niaz darad.
- Log-e command ra ba `Log::info/error` mesle `CancelExpiredOrders` anjam bede — hich `failed()` nadarad, khod command bayad per-item try/catch konad.
