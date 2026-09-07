# Inventory Reservation & Locking

> **Source of Truth:** `app/Services/StockService.php:1-220` , `app/Services/OrderService.php:96-102` , `app/Models/Product.php:16-32` , `app/Models/ProductVariant.php:15-45` , `app/Models/InventoryTransaction.php:1-25` , `app/Observers/OrderObserver.php:41-53` , `app/Console/Commands/CancelExpiredOrders.php:46-55`

## 1. Reserve — Vaghti `Order::create` Seda Zade Mishavad

**Trigger:** `OrderService::create:96-102` — bad az `Order::create` + `OrderItem::create` loop, **ghabl** az `AuditService` va `NotificationService`.

```php
try {
    $this->stockService->reserveStock($order);
} catch (\RuntimeException $e) {
    Log::warning('Stock reservation failed ...');
    $order->forceDelete(); // soft-delete hast vali forceDelete → hazf-e kamel + OrderItem orphan
    throw $e; // be CheckoutController → redirect checkout.failed
}
```

**Tables:** `orders` (forceDelete agar fail), `products.stock` / `product_variants.stock` (decrement), `inventory_transactions` (type=subtract), `audit_logs` (via AuditService).

### 1.1 `StockService::reserveStock` — Step-by-Step

```php
public function reserveStock(Order $order): bool {
    $items = $order->items()->get(); // OrderItem where quantity>0
    if ($items->isEmpty()) return true;
    return DB::transaction(function() use($order, $items){
        foreach($items as $item) $this->decreaseItemStock($item->product_id, $item->product_variant_id, $item->quantity, $order->id);
        return true;
    });
}
private function decreaseItemStock(int $productId, ?int $variantId, int $qty, int $orderId): void {
    if ($variantId) $this->decreaseVariantStock(productId, variantId, qty, orderId);
    else $this->decreaseProductStock(productId, qty, orderId);
}
```

#### `decreaseProductStock` — Product-e Sade (bedoun variant)

```php
private function decreaseProductStock(int $productId, int $quantity, int $orderId): void {
    $product = Product::where('id',$productId)->lockForUpdate()->first(); // ← PESSIMISTIC LOCK
    if (!$product) { Log::warning("Product #{$productId} not found"); return; }
    if ((int)$product->stock < $quantity) {
        throw new RuntimeException("موجودی محصول «{$product->trans('name')}» کافی نیست. موجودی فعلی: {$product->stock}، درخواستی: {$quantity}");
    }
    $product->decrement('stock', $quantity); // UPDATE products SET stock = stock - qty
    InventoryTransaction::create([
        'product_id'=>$productId, 'quantity'=>$quantity, 'type'=>'subtract', 'order_id'=>$orderId,
        'description'=>"رزرو موجودی — سفارش #{$orderId}",
    ]);
    AuditService::productStockChanged($product, $old=$product->stock+$quantity, $new=$product->stock, 'رزرو سفارش #'.$orderId, [order_id, quantity]);
}
```

#### `decreaseVariantStock` — Product Variant-dar

```php
private function decreaseVariantStock(int $productId, int $variantId, int $quantity, int $orderId): void {
    $variant = ProductVariant::where('id',$variantId)->lockForUpdate()->first(); // ← LOCK variant row
    if (!$variant) { Log::warning(...); return; }
    if ((int)$variant->stock < $quantity) {
        throw new RuntimeException("موجودی واریانت «{$variant->label}» کافی نیست. موجودی: {$variant->stock}، درخواستی: {$quantity}");
    }
    $variant->decrement('stock', $quantity);
    $product = Product::find($productId);
    if ($product) { $sumStock = $product->variants()->sum('stock'); $product->update(['stock'=>$sumStock]); } // aggregate
    InventoryTransaction::create(['product_id'=>$productId, 'product_variant_id'=>$variantId, 'quantity'=>$quantity, 'type'=>'subtract', 'order_id'=>$orderId, ...]);
    AuditService::productStockChanged(...);
}
```

**Math:** `new_stock = old_stock - quantity` . Stock manfi nemishavad — ghabl az decrement check `if stock < quantity → throw` . Hich `CHECK (stock >=0)` dar DB nist — hifazat faghat dar code.

## 2. Race Condition Handling — Lock-e Daghigh

