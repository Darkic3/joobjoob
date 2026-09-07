# Discount & Coupon Engine

> **Source of Truth:** `app/Services/CouponService.php:1-145` , `app/Services/WelcomeDiscountService.php:1-67` , `app/Models/Discount.php:1-130` , `app/Models/DiscountUsage.php` , `app/Services/OrderService.php:70-73` , `app/Services/CartService.php:338-378` , `app/Http/Controllers/Site/CheckoutController.php` (coupon apply/remove endpoints), `app/Http/Controllers/Admin/DiscountController.php`

## 1. Model — `Discount` Fields-e Mohem

| Field | Type | Mani | Mesal |
|-------|------|------|-------|
| `title` | string | Namayesh be user | `تخفیف خوشامدگویی` |
| `code` | string unique | Uppercase, `WELCOME-XXXXXX` | `WELCOME-AB12CD` |
| `type` | `percent` / `cash` | Noe takhfif | `percent` |
| `amount` | float | Darsad ya mablagh toman | `10` (10%) ya `50000` (50k toman) |
| `count` | int | Sakf-e kol estefade | `1` (welcome) ya `100` |
| `max_usage_per_user` | int | Sakf-e har karbar | `1` ya `0`=bi nahayat |
| `basket_minimum_price` | int | Hadeaghal sefارش | `0` ya `200000` |
| `first_purchase` | bool (0/1) | Faghat kharid-e aval? | `1` baraye welcome |
| `with_discount` | bool (0/1) | Rooye kala-ye takhfifdar ham? | `0` baraye welcome |
| `pay_type` | null/`category`/`brand`/`product` | Mahdoud be ... | `null` baraye welcome |
| `user_id` | nullable FK | Ekhtesasi be yek user | `42` baraye welcome |
| `is_active` | bool | Faal? | `true` |
| `starts_at` / `expires_at` | nullable datetime | Bazeye zamani | `expires_at = now+30d` baraye welcome |

Pivot `discount_users` (discount↔user), `discountable` polymorphic (discount↔category/brand/product), `discount_usages` (discount_id, user_id, order_id, discount_amount, used_at).

## 2. Order of Operations — Tartib-e Daghigh-e Mohasebe dar Checkout

```
Step 1  subtotal          = Σ (item.price * item.qty)          // item.price = final_price (ba takhfif-e product)
Step 2  discount_amount   = CouponService::calculateForCart(cart) // ya 0 agar coupon nist
Step 3  total             = max(0, subtotal - discount_amount)   // CartService::total()
Step 4  shipping_price    = ShippingCalculator::calculate(method, weightSum, subtotal, address).price
                            // weightSum = Σ weight*qty, roundedWeight = ceil(weightSum/100)*100 baraye with_weight
Step 5  payment_price     = total + shipping_price               // hiche tax/maliat nist
        deposit_price     = (agar naghdi+aghsati) ? deposit : null
```

**Checkout → OrderService::create mapping:**

```php
$orderData = [
  'total_price'    => subtotal,              // ghabl az coupon
  'discount_price' => discount_amount,       // az coupon (0 agar nadarad)
  'discount_id'    => coupon.discount_id ?? null,
  'shipping_price' => shipping_price,        // az ShippingCalculator
  'payment_price'  => payment_price,         // total + shipping
  'deposit_price'  => deposit_price ?? 0,
  'total_weight'   => weightSum,             // Σ weight*qty (na rounded)
]
Order::create(total_price, discount_price, discount_id, shipping_price, payment_price, ...)
```

> Tax: `orders.tax_price`, `current_tax` vojoud darad vali dar flow-e fe'li hamishe 0 — hich `TaxService` nist.

## 3. `CouponService::apply` — 8 Step Validation (be tartib)

```php
public function apply(string $code, float $subtotal): array{success, message, discount?, discount_amount?}
```

| Step | Shart | Payam-e Khata | Code |
|------|-------|---------------|------|
| 1 | `Discount::where(code=strtoupper(trim(code)))->first() ?: fail` | `کد تخفیف نامعتبر است.` | `CouponService.php:18` |
| 2 | `discount->isCurrentlyValid() ?: fail` → `is_active && now ∈ [starts_at, expires_at]` | `این کد تخفیف منقضی شده یا غیرفعال است.` | `:27` |
| 3 | `if basket_minimum_price>0 && subtotal < basket_minimum_price → fail` | `حداقل خرید برای این کد {min} تومان است.` | `:32` |
| 4 | `if first_purchase && user.orders(where order_status=paid).exists() → fail` | `این کد فقط برای خرید اول قابل استفاده است.` | `:40-44` — faghat `paid` check, `deposit_paid` hesab nemishavad (bug) |
| 5 | `if user_id && (!user || user.id !== discount.user_id) → fail` | `این کد تخفیف مخصوص شما نیست.` | `:49` |
| 6 | `if hasReachedLimit(user.id) → fail` → `count<=0 → true` ; `usageRecords.count >= count → true` ; `userUsage >= max_usage_per_user → true` | `ظرفیت استفاده از این کد تمام شده است.` | `:53` |
| 7 | `discountAmount = discount.calculateDiscount(subtotal)` → `if type=percent: subtotal*amount/100 else amount ; min(discount, subtotal)` ; `if discountAmount<=0 → fail` | `مبلغ تخفیف قابل محاسبه نیست.` | `:57-61` |
| 8 | `session(['coupon'=>[code, discount_id, type, value, discount_amount, title, with_discount, pay_type]])` | `کد تخفیف «{title}» با موفقیت اعمال شد. ({formatted} تومان)` | `:64-75` |

