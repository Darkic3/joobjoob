# Shipping — Dependencies (Enriched Phase 2.1)

## Graph
```
Admin/ShippingMethodController (index/create/store/edit/update/destroy/toggleStatus)
  ├─► ShippingMethod Model (title, price string, price_ceiling, freight_balance, type, chapar_type, sender_city_id, softDeletes)
  │    ├─► City Model (via cities belongsToMany pivot shipping_method_city)
  │     └─► City senderCity (hasOne)
  └─► City Model (active select for form)

ShippingCalculator (pure stateless)
  ├─► ShippingMethod (price, price_ceiling, freight_balance, type, chapar_type, senderCity)
  ├─► Address (city -> chapar_id for Chapar destination)
  │     └─► City (chapar_id, availableShippingMethods pivot)
  └─► ChaparShipment::quote (BROKEN authToken='' -> always 0)
        └─► curl POST http://api.krch.ir/v1/get_quote APP-AUTH: '' + value .'0' rial

Site/CheckoutController (shipments/setShipment/cities/addressPrice/orderPrice/create)
  ├─► AddressService::findOne -> Address
  ├─► CityService::findActiveByState
  ├─► ShippingCalculator::getAvailableMethods (reads city->availableShippingMethods)
  ├─► CartService (roundedWeight, total)
  ├─► Session (checkout_address_id, checkout_shipping_method_id, checkout_shipping_price)
  └─► OrderService::create (orderData shipping_* fields)
```

## Service → Model

| Service/Controller | Writes | Reads |
|--------------------|--------|-------|
| ShippingMethodController store/update | writes `shipping_methods` + `shipping_method_city` sync | reads `cities active` |
| ShippingCalculator calculate/getAvailableMethods | no write | reads `shipping_methods, cities pivot, addresses with state/city` |
| ChaparShipment | no write (external) | reads `shippingMethod.senderCity.chapar_id, address.city.chapar_id, weight, finalPrice+'0'` |
| CheckoutController shipments | writes `session checkout_address_id` | reads `addresses, shipping_methods via city pivot` |

## Tightly coupled
- **Address → City → ShippingMethod pivot**: getAvailableMethods faghat az `city->availableShippingMethods` mikhune — agar pivot khali shipping 0.
- **Weight rounding**: with_weight ceil/100*100 — dar 2 ja (ShippingCalculator + Cart).
- **Chapar BROKEN** — har calculate chapar ya original_price calculation be catch mirese -> 0/dynamic — agent bayad fallback بدانه.

## Reusable
- ShippingCalculator stateless — testable bedoone DB (mock method/address).
- CityService reusable.

## External
- Chapar API `http://api.krch.ir/v1/` + `APP-AUTH ''` — BROKEN, documented.
- No queue/cache.

## Evidence
- `app/Services/ShippingCalculator.php:15 calculate, 55 chaparQuote, 78 getAvailableMethods city->availableShippingMethods`
- `app/Models/ShippingMethod.php:20 cities pivot, 26 senderCity`
- `app/Services/ChaparShipment.php:7 BROKEN`
- `app/Http/Controllers/Admin/ShippingMethodController.php:21 store validate cod24 allowed + min/max swap`