| Lock | File:Line | Type | Scope |
|------|-----------|------|-------|
| `Product::where(id)->lockForUpdate()->first()` | `StockService.php:107` | **Pessimistic row lock** (`SELECT ... FOR UPDATE`) dar `DB::transaction` | Reserve product sade |
| `ProductVariant::where(id)->lockForUpdate()->first()` | `StockService.php:131` | **Pessimistic row lock** dar `DB::transaction` | Reserve variant |
| Haman 2 lock dar `increaseProductStock/VariantStock` | `StockService.php:172,190` | Pessimistic | Restore |
| Hich Redis lock / `Cache::lock` | `grep Cache::lock app/ → 0` | — | — |
| Hich `DB::transaction` dar `OrderService::create` khodash | `OrderService.php:53-94` hich transaction nist | — | Order create + OrderItem loop **kharej** az transaction-e StockService |

### 2.1 Race Scenario — 2 User Hamzaman Akharin Kala Ra Bekharand

```
T0  stock = 1
T1  User A → OrderService::create → Order A created (paying)
T2  User B → OrderService::create → Order B created (paying)  // har do order sakhte shod — kharej az transaction!
T3  A → StockService::reserveStock → DB::transaction begin → SELECT ... FOR UPDATE (product row locked)
T4  B → StockService::reserveStock → DB::transaction begin → SELECT ... FOR UPDATE (BLOCKED — montazer-e A)
T5  A → check stock=1 >= qty=1 → decrement → stock=0 → commit → lock azad
T6  B → lock gerefte → check stock=0 < qty=1 → throw RuntimeException → Order B forceDelete → User B error "موجودی کافی نیست"
Result: faghat 1 order baghi mimanad — dorost.
```

**Highlight — Window-e Risk:** `Order::create` + `OrderItem::create` **kharej** az `DB::transaction` hastand. Agar 2 request hamzaman order besazand, har do order sakhte mishavad va bad yeki forceDelete mishavad. Behtar bud `Order::create` ham dakhel haman transaction bashad ta orphan `OrderHistory` ijad nashavad.

### 2.2 `validateCartItems` — **Bedoun Lock** (Pre-check)

```php
public function validateCartItems(array $cartItems): array {
    foreach $cartItems as item:
        currentStock = getAvailableStock(productId, variantId) // Product::withTrashed()->find() — NO lockForUpdate
        if currentStock <=0: unavailable[]
        else if currentStock < requestedQty: insufficient[]
    return [unavailable, insufficient]
}
public function getAvailableStock(int $productId, ?int $variantId): int {
    if variantId: return max(0, (int) ProductVariant::withTrashed()->find(variantId)->stock ?? 0)
    else: return max(0, (int) Product::withTrashed()->find(productId)->stock ?? 0)
}
```

In method faghat dar `CheckoutController::validateStock` (AJAX) seda zade mishavad — **hifazat-e asli nist**, faghat UX. Race hanuz dar `reserveStock` ba lock hal mishavad.

## 3. Restore — `StockService::restoreStock`

**Triggers:** `OrderService::handleFailedPayment` (unpaid), `OrderService::cancelOrder` (cancelled), `CancelExpiredOrders` (cancelled), `OrderObserver::updated` (cancelled duplicate).

```php
public function restoreStock(Order $order): bool {
    $items = $order->items()->get();
    if ($items->isEmpty()) return true;
    return DB::transaction(function() use($order,$items){
        foreach($items as $item) $this->increaseItemStock(productId, variantId, quantity, orderId);
    });
}
private function increaseProductStock(int $productId, int $quantity, int $orderId): void {
    $product = Product::where('id',$productId)->lockForUpdate()->first();
    if (!$product) { Log::warning(...); return; }
    $product->increment('stock', $quantity);
    InventoryTransaction::create(['product_id'=>$productId, 'quantity'=>$quantity, 'type'=>'add', 'order_id'=>$orderId, 'description'=>"بازگردانی موجودی — سفارش #{$orderId} لغو شده"]);
    AuditService::productStockChanged($product, $old=$product->stock-$quantity, $new=$product->stock, 'بازگردانی سفارش #'.$orderId, [order_id, quantity, reason=>'cancel_or_fail']);
}
private function increaseVariantStock(...): void {
    $variant = ProductVariant::where('id',$variantId)->lockForUpdate()->first();
    $variant->increment('stock', $quantity);
    $product = Product::find(productId); $product->update(['stock'=> $product->variants()->sum('stock')]);
    InventoryTransaction::create([... type=>'add', product_variant_id=>variantId ...]);
}
```