**Race gotcha Step 6:** `hasReachedLimit` = `usageRecords()->count()` **bedoun-e lock**. 2 user hamzaman check konand har do pass mishavand, bad har do `recordUsage` — sakf rad mishavad. Bayad `lockForUpdate` ya `SELECT ... FOR UPDATE` bar `discounts` row.

## 4. `calculateForCart` — Discount-e Hooshmand (pay_type + with_discount)

```php
public function calculateForCart(CartService $cart): float
```

```
coupon = session('coupon')
if !coupon: return 0
discount = Discount::find(coupon.discount_id)
withDiscount = discount.appliesToDiscountedItems() // (bool)with_discount

// Fast path: agar hich mahdoudiat nist → kol-e subtotal
if empty(pay_type) && withDiscount:
  return calculate(subtotal) // percent: subtotal*value/100, cash: min(value, subtotal)

if !discount: return calculate(subtotal) // fallback

totalDiscount = 0
foreach item in cart.content():
  itemTotal = item.price * item.qty
  originalPrice = item.original_price ?? item.price
  isDiscountedProduct = originalPrice > item.price && originalPrice >0
  if isDiscountedProduct && !withDiscount: continue // skip kala-ye takhfifdar

  if !discount.isEligibleCartItem(item): continue // pay_type check (category/brand/product)

  totalDiscount += discount.calculateDiscount(itemTotal) // per-item
return min(totalDiscount, cart.subtotal())
```

**`isEligibleCartItem` (Discount.php:170-192):**

```php
if !hasPayTypeRestriction(): return true
product = Product::with(category,brand)->find(productId) // N+1 — har item yek query!
return match pay_type:
  'category' => product.product_category_id && categories().where(product_categories.id, product.category_id).exists()
  'brand'    => product.brand_id && brands().where(brands.id, product.brand_id).exists()
  'product'  => products().where(products.id, productId).exists()
  default    => true
```

**`calculateDiscount` (Discount.php:197-206):**

```php
public function calculateDiscount(float $amount): float {
    if type==='percent': discount = amount * (amount_field/100)
    else: discount = (float)amount_field // cash
    return min(discount, amount) // cap: bishtar az mablagh nist
}
```

## 5. Stackable vs Non-Stackable

| So'al | Javab | Evidence |
|-------|-------|---------|
| Chand coupon hamzaman? | ❌ **Na** — `session('coupon')` yek key ast, `apply` overwrite mikonad | `CouponService.php:64 session(['coupon'=>...])` |
| Coupon + takhfif-e product? | Mashroot — `with_discount` tasmim migirad | `calculateForCart` skip logic |
| Coupon + shipping free? | Joda — `price_ceiling` va `freight_balance` dar `ShippingCalculator` | `ShippingCalculator.php:38-44` |

Pseudo:

```
coupon_stackable = false // hard-coded: session yek coupon
product_discount + coupon = with_discount ? eligible : skip discounted items
coupon + free_shipping = independent (shipping_price alahde az discount_price hesab mishavad)
```

## 6. Welcome Discount — `WelcomeDiscountService::createForUser`

**Trigger:** `OtpService::send` vaghti user jadid sakhte mishavad (mobile jadid).

```php
// WelcomeDiscountService.php:31-52
code = "WELCOME-" + strtoupper(Str::random(6)) // do-while unique check (race: 2 hamzaman duplicate momken)
Discount::create([
  title=>'تخفیف خوشامدگویی', code, type='percent', amount=10, count=1, max_usage_per_user=1,
  first_purchase=true, with_discount=false, is_active=true, basket_minimum_price=0,
  user_id=user.id, expires_at=now+30days,
])
discount.users().attach(user.id) // duplicate-e user_id (column + pivot)
```

| Field | Meghdar | Natije |
|-------|---------|--------|
| 10% | `amount=10, type=percent` | `0.10 * eligible_subtotal` |
| 1 bar | `count=1, max_usage=1` | bad az 1 `recordUsage` limit |
| Faghat aval | `first_purchase=true` | dar `apply` check |
| Ekhtesasi | `user_id=user.id` | dar `apply` Step 5 |
| 30 rooz | `expires_at=now+30d` | dar `isCurrentlyValid` |
| Na rooye takhfifdar | `with_discount=false` | skip `isDiscountedProduct` |

**Race:** `generateUniqueCode` loop `while exists()` bedoun-e transaction — 2 request hamzaman mitavanad haman code ra generate konad. Behtar: `code` unique index + retry on `IntegrityConstraintViolation`.

