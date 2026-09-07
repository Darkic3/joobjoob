# Shipping — API Contract (Enriched Phase 2.1)

> **Source:** `routes/admin.php` + `routes/site.php` + `app/Http/Controllers/Admin/ShippingMethodController.php` + `app/Http/Controllers/Site/CheckoutController.php`

## Admin — Shipping Methods (`prefix admin, middleware auth+adminPermission+log.admin`)

| Method | URI | Name | Controller@Method | Input Validation |
|--------|-----|------|-------------------|------------------|
| GET | `/admin/shipping-methods` | `admin.shipping-methods.index` | `ShippingMethodController@index` | — |
| GET | `/admin/shipping-methods/create` | `admin.shipping-methods.create` | `ShippingMethodController@create` | — |
| POST | `/admin/shipping-methods` | `admin.shipping-methods.store` | `ShippingMethodController@store` | `title required|string|max255, type required in fixed_price/with_weight/chapar/cod24, price nullable numeric min0, price_ceiling nullable numeric, estimated_days_min/max required integer 1..30 (swapped min max), status required boolean, description nullable, freight_balance nullable boolean, chapar_type nullable string, sender_name/company/city_id/phone/mobile/email/address/postal_code nullable, cities nullable array` |
| GET | `/admin/shipping-methods/{shippingMethod}/edit` | `admin.shipping-methods.edit` | `ShippingMethodController@edit` | `cities active select` |
| PUT | `/admin/shipping-methods/{shippingMethod}` | `admin.shipping-methods.update` | `ShippingMethodController@update` | same as store |
| DELETE | `/admin/shipping-methods/{shippingMethod}` | `admin.shipping-methods.destroy` | `ShippingMethodController@destroy` | softDelete |
| POST | `/admin/shipping-methods/{shippingMethod}/toggle-status` | `admin.shipping-methods.toggle-status` | `ShippingMethodController@toggleStatus` | `status 0<->1` |

## Site — Checkout Shipping (prefix checkout, middleware auth except cities? actually cities/shipments require auth group)

| Method | URI | Name | Controller@Method | Auth | Input | Response |
|--------|-----|------|-------------------|------|-------|----------|
| GET | `/checkout/cities?state_id=` | `checkout.cities` | `CheckoutController@cities` | auth | `state_id` query | JSON `{cities: [{id,name}]} via CityService` |
| POST | `/checkout/shipments` | `checkout.shipments` | `CheckoutController@shipments` | auth | `address_id` (AddressService::findOne) | JSON `{shipping_methods: [{id,title,type,freight_balance,description,price (string or dynamic), original_price, price_ceiling, estimated_days_min/max, show_estimated_days}]}` + `session checkout_address_id` |
| POST | `/checkout/set-shipment` | `checkout.set-shipment` | `CheckoutController@setShipment` | auth | `shipping_method_id, shipping_price (0 or int or dynamic? actually string 0 or int)` | JSON `{success:true}` + `session checkout_shipping_method_id + checkout_shipping_price` |
| GET | `/checkout/address-price` | `checkout.address-price` | `CheckoutController@addressPrice` | auth | session keys | JSON `{final_price_sum=subtotal, price_discount, price_shipping=session, price_cart=total+shipping}` |
| GET | `/checkout/order-price` | `checkout.order-price` | `CheckoutController@orderPrice` | auth | same | JSON same as address-price |
| POST | `/checkout/create` | `checkout.create` | `CheckoutController@create` | auth | session `checkout_address_id/shipping_method_id/shipping_price` + `bank_id, user_description?, receipt_* if card_to_card` | Redirect via `OrderService::checkout` |

## Site — locale duplicate
- Har radif bala yek copy ba `prefix /{locale}` + `name locale.*` darad — SetLocale constraint.

## Response / Redirect
- Admin store/update -> `redirect route admin.shipping-methods.index with success 'روش ارسال ...'`
- Site JSON only — no view.