**Math:** `new_stock = old_stock + quantity` . Hich check-e max stock nist.

**Duplicate gotcha:** `cancelOrder` + `OrderObserver::updated` har do `restoreStock` ra seda mizanand → 2× increment + 2× `InventoryTransaction type=add` → stock 2 barabar ziad mishavad. Bayad idempotent shavad (masalan `wasChanged` guard ya unique key bar `inventory_transactions`).

## 4. `InventoryTransaction` — Ledger

**File:** `app/Models/InventoryTransaction.php` — `SoftDeletes`, fillable `[product_id, product_variant_id, quantity, type, order_id, description]`, relations `product`, `productVariant`, `order`.

| Field | Type | Mesal |
|-------|------|-------|
| `type` | `subtract` (reserve) / `add` (restore) | `subtract` dar reserveStock |
| `quantity` | int | `2` |
| `product_variant_id` | nullable | null baraye product sade |

Hich unique constraint bar `(order_id, product_id, variant_id, type)` nist — duplicate-e gotcha emkan pazir.

## 5. Pseudo-Code — Kamel

```php
// RESERVE — called inside OrderService::create after Order+OrderItems created
function reserveStock(Order order): bool {
    items = order.items()->get() // quantity>0
    if items.empty: return true
    return DB::transaction(() => {
        foreach item in items:
            if item.product_variant_id:
                variant = ProductVariant::where(id=item.product_variant_id)->lockForUpdate()->first()
                if !variant: log warning; continue
                if variant.stock < item.quantity: throw RuntimeException("موجودی واریانت کافی نیست ...")
                variant.decrement('stock', item.quantity)
                product = Product::find(item.product_id)
                if product: product.stock = product.variants()->sum('stock'); product.save()
                InventoryTransaction::create(product_id, variant_id, quantity, type='subtract', order_id)
                AuditService::productStockChanged(product, old, new, 'رزرو واریانت سفارش #orderId')
            else:
                product = Product::where(id=item.product_id)->lockForUpdate()->first()
                if !product: log warning; continue
                if product.stock < item.quantity: throw RuntimeException("موجودی محصول کافی نیست ...")
                product.decrement('stock', item.quantity)
                InventoryTransaction::create(product_id, quantity, type='subtract', order_id)
                AuditService::productStockChanged(product, old, new, 'رزرو سفارش #orderId')
    })
}

// RESTORE — idempotent nabayad 2 bar seda zade shavad
function restoreStock(Order order): bool {
    items = order.items()->get()
    if items.empty: return true
    return DB::transaction(() => {
        foreach item in items:
            if variant_id: variant.lockForUpdate().increment(qty); product.stock = sum(variants.stock)
            else: product.lockForUpdate().increment(qty)
            InventoryTransaction::create(... type='add' ...)
            AuditService::productStockChanged(..., reason='cancel_or_fail')
    })
}

// PRE-CHECK — no lock, UX only
function validateCartItems(cartItems): [unavailable[], insufficient[]] {
    foreach item: currentStock = getAvailableStock(productId, variantId) // no lock
        if currentStock<=0: unavailable[]
        else if currentStock < qty: insufficient[]
}
```

## 6. Invariants baraye Vibe Coding

- **Hifazat-e asli `lockForUpdate` dar `DB::transaction` ast** — `validateCartItems` hifazat nist. Hargiz `reserveStock` ra bedoun-e transaction seda nazan.
- **Order create kharej az transaction** — agar reserve fail shavad `forceDelete` mishavad. Agar shoma `Order::create` ra dakhel transaction-e `reserveStock` bebarid, orphan kamtar mishavad vali `OrderObserver::created` ham dakhel transaction khahad bud.
- **Variant aggregate:** `product.stock = sum(variants.stock)` bad az har variant decrement/increment. Agar product variant-dar ast vali `product.stock` mostaghim decrement shavad bug ast — hamishe az `variant_id` branch estefade kon.
- **Duplicate restore ra hal kon:** ya `OrderObserver::updated` restore ra hazf kon (chon `cancelOrder` khodash restore mikonad), ya `InventoryTransaction` unique key ezafe kon.
