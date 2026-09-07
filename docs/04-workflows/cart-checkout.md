# Cart -> Checkout Workflow

> **Source:** `app/Services/CartService.php`, `app/Http/Controllers/Site/CartController.php`, `CheckoutController.php`, `ShippingCalculator.php`, `CouponService.php`

## Cart
- Session key `cart` for guest (md5(product_id+variant_id+color+size) as rowId)
- DB table `carts` for auth (user_id index)
- `content()` branches isAuth() ? dbContent : sessionContent
- Stock enrichment: `StockService::enrichCartItemsWithStockStatus` adds is_in_stock/current_stock (N+1 risk)
- Sync: `mergeGuestCart(guestItems)` exists but **no caller found** — UNKNOWN.

## Checkout 4 steps in one view `cart.index` with ?step
```
GET /cart -> CartController@index:
  if count==0 -> empty view
  if guest -> only step1 items
  if auth -> load addresses (AddressService::find), states (StateService::findActive), banks (Bank::active), selectedAddress/Sipping from session

AJAX POST /checkout/cities {state_id} -> CityService::findActiveByState
AJAX POST /checkout/shipments {address_id} -> AddressService::findOne -> session[checkout_address_id]=id -> ShippingCalculator::getAvailableMethods(address, roundedWeight=ceil(totalWeight/100)*100, subtotal)
AJAX POST /checkout/set-shipment {shipping_method_id, shipping_price} -> session[checkout_shipping_method_id, checkout_shipping_price]
AJAX POST /checkout/apply-discount {discount_code} -> CouponService::apply(code, subtotal) -> session[coupon={code,discount_id,type,value,discount_amount,title,with_discount,pay_type}]
POST /checkout/create (CardToCardCheckoutRequest) -> CheckoutController@create:
  bank = Bank::find(bankId), isCardToCard = bank_type==='card_to_card'
  require checkout_address_id && checkout_shipping_method_id else error
  orderData = address fields + shipping + total_price=subtotal discount_price=discount payment_price=total+shipping discount_id=session coupon.discount_id
  OrderService::create(cartArray, orderData) -> Order status paying -> reserveStock -> notifications
  OrderService::checkout(order) -> if card_to_card -> pending_confirmation else BankPaymentService::handlePayment
  clear cart + forget coupon + forget checkout_*
```

## Session keys
cart, checkout_address_id, checkout_shipping_method_id, checkout_shipping_price, coupon

## AI gotcha
- Always check session keys before create.
- Amount toman, shipping added after discount.