## 7. `recordUsage` + `DiscountController` — Vaghti Estefade Sabt Mishavad

```php
// CouponService.php:130
public function recordUsage(int $discountId, ?int $userId, ?int $orderId, float $discountAmount): void {
    DiscountUsage::create([discount_id, user_id, order_id, discount_amount, used_at=>now()]);
}
// Discount model: hasReachedLimit = usageRecords()->count() >= count
```

`recordUsage` dar `CheckoutController::create` (checkout form submit) bad az `OrderService::create` seda zade mishavad — **ghabl** az payment verify. Agar payment fail shavad usage baqi mimanad (bug — bayad dar `handleSuccess` sabt mishod ya dar `handleFailed` hazf).

Hich `DB::transaction` baraye `apply` + `recordUsage` nist.

## 8. Pseudo-Code — Kamel

```php
// APPLY — 8 validation + session store
function apply(code, subtotal):
  code = strtoupper(trim(code))
  discount = Discount::where(code).first() ?: return {success:false, "کد تخفیف نامعتبر است."}
  if !discount.isCurrentlyValid(): return {success:false, "منقضی شده یا غیرفعال"}
  if basket_minimum_price>0 && subtotal < basket_minimum_price: return {success:false, "حداقل خرید {min}"}
  user = Auth::user()
  if first_purchase && user && user.orders(where order_status='paid').exists(): return {success:false, "فقط خرید اول"}
  if user_id && (!user || user.id !== discount.user_id): return {success:false, "مخصوص شما نیست"}
  if hasReachedLimit(user?.id): return {success:false, "ظرفیت تمام شده"}
  discountAmount = calculateDiscount(subtotal) // percent: subtotal*amount/100, cash: amount; min(discount, subtotal)
  if discountAmount<=0: return {success:false, "قابل محاسبه نیست"}
  session.coupon = {code, discount_id, type, value:amount, discount_amount, title, with_discount, pay_type}
  return {success:true, discount, discount_amount}

// CALCULATE FOR CART — per-item eligibility
function calculateForCart(cart):
  coupon = session.coupon; if !coupon: return 0
  discount = Discount::find(coupon.discount_id); withDiscount = discount?.appliesToDiscountedItems() ?? true
  if empty(pay_type) && withDiscount: return calculate(cart.subtotal())
  if !discount: return calculate(cart.subtotal())
  totalDiscount=0
  foreach item in cart.content():
    itemTotal = item.price * item.qty
    isDiscountedProduct = (item.original_price ?? item.price) > item.price
    if isDiscountedProduct && !withDiscount: continue
    if !discount.isEligibleCartItem(item): continue // N+1 per item!
    totalDiscount += discount.calculateDiscount(itemTotal)
  return min(totalDiscount, cart.subtotal())

// isCurrentlyValid
function isCurrentlyValid(): bool { return is_active && (!starts_at || starts_at<=now) && (!expires_at || expires_at>=now) }

// hasReachedLimit
function hasReachedLimit(userId): bool {
  if count<=0: return true
  if usageRecords.count() >= count: return true
  if userId && max_usage_per_user>0 && usageRecords.where(user_id=userId).count() >= max_usage_per_user: return true
  return false
}

// calculateDiscount
function calculateDiscount(amount): float {
  discount = type==='percent' ? amount * (amount_field/100) : (float)amount_field
  return min(discount, amount)
}

// Welcome
function createForUser(user): Discount {
  do { code = "WELCOME-"+upper(random(6)) } while Discount::where(code).exists()
  discount = Discount::create(title, code, type=percent, amount=10, count=1, max_usage=1, first_purchase=true, with_discount=false, user_id, expires_at=now+30d)
  discount.users().attach(user.id)
  return discount
}
```

## 9. Mathematical Formula

```
subtotal         = Σ_i (final_price_i * qty_i)
discount_amount  = if pay_type==null && with_discount: calc(subtotal)
                   else: Σ_{i ∈ eligible} calc(final_price_i * qty_i)
                   where calc(x) = x*rate/100 (percent) ya min(cash, x)
                   cap: discount_amount ≤ subtotal
payment_price    = max(0, subtotal - discount_amount) + shipping_price
```

## 10. Invariants baraye Vibe Coding

- **Yek coupon** — `session('coupon')` single. Agar stackable mikhahi `session('coupons')` array kon va `calculateForCart` loop kon.
- **Race dar `hasReachedLimit`** — hich lock nist. Baraye `count=1` mitavanad oversell shavad. Fix: `Discount::where(id)->lockForUpdate()` ghabl az check.
- **`first_purchase` faghat `paid` ra check mikonad** — `deposit_paid` hesab nist. Agar deposit mohem ast `whereIn([paid,deposit_paid])` kon.
- **`recordUsage` zood sabt mishavad** (ghabl az verify) — agar payment fail shavad usage harz miravad. Behtar dar `handleSuccess` sabt shavad.
- **N+1 dar `calculateForCart`** — har item `Product::with(category,brand)->find()` . Baraye 20 item = 20 query. Eager load kon: `Product::with(...)->whereIn(ids)->get()->keyBy(id)`.
