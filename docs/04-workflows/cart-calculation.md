# Cart Calculation Engine

> **Source of Truth:** `app/Services/CartService.php:333-384` , `app/Services/CouponService.php:84-145` , `app/Services/ShippingCalculator.php:14-95` , `app/Services/OrderService.php:43-81` , `app/Models/Product.php:23-30,195-218` , `app/Models/ProductVariant.php:20-23`

## 1. Price Model — Se Gheymati-e Product

| Field (DB) | Cast | Mani | Source |
|------------|------|------|--------|
| `price` | integer | Gheymat-e asli (bedoun takhfif) | `Product.php:28` |
| `discount_price` | integer | Gheymat ba takhfif (0 agar nadarad) | — |
| `final_price` | integer | Gheymat-e nahayi-e namayesh | `Product.php:195-204` — `if final_price>0 return final_price else discount_price>0 ? discount_price : price` |
| Variant `price/discounted_price/final_price` | float | Haman baraye variant | `ProductVariant.php:20-22` |

`Product::getHasDiscount` = `final_price>0 && discount_price>0 && discount_price < price` . `discount_percentage = round((price-final_price)/price*100)` .

Cart hamishe `price = variant ? variant.final_price : product.final_price` + `original_price = variant ? variant.price : product.price` zakhire mikonad — `original_price` baraye tashkhis-e `isDiscountedProduct` dar coupon.

## 2. CartService — Formula-haye Daghigh

### 2.1 `subtotal(): float` — `CartService.php:333`

```php
public function subtotal(): float {
    return (float) $this->content()->sum(fn($item) => ($item['price'] ?? 0) * ($item['qty'] ?? 0));
}
// = Σ_i (final_price_i * qty_i)
// price hamishe final_price ast (ba takhfif-e product), na price-e kham
// hich round/ceil — jame kham-e float
```

### 2.2 `discount(): float` — `CartService.php:338`

```php
public function discount(): float {
    $coupon = session('coupon');
    if (!$coupon) return 0;
    return app(CouponService::class)->calculateForCart($this);
}
// = CouponService::calculateForCart(cart)
// tafsil dar discount-engine.md section 4 — per-item eligibility + cap min(totalDiscount, subtotal)
```

### 2.3 `total(): float` — `CartService.php:351`

```php
public function total(): float {
    return max(0, $this->subtotal() - $this->discount());
}
// = max(0, subtotal - discount_amount) — manfi nemishavad
// hich round — float kham
```

### 2.4 `count(): int` — `CartService.php:356`

```php
public function count(): int { return (int) $this->content()->sum('qty'); }
// = Σ qty_i
```

### 2.5 `totalWeight(): float` + `roundedWeight(): float` — `CartService.php:361-370`

```php
public function totalWeight(): float {
    return $this->content()->sum(fn($item) => ($item['weight'] ?? 0) * $item['qty']);
}
// = Σ (weight_i * qty_i) — weight bar hasab gram (integer dar Product, float dar Variant)

public function roundedWeight(): float {
    $weight = $this->totalWeight();
    return ceil($weight / 100) * 100;
}
// = ceil(totalWeight / 100) * 100 — gird be 100g bala
// mesal: 230g → ceil(2.3)*100=300g ; 100g → 100g ; 0g → 0g
```

Rounded weight faghat baraye `ShippingCalculator` type `with_weight` estefade mishavad — dar `ShippingCalculator::getAvailableMethods` ham `ceil(weight/100)*100` dobare hesab mishavad (duplicate logic).

### 2.6 `add / update / remove / clear / mergeGuestCart`

| Method | Logic-e Daghigh |
|--------|-----------------|
| `add(product, qty, variantId, color, size)` | `qty=max(1,qty)` → `dbAdd` ya `sessionAdd` . `rowId=md5(product.id+variantId+color+size)` . Agar existing (hamin 4-tuple): `qty += qty` (carts table) ya `item.qty+=qty` (session). Vagar na `Cart::create` / `cart[rowId]=...` . `price=variant.final_price ?? product.final_price` , `stock`, `weight` zakhire. |
| `update(rowId, qty)` | `qty=max(1,qty)` — hich 0 ya manfi ghabel nist. `update(['qty'=>qty])` |
| `remove(rowId)` | `delete` / `forget` |
| `clear()` | `Cart::where(user_id)->delete()` / `session.forget(cart)` |
| `mergeGuestCart(guestItems)` | Bad az login: baraye har `guestItem` agar hamān 4-tuple dar DB hast → `newQty = min(existing.qty + guest.qty, stock)` (cap be stock!) — vagar na `dbAdd` . Bad `sessionClear()` |

