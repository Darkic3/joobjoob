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
- `app/Services/CartService.php:12,16,26,80,180,260`
- `app/Http/Controllers/Site/CartController.php:14,46,112,147,167`
- `app/Models/Cart.php:8`
- `routes/site.php: $siteRoutes cart.* + checkout.*`
