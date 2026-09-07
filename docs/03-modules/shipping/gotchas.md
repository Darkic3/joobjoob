# Shipping — Gotchas & Known Bugs (Enriched Phase 2.1)

## Critical Gotchas for AI
- **Chapar BROKEN**: `ChaparShipment::$authToken=''` hardcode, `baseUrl http://api.krch.ir/v1/` http not https, header `APP-AUTH: ''` — har quote dar try catch be `return 0` ya exception log -> caller `calculate` 0 barmigardune, `getAvailableMethods` price `dynamic`. Agent nakhad chaphar ro ba amount koli hesab kone — feli broken. Evidence `ChaparShipment.php:7-8, 24`.
- **Toman→Rial `'0'` concat**: `ShippingCalculator::chaparQuote value => finalPriceSum.'0'` (rial) + ZarinPal ham `amount.'0'` — dobar tabdil, bad `totalCost/10` baraye bargasht be toman. Eshtabah => 10x shipping price bug. Evidence `ShippingCalculator.php:59` + `ZarinPal.php:20`.
- **price string cast**: `ShippingMethod casts price=>string` — `calculate` string return, `calculateRaw floatval` — comparison `price==0` loose — behtar `floatval`.
- **freight_balance override**: `freight_balance==1` har price ro 0 mikone (پس کرایه) — ghabal az price_ceiling check. Evidence `ShippingCalculator.php:28-31`.
- **price_ceiling**: agar `price_ceiling>0 && finalPriceSum > ceiling` => price 0 (رایگان) — vali `original_price` baraye namayesh `% تخفیف ارسال` ba `calculateRaw` dobare mohasebe mishe. Evidence `getAvailableMethods 92-98`.
- **Weight units**: `weightSum` gram hast (Product weight integer gram), `chaparQuote weight/1000` kg, `with_weight (weight/100)*price` per 100gr, `getAvailableMethods` with_weight ceil/100*100 — 3 tabdil motevafaut. Evidence `ShippingCalculator.php:18,59`.
- **cod24 legacy**: `validate in: fixed_price/with_weight/chapar/cod24` vali `match` cod24 nadarad -> default price — legacy. Evidence `ShippingMethodController.php:21`.
- **City pivot**: `availableShippingMethods` az `address->city` mikhune — agar user address khali bezane ya city pivot nadashte bashe methods khali — checkout.create ba `!addressId || !shippingMethodId => back error اطلاعات تکمیل نیست` fail mishe.
- **Session keys**: `checkout_address_id, checkout_shipping_method_id, checkout_shipping_price` — checkout.create in ha ro check mikone, vali `checkout/cities` faghat address_id migire — setShipment bayad ba'd seda zade she.

## Known Bugs (doc only)
- Chapar broken amdan doc, fix nakardin.
- `price` string compare loose — potential type bug vali logic ghol mide.
- `cod24` type no match fallback — legacy.

## AI Checklist
- [ ] Checked `chapar BROKEN` — fallback 0/dynamic
- [ ] Verified toman vs rial `'0'` before calc
- [ ] Checked freight_balance before ceiling
- [ ] Verified weight gram vs kg
- [ ] Tested city with empty pivot -> empty methods path
- [ ] Checked session keys required for checkout.create

## Evidence
- `app/Services/ChaparShipment.php:7 authToken '' BROKEN`
- `app/Services/ShippingCalculator.php:15 calculate match, 28 freight_balance, 33 price_ceiling, 59 '0' rial, 78 city->availableShippingMethods, 82 ceil`
- `app/Models/ShippingMethod.php:16 casts price string, 33 typeName`
- `app/Http/Controllers/Admin/ShippingMethodController.php:21 validate cod24 + min/max swap`
- `routes/site.php: POST /checkout/shipments + set-shipment + cities + address-price`