**Race dar merge:** `stock` dar `mergeGuestCart:310-318` bedoun-e lock khande mishavad — 2 login hamzaman oversell.

## 3. OrderService::create — Az Cart be Order

```php
// OrderService.php:43-81
weightSum = Σ (item.weight * item.qty) // na rounded — kham

Order::create([
  user_id=>Auth::id(), state_id, city_id, address_id,
  address=>[state,city,address,receiptor_mobile,postal_code], // JSON
  receiptor_full_name, shipping_method_id, bank_id,
  order_status='paying',
  shipping_price => orderData.shipping_price ?? 0, // az ShippingCalculator
  total_price    => orderData.total_price ?? 0,    // = subtotal (ghabl az coupon)
  discount_price => orderData.discount_price ?? 0, // = discount_amount
  discount_id    => orderData.discount_id ?? null,
  payment_price  => orderData.payment_price ?? 0,  // = max(0, subtotal-discount) + shipping
  freight_balance=> orderData.freight_balance ?? 0, // 0 ya 1 (pas keraye)
  total_weight   => weightSum, // gram, na rounded
  user_description,
  shipping_status_id => OrderShippingStatus::default()->first()?->id,
  payment_receipt, receipt_tracking_code, receipt_payer_card, // card-to-card
])
foreach cartItems: OrderItem::create(order_id, product_id, variant_id, quantity=qty, weight, price, discounted_price=price)
// price dar OrderItem = item.price (final_price), na original_price
```

## 4. Shipping — `ShippingCalculator::calculate`

```php
// ShippingCalculator.php:14-45
public static function calculate(ShippingMethod $method, float $weightSum, float $finalPriceSum, ?Address $address): array{price, freight_balance} {
    price=''; freight_balance=0
    match method.type:
        'fixed_price' => price = method.price ?? 0
        'with_weight' => price = (weightSum/100) * (method.price ?? 0) // weightSum ghablan rounded (ceil/100*100)
        'chapar'      => price = chaparQuote(method, weightSum, finalPriceSum, address) // API, /10 toman
        default       => price = method.price ?? 0

    if method.freight_balance==1: price=0; freight_balance=1 // pas keraye → rayegan
    if method.price_ceiling>0 && finalPriceSum > price_ceiling: price=0 // sakf → rayegan
    return [price, freight_balance]
}
```

Tartib: `type` → `freight_balance` (hamishe rayegan) → `price_ceiling` (sharti). `getAvailableMethods` baraye `with_weight` `ceil(weight/100)*100` dobare mikonad.

`chaparQuote`: `weight/1000` kg + `value."0"` rial → `fld_Total_Cost/10` toman — try/catch → 0 agar fail.

## 5. Tartib-e Mohasebe — Order of Operations-e Kamel

```
1. subtotal        = Σ (final_price_i * qty_i)                          // CartService::subtotal()
2. discount_amount = CouponService::calculateForCart(cart)               // 0 agar coupon nist, cap ≤ subtotal
3. total          = max(0, subtotal - discount_amount)                   // CartService::total()
4. weightSum      = Σ (weight_i * qty_i)                                 // CartService::totalWeight()
   roundedWeight  = ceil(weightSum/100)*100                               // baraye with_weight
5. shipping_price = ShippingCalculator::calculate(method, roundedWeight, subtotal, address).price
                     // with_weight: (roundedWeight/100)*method.price
                     // fixed_price: method.price
                     // chapar: API quote
                     // + freight_balance==1 → 0
                     // + subtotal > price_ceiling → 0
6. payment_price  = total + shipping_price                               // hich tax nist
   // zakhire dar orders:
   // total_price    = subtotal
   // discount_price = discount_amount
   // shipping_price = shipping_price
   // payment_price  = payment_price
   // total_weight   = weightSum (kham)
```

