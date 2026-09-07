# Cart — API Contract (Enriched Phase 2.1)

> **Source:** `routes/site.php:81,84-90,91` ($siteRoutes closure, duplicated under `{locale}`) + `app/Http/Controllers/Site/CartController.php` (221 lines)

> **Note Routes Duplicate:** hameye site routes 2 bar sabt mishavand: `name home, cart.*, checkout.*` (canonical) + `name locale.home, locale.cart.*, locale.checkout.*` ba `prefix {locale} where localePattern`. Table zir version canonical ra neshun mide; locale version prefix `/en` + name `locale.` darad.

| Method | URI | Name | Middleware | Controller@Method | Auth | Input | Response |
|--------|-----|------|------------|-------------------|------|-------|----------|
| POST | `/cart/add/{product}` | `cart.add` | `web` | `CartController@add` | guest+auth | `product` path (slug or id), body `qty? integer min:1`, `variant_id?` exists:product_variants, `color?`, `size?` | Partial add up to stock; JSON `{success, message added_to_cart or stock_limit_reached, cart_count, stock_warning}` — **HTTP 422** `{success:false, stock_limit:true}` when nothing addable; else `back with flash success/error` |
| GET | `/cart` | `cart.index` | `web` | `CartController@index` | guest+auth | — | `view site.cart.index` with `items (Collection), cart (CartService), addresses, states, banks, isGuest` |
| PATCH | `/cart/update/{rowId}` | `cart.update` | `web` | `CartController@update` | guest+auth | `rowId` path (md5 or DB id — treat opaque), body `qty required integer min:1` | requested > live stock does NOT clamp to stock — **current qty is KEPT** (no-clamp comment in code) + `stock_warning` in JSON; `{success, item, subtotal, discount, total, cart_count, stock_warning}` if wantsJson else back. ⚠️ invalid rowId → silent null-guard gap (see gotchas) |
| DELETE | `/cart/remove/{rowId}` | `cart.remove` | `web` | `CartController@remove` | guest+auth | `rowId` | JSON `{success, subtotal, discount, total, cart_count, is_empty}` if wantsJson else back |
| POST | `/cart/coupon` | `cart.coupon` | `web,auth` | `CartController@applyCoupon` | auth only | `code required|string` | `back with success coupon_applied or error coupon_invalid` (CartService::applyCoupon->CouponService) |
| GET | `/checkout` | `checkout.index` | `web,auth` | `CheckoutController@index` | auth | — | `redirect route cart.index` (index delegates) |
| GET | `/checkout/address` | `checkout.address` | `web,auth` | `CheckoutController@address` | auth | — | `redirect cart.index with step 2` |
| GET | `/checkout/cities?state_id=` | `checkout.cities` | `web,auth` | `CheckoutController@cities` | auth | `query state_id` | JSON `{cities: City[]}` via `CityService::findActiveByState` |
| POST | `/checkout/shipments` | `checkout.shipments` | `web,auth` | `CheckoutController@shipments` | auth | `address_id` | JSON `{shipping_methods: array{id,title,type,freight_balance,price/original_price/price_ceiling/estimated_days}}` + `session[checkout_address_id]` |
| POST | `/checkout/set-shipment` | `checkout.set-shipment` | `web,auth` | `CheckoutController@setShipment` | auth | `shipping_method_id, shipping_price` | JSON `{success:true}` + `session[checkout_shipping_method_id, checkout_shipping_price]` |
| GET | `/checkout/address-price` | `checkout.address-price` | `web,auth` | `CheckoutController@addressPrice` | auth | — | JSON `{final_price_sum=subtotal, price_discount, price_shipping=session, price_cart=total+shipping}` |
| GET | `/checkout/payment` | `checkout.payment` | `web,auth` | `CheckoutController@payment` | auth | — | `redirect cart.index step 3` |
| POST | `/checkout/apply-discount` | `checkout.apply-discount` | `web,auth` | `CheckoutController@applyDiscount` | auth | `discount_code` | JSON `{success,message,discount_amount}` from `CouponService::apply(code, cart->subtotal())` |
| POST | `/checkout/remove-discount` | `checkout.remove-discount` | `web,auth` | `CheckoutController@removeDiscount` | auth | — | JSON `{success:true}` |
| POST | `/checkout/validate-stock` | `checkout.validate-stock` | `web,auth` | `CheckoutController@validateStock` | auth | — | JSON `{unavailable:[], insufficient:[]}` from `StockService::validateCartItems(toArray)` |
| GET | `/checkout/order-price` | `checkout.order-price` | `web,auth` | `CheckoutController@orderPrice` | auth | — | JSON `{final_price_sum, price_discount, price_shipping, price_cart}` |
| POST | `/checkout/create` | `checkout.create` | `web,auth` | `CheckoutController@create` | auth | `CardToCardCheckoutRequest` body `bank_id required, user_description?, receipt_tracking_code? (if card_to_card), receipt_payer_card?` | Redirect `checkout.success` or `checkout.finish.*` via `OrderService::checkout` |
| GET | `/checkout/success/{order}` | `checkout.success` | `web,auth` | `CheckoutController@success` | auth (ownership check) | `order` model binding | View `checkout.success` or redirect `/` if `order->user_id !== auth` |
| GET | `/checkout/failed` | `checkout.failed` | `web,auth` | `CheckoutController@failed` | auth | — | View failed |
| ANY | `/checkout/finish` | `checkout.finish.zarin-pal` | `web` (no auth) | `CheckoutController@finishZarinPal` | guest callback | `Authority, Status` | Redirect success/failed, verify via ZarinPal |
| ANY | `/checkout/finish-saman` | `checkout.finish.saman-bank` | `web` | `CheckoutController@finishSaman` | guest | — | legacy, same verify |
| ANY | `/checkout/finish-sadad` | `checkout.finish.sadad-bank` | `web` | `CheckoutController@finishSadad` | guest | — | legacy |
| ANY | `/checkout/finish-zibal` | `checkout.finish.zibal-bank` | `web` | `CheckoutController@finishZibal` | guest | — | legacy |

**Validation details:**
- `add`: no FormRequest, inline `Product::where` + qty min1, variant existence if provided.
- `update`: `qty required|integer|min:1`; live stock via `StockService::getAvailableStock` (add() reads `$variant->stock/$product->stock` directly — same numbers today since add() 404s trashed products via firstOrFail). Over-stock request KEEPS current qty + warning (intentional no-clamp).
- `applyCoupon`: `code required|string` -> CouponService 8 checks (valid, basket_min, first_purchase, user, limit, discount>0).
- `checkout/create`: `CardToCardCheckoutRequest` (see `app/Http/Requests/Site/CardToCardCheckoutRequest.php`) — `bank_id exists:banks,id, receipt_tracking_code/receipt_payer_card nullable string` + session guards `checkout_address_id/shipping_method_id` required else `back error اطلاعات تکمیل نیست`.

**Duplicate locale routes:** har radif bala yek copy ba `prefix /{locale}` va `name locale.*` darad — behavior yeki, `SetLocale` canonical redirect.
