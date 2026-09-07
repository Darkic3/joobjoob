# Orders — API Contract (Enriched Phase 2.1)

> **Source:** `routes/admin.php` orders group + `routes/site.php` checkout group + `app/Http/Controllers/Admin/OrderController.php` + `app/Http/Controllers/Site/CheckoutController.php`

> **Locale dup:** site checkout routes duplicate `locale.` prefix; table canonical.

## Admin — Orders (prefix admin, middleware auth+adminPermission+log.admin, permission orders.*)

| Method | URI | Name | Controller@Method | Query / Body | Response |
|--------|-----|------|-------------------|--------------|----------|
| GET | `/admin/orders` | `admin.orders.index` | `OrderController@index` | query `id?, order_status? (paying/paid/deposit_paid/wait_for_verification/pending_confirmation/unpaid/cancelled), shipping_status_id?, shipping_method_id?, mobile? (whereHas user mobile LIKE), from_date? jalali yyyy/mm/dd -> jalali_to_gregorian, to_date? same` | view `admin.orders.index` with `orders paginate 20 with user/shippingStatus/shippingMethod/bank + shippingStatuses + shippingMethods + newOrdersCount` |
| GET | `/admin/orders/{id}` | `admin.orders.show` | `OrderController@show` | path id | view `admin.orders.show` with `order items.product/items.productVariant/shippingStatus/shippingMethod/user/bank/histories.shippingStatus` |
| POST | `/admin/orders/{id}/change-shipping-status` | `admin.orders.change-shipping-status` | `OrderController@changeShippingStatus` | body `shipping_status_id` | `redirect back with success بروزرسانی شد` + notifyUser `order.shipped` |
| POST | `/admin/orders/change-order-status/{id}` | `admin.orders.change-order-status` | `OrderController@changeOrderStatus` | body `order_status` (if cancelled -> cancelOrder with restore else update) | redirect back success |
| POST | `/admin/orders/post-code` | `admin.orders.post-code` | `OrderController@postCode` | body `order_id, post_code` | redirect back success |
| GET | `/admin/orders/{id}/factor` | `admin.orders.factor` | `OrderController@factor` | path id | view `admin.orders.factor` |
| GET | `/admin/orders/bulk-factor?ids=1,2,3` | `admin.orders.bulk-factor` | `OrderController@bulkFactor` | query `ids comma` | view `admin.orders.bulk-factor` with `whereIn ids` |

## Site — Checkout / Order Create (prefix checkout, middleware auth except callbacks, web)

| Method | URI | Name | Controller@Method | Auth | Input | Response |
|--------|-----|------|-------------------|------|-------|----------|
| GET | `/checkout` | `checkout.index` | `CheckoutController@index` | auth | — | `redirect route cart.index` |
| GET | `/checkout/address` | `checkout.address` | `CheckoutController@address` | auth | — | `redirect cart.index step2` |
| GET | `/checkout/cities?state_id=` | `checkout.cities` | `CheckoutController@cities` | auth | `state_id` | JSON `{cities}` |
| POST | `/checkout/shipments` | `checkout.shipments` | `CheckoutController@shipments` | auth | `address_id` | JSON `{shipping_methods}` + session `checkout_address_id` |
| POST | `/checkout/set-shipment` | `checkout.set-shipment` | `CheckoutController@setShipment` | auth | `shipping_method_id, shipping_price` | JSON `{success}` + session `checkout_shipping_method_id/_price` |
| GET | `/checkout/address-price` | `checkout.address-price` | `CheckoutController@addressPrice` | auth | — | JSON `{final_price_sum=subtotal, price_discount, price_shipping, price_cart=total+shipping}` |
| GET | `/checkout/payment` | `checkout.payment` | `CheckoutController@payment` | auth | — | `redirect cart.index step3` |
| POST | `/checkout/apply-discount` | `checkout.apply-discount` | `CheckoutController@applyDiscount` | auth | `discount_code` | JSON `{success,message,discount,discount_amount}` |
| POST | `/checkout/remove-discount` | `checkout.remove-discount` | `CheckoutController@removeDiscount` | auth | — | JSON `{success}` |
| POST | `/checkout/validate-stock` | `checkout.validate-stock` | `CheckoutController@validateStock` (via OrderService tail) | auth | cart toArray | JSON `{unavailable, insufficient}` |
| GET | `/checkout/order-price` | `checkout.order-price` | `CheckoutController@orderPrice` | auth | — | JSON same as address-price |
| POST | `/checkout/create` | `checkout.create` | `CheckoutController@create` | auth | `CardToCardCheckoutRequest` body `bank_id required exists banks, user_description nullable string, receipt_tracking_code nullable required_if card_to_card, receipt_payer_card nullable string` + session `checkout_address_id/_shipping_method_id/_price` required else `back error اطلاعات تکمیل نیست` | Redirect `checkout.success` or `checkout.failed` via `OrderService::checkout` + `cart clear + session forget coupon/checkout_*` |
| GET | `/checkout/success/{order}` | `checkout.success` | `CheckoutController@success` | auth + ownership `order->user_id === auth id` else `/` | path `order` model binding | View `checkout.success` (paid) or redirect `/` for pending_confirmation/unpaid |
| GET | `/checkout/failed` | `checkout.failed` | `CheckoutController@failed` | auth | — | view `checkout.failed` |
| ANY | `/checkout/finish` | `checkout.finish.zarin-pal` | `CheckoutController@finishZarinPal` | web (no auth) | query/body `Authority, Status` | Redirect `checkout.success/{order}` if verify code 100/101 -> order_status paid else unpaid -> `checkout.failed` |
| ANY | `/checkout/finish-saman` | `checkout.finish.saman-bank` | `CheckoutController@finishSaman` | web | — | legacy same as finish (BROKEN legacy) |
| ANY | `/checkout/finish-sadad` | `checkout.finish.sadad-bank` | `CheckoutController@finishSadad` | web | — | legacy |
| ANY | `/checkout/finish-zibal` | `checkout.finish.zibal-bank` | `CheckoutController@finishZibal` | web | — | legacy |

## Validation (Checkout create)
- `CardToCardCheckoutRequest` rules (check file `app/Http/Requests/Site/CardToCardCheckoutRequest.php`) `bank_id required exists:banks,id, receipt_tracking_code nullable string required_if bank_type card_to_card, receipt_payer_card nullable string`.
- `shipment` session guard, not FormRequest.

## Responses / Redirects
- `OrderService::checkout` returns `RedirectResponse` via `BankPaymentService->handlePayment` (ZarinPal -> `header Location https://payment.zarinpal.com/pg/StartPay/Authority` + exit) wrapped `response(content)`.
- `finishZarinPal` restores auth via `Auth::loginUsingId(order->user_id)` before update.

## Evidence
- `routes/admin.php: orders 7 routes index/show/change-shipping-status/change-order-status/post-code/factor/bulk-factor`
- `routes/site.php: checkout group 14 routes + 4 callbacks any`
- `app/Http/Controllers/Admin/OrderController.php:18 index filters jalali, 72 changeShippingStatus, 110 changeOrderStatus`
- `app/Http/Controllers/Site/CheckoutController.php:124 create guard, 176 orderData 13 fields, 230 success ownership, 260 finishZarinPal Authority lookup`