**Formula-e Riyazi:**

```
subtotal       = Σ_i (P_i × Q_i)                          // P_i = final_price
discount       = Σ_{i∈eligible} calc(P_i×Q_i)  ≤ subtotal  // calc = P×rate/100 ya min(cash,P×Q)
total          = max(0, subtotal − discount)
shipping       = f(method, ceil(Σ W_i×Q_i /100)×100, subtotal) // W_i = weight gram
payment_price  = total + shipping
```

## 6. Gerd Kardan (Rounding)

| Jay | Method | Daghigh |
|-----|--------|---------|
| `roundedWeight` | `ceil(weight/100)*100` | Bala be 100g — 230→300 |
| `with_weight` shipping | `(roundedWeight/100)*price` | Bedoun round — float |
| `subtotal/total/discount` | **hich** round/ceil/floor | Float kham — `number_format` faghat dar namayesh |
| `payment_price` zakhire | `integer` dar DB? | `orders.payment_price` integer (na cast) — vali hesab float |
| `discount_percentage` | `round((price-final)/price*100)` | `Product.php:214` — round-e 0 ashar |
| `ZarinPal amount` | `intval(amount."0")` | Toman→Rial ×10 — dar `PaymentGateway` |
| Namayesh | `number_format(price)` | `1,234,567` — faghat view |

Hich `ceil` baraye price, hich `floor`, hich `int cast` joz ZarinPal — price ha float zakhire vali dar DB integer.

## 7. Tax — Vojoud Nadarad

`orders.tax_price`, `current_tax` column vojoud darad vali hamishe 0 — hich `TaxService`, hich `tax_rate`, hich hesab-e maliat dar `OrderService` ya `CartService` nist. `payment_price = total + shipping` (tax=0).

## 8. Pseudo-Code — Kamel

```php
function cartSubtotal(cart): float { return sum(item.price * item.qty for item in cart.content()) }
function cartDiscount(cart): float {
    coupon = session.coupon; if !coupon: return 0
    return CouponService.calculateForCart(cart) // per-item eligibility, min(totalDiscount, subtotal)
}
function cartTotal(cart): float { return max(0, cartSubtotal(cart) - cartDiscount(cart)) }
function totalWeight(cart): float { return sum((item.weight ?? 0) * item.qty for item in cart.content()) }
function roundedWeight(cart): float { return ceil(totalWeight(cart)/100)*100 }

function checkoutSummary(cart, shippingMethod, address):
    subtotal = cartSubtotal(cart)
    discount = cartDiscount(cart)
    total = max(0, subtotal - discount)
    weightSum = totalWeight(cart)
    rounded = ceil(weightSum/100)*100
    shipping = ShippingCalculator.calculate(shippingMethod, rounded, subtotal, address)
    // shipping.price: fixed/with_weight/chapar → freight_balance → price_ceiling
    payment_price = total + (float)shipping.price
    return [subtotal, discount, total, weightSum, rounded, shipping.price, shipping.freight_balance, payment_price]

// Order create mapping
orderData = [total_price=>subtotal, discount_price=>discount, discount_id=>coupon?.discount_id, shipping_price, payment_price, total_weight=>weightSum]
Order::create(orderData + [order_status=>'paying', ...])
foreach item in cart.content(): OrderItem::create(product_id, variant_id, quantity=qty, weight=item.weight, price=item.price, discounted_price=item.price)
```

## 9. Invariants baraye Vibe Coding

- **Price = final_price** — hargiz `price` kham ra dar cart zakhire nakon. `original_price` ra negah dar ta coupon `isDiscountedProduct` tashkhis dahad.
- **Rounded weight faghat baraye shipping** — `total_weight` dar `orders` kham zakhire mishavad. Hargiz `roundedWeight` ra be `orders.total_weight` nanevis.
- **Hich round baraye price** — `subtotal/total/payment_price` float kham. `number_format` faghat baraye namayesh.
- **`mergeGuestCart` cap `min(qty, stock)`** — vali bedoun lock. Agar race mohem ast `lockForUpdate` ezafe kon.
- **Tax = 0** — agar maliat ezafe mikoni `payment_price = total + shipping + tax` va `tax_price` ra por kon.
