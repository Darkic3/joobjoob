# Cart — Dependencies (Enriched Phase 2.1)

## Graph (actual)
```
CartController (index/add/update/remove/applyCoupon)
  ├─► CartService (sessionKey cart, isAuth)
  │     ├─► Cart Model (user_id, product_id, variant_id, price/original_price, qty, stock, weight, color,size,image)
  │     ├─► Product Model (->ProductVariant via variants(), ->Specification via spec trans, ->ProductImage url)
  │     ├─► StockService::enrichCartItemsWithStockStatus (per item Product::withTrashed()->find)
  │     └─► CouponService (calculateForCart -> Discount::isEligibleCartItem, appliesToDiscountedItems)
  │           └─► Discount (discountable morphs), DiscountUsage
  ├─► Product Model (direct in add: Product::where(slug)->firstOrFail)
  ├─► AddressService / StateService / Bank::active (for cart.index view when auth)
  └─► CouponService (applyCoupon)

OtpController::verify (P1-05 ✅)
  └─→ CartService::mergeGuestCart (session 'cart' captured pre-login → regenerate → merge → sessionClear)
        ├─→ Cart (existing-row match on product/variant/color/size, qty=min(existing+qty, stock))
        └─→ dbAdd for new rows + sessionClear

CheckoutController (shipments/setShipment/addressPrice/orderPrice/create)
  ├─► AddressService::find / findOne -> Address+City
  ├─► ShippingCalculator::getAvailableMethods / calculate (see shipping module)
  ├─► CartService (subtotal/discount/total/roundedWeight/toArray/clear)
  ├─► CouponService::apply/remove/recordUsage
  ├─► OrderService::create (cart.toArray + orderData) -> StockService::reserveStock
  └─► Session (checkout_address_id, checkout_shipping_method_id, checkout_shipping_price, coupon)
```

## Service → Model

| Service | Writes | Reads |
|---------|--------|-------|
| CartService dbAdd/dbUpdate/dbRemove | writes `carts` (create/update/delete where user_id) | reads `products, product_variants, specifications` (for price/stock/label), reads `Discount` via CouponService |
| CartService session* | writes `session cart` array | reads `products/variants` per item (N+1) |
| CartService::getSessionItems | — | DEAD CODE (zero callers) |
| CouponService | writes `session coupon` + `discount_usage` via recordUsage | reads `discounts, discountable, product_categories/brands/products` for eligibility |

## Controller → Service
- `CartController::__construct(CartService)` DI singleton (AppServiceProvider).
- `CartController` ham mostaghim `Product::where` + `Cart` via service, na repository.
- `CheckoutController` ham `CartService` injected + `app(CouponService)`, `app(OrderService)` transient.

## Tightly coupled
- **Cart ↔ Checkout session**: `cart` + `checkout_*` + `coupon` keys bayad hamزمان hast bashand — har taghir dar CartService bayad CheckoutController ra test konad.
- **Cart price snapshot**: snapshot dar add, na live — taghir catalog price rooye cart asar nadarad.
- **Stock**: enrich per item ba `withTrashed()->find` — N+1 vali ba lockForUpdate dar Order reserve jaebran mishe.

## Reusable / Shared
- `ShippingCalculator` stateless pure (calculate/getAvailableMethods).
- `StockService` shared ba orders/inventory.
- `CouponService` shared ba discounts/orders/cart.

## External / DB
- No external for Cart; DB `carts` + `products/variants/images` + `discounts`. Session `cart` for guest.

## Evidence
- `app/Services/CartService.php` (full audit — dbContent/refreshDbPrices/mergeGuestCart:348/getSessionItems:386 dead)
- `app/Http/Controllers/Site/CartController.php` (index/add/update/remove/applyCoupon)
- `app/Models/Cart.php` (fillable, NO CartItem model, no SoftDeletes)
- `app/Http/Controllers/Site/OtpController.php:135-147` (P1-05 merge caller)
- `database/migrations/2026_08_31_163905_create_carts_table.php` (user_id FK nullOnDelete + index, product_id FK cascade, no unique composite)
- `routes/site.php:81,84-90,91` ($siteRoutes cart.* + checkout.*)
