# Shipping — Logic (Enriched Phase 2.1)

> **Source:** `app/Models/ShippingMethod.php:1-100`, `app/Services/ShippingCalculator.php:1-180`, `app/Services/ChaparShipment.php:1-70`, `app/Http/Controllers/Admin/ShippingMethodController.php:1-140`, `app/Http/Controllers/Site/CheckoutController.php:shipments/setShipment/cities/addressPrice`, `routes/admin.php + routes/site.php`

## 1. Purpose
Shipping 3 no'e mohasebe dare: `fixed_price` (price), `with_weight` ((weight/100)*price), `chapar` (API quote) — ba `freight_balance` (پس کرایه =>0) va `price_ceiling` (سقف => رایگان) override.

## 2. Model — ShippingMethod (`app/Models/ShippingMethod.php`)
- `fillable: title, price (string!), price_ceiling (double), status, description, estimated_days_min/max, show_estimated_days, freight_balance (int 0/1), type, chapar_type, config(json), sender_*: sender_name/company/sender_city_id/sender_phone/mobile/email/address/postal_code, softDeletes` — evidence line 8-14.
- `casts: price=>string, price_ceiling=>double, config=>json, freight_balance=>integer` — price string hast chon fixed/with_weight string, chapar dynamic.
- `relations: cities() belongsToMany(City, shipping_method_city) -> availableShippingMethods pivot, senderCity() hasOne(City, id, sender_city_id)`.
- `scopes: scopeActive => where status 1`.
- `accessors: getTypeNameAttribute match type: chapar=>چاپار/success, cod24=>Cod24/primary, with_weight=>بر اساس وزن/info, default=>قیمت ثابت/info; getFreightBalanceNameAttribute 1=>پس کرایه/success else پیش کرایه/info; getEstimatedDaysTextAttribute min==max ? 'X روز کاری' else 'X تا Y روز کاری'`.

## 3. Service — ShippingCalculator (`app/Services/ShippingCalculator.php`)
### 3.1 calculate(ShippingMethod $method, float $weightSum, float $finalPriceSum, ?Address $address): array{price:string, freight_balance:int}
- `price=''` default empty string, `freightBalance=0`.
- `match type:` `fixed_price => price=method->price ??0`, `with_weight => price=(weightSum/100)*price`, `chapar => price=chaparQuote(...)`, `default => price`. Evidence 15-26.
- Agar `freight_balance==1` => `price=0, freightBalance=1` — override har price. Evidence 28-31.
- Agar `price_ceiling>0 && finalPriceSum > price_ceiling` => `price=0` — رایگان. Evidence 33-36. Note: freight_balance=1 ham 0 mikone vali ceiling ham.

### 3.2 calculateRaw (private)
- Same match vali `freight_balance 1 =>0` faghat, ceiling ham? No — `calculateRaw` ham `price_ceiling` handle mikone for original_price case.

### 3.3 chaparQuote(ShippingMethod, weightSum, finalPriceSum, ?Address): string
- `try ChaparShipment::quote([origin=>senderCity?->chapar_id, destination=>address?->city?->chapar_id, weight=>weightSum/1000 kg, value=>finalPriceSum.'0' rial, method=>chapar_type])`. Evidence 55-63.
- Agar `result===true` => `totalCost = objects.order.price.fld_Total_Cost ??0` (rial), `return totalCost/10` (toman). Evidence 64-66.
- Catch Exception log `Chapar quote failed` return 0 (string/int 0). Evidence 68-71.
- **BROKEN**: `ChaparShipment::$authToken=''`, har quote catch =>0. Status documented.

### 3.4 getAvailableMethods(Address $address, float weightSum, float finalPriceSum): array
- `$shippingMethods = address->city?->availableShippingMethods ?? collect()` — evidence 78 — pivot `shipping_method_city` filter.
- Loop har `shipment`: agar type with_weight => `shipmentWeightSum = ceil(weightSum/100)*100` (round up 100gr). Evidence 82-84.
- Agar `type in [with_weight, fixed_price]` => `$result=calculate(shipment, shipmentWeightSum, finalPriceSum, address); price=result['price']` else chapar => `price='dynamic'`. Evidence 86-90.
- **original_price** baraye namayesh سود رایگان: `originalPrice = is_numeric(price)?price:0; if price_ceiling>0 && freight_balance!=1 && price==0 && finalPriceSum>=ceiling => originalPrice=calculateRaw(...)`. Evidence 92-98.
- Return per method array: `id, title, type, freight_balance, description, price, original_price, price_ceiling, estimated_days_min/max, show_estimated_days`. Evidence 100-112.

## 4. Service — ChaparShipment (`app/Services/ChaparShipment.php`)
- `protected static string baseUrl='http://api.krch.ir/v1/'`, `authToken=''` — evidence 7-8 — BROKEN.
- `request(endpoint, postFields):array` — `curl_init`, `CURLOPT_URL baseUrl+endpoint`, `RETURNTRANSFER true, TIMEOUT 30, FOLLOWLOCATION, HTTP_VERSION 1.1, CUSTOMREQUEST POST, POSTFIELDS, HTTPHEADER APP-AUTH: authToken` — evidence 11-25. `curl_exec`, `curl_errno` throw, `json_decode response true ?? []`.
- `quote(array orderData):array` — `json_encode(['order'=>orderData], UNESCAPED_UNICODE)` -> `request('get_quote',['input'=>json])`. `bulk(json)` -> `bulk_import`, `agent()` -> `fetch_agent`. Evidence 33-44.

## 5. Controllers
### 5.1 Admin/ShippingMethodController (`app/Http/Controllers/Admin/ShippingMethodController.php`)
- `index()` — `ShippingMethod::with('cities')->latest()->get()` view `admin.shipping-methods.index`. Evidence 11-14.
- `create()` — `City::active()->select(id,name,state_id)->orderBy(name)->get()` view create. Evidence 16-19.
- `store(Request)` — validate `title required string max255, type required in fixed_price/with_weight/chapar/cod24, price nullable numeric min0, price_ceiling nullable numeric, estimated_days_min/max required integer 1..30, status required boolean, description nullable string, freight_balance nullable boolean, chapar_type nullable string, sender_* nullable, cities nullable array`, then `min/max swap` (min=min(min,max) max=max), `cities ?? []` sync, `ShippingMethod::create(validated)` + `cities()->sync`. Evidence 21-48.
- `edit(ShippingMethod)` — load cities, `selectedCityIds = cities->pluck(id)`. Evidence 50-55.
- `update(Request, ShippingMethod)` — same validate + min/max swap + sync. Evidence 57-86.
- `destroy(ShippingMethod)` — `delete()` softDeletes. Evidence 88-92.
- `toggleStatus` — `update status ?0:1` back. Evidence 94-97.

### 5.2 Site/CheckoutController for shipping
- `cities(Request):JsonResponse` — `CityService::findActiveByState(state_id)` -> `cities`. Evidence CheckoutController:30-34.
- `shipments(Request):JsonResponse` — `AddressService::findOne(address_id)` -> `session[checkout_address_id]=id` + `ShippingCalculator::getAvailableMethods(address, cart->roundedWeight(), cart->total())` -> `shipping_methods`. Evidence 37-49.
- `setShipment(Request):JsonResponse` — `session[checkout_shipping_method_id, checkout_shipping_price]` -> success. Evidence 53-60.
- `addressPrice():JsonResponse` — reads `session checkout_address_id/shipping_method_id/shipping_price`, `cart->subtotal()/discount()/total()` + `intval(shippingPrice)` => `price_cart`. Same for `orderPrice`. Evidence 63-78.

## 6. Models — State/City/Address
- `states: id, name, status, cod24_id, chapar_id` (for shipping origin/destination mapping).
- `cities: id, name, state_id FK, status, chapar_id, cod24_id`, `availableShippingMethods()` via pivot, `scopeActive`.
- `addresses: id, user_id, state_id, city_id, address (text), receiptor_full_name, receiptor_mobile, postal_code, softDeletes? check`.

## 7. Validation / Auth / Side Effects
- Admin shipping-methods behind `auth + adminPermission + log.admin` prefix admin.
- Validation includes `cod24` legacy type — allowed but not used in calculate (falls to default price).
- `chapar_type` nullable string — forwarded to Chapar API `method`.
- No transaction needed — simple CRUD + sync.

## 8. Transactions / Cache
- No Cache for shipping; no transaction for calc (pure). getAvailableMethods called per address change (AJAX).

## 9. Business Rules / Legacy
- `cod24` type in validate allowed vali `ShippingCalculator::calculate` match nadarad → default price — legacy, doc as is.
- `price string` cast — numeric string handled `floatval` in calculateRaw.
- `chapar BROKEN` — har chapar method `price=dynamic` ya 0 fallback — agent bayad fallback بداند.

## 10. Evidence
- `app/Models/ShippingMethod.php:8 fillable, 16 casts, 20 cities, 26 senderCity, 33 getTypeName`
- `app/Services/ShippingCalculator.php:15 calculate match, 55 chaparQuote .'0' +/10, 78 getAvailableMethods city->availableShippingMethods`
- `app/Services/ChaparShipment.php:7 baseUrl+authToken BROKEN, 11 request curl`
- `app/Http/Controllers/Admin/ShippingMethodController.php:21 store validate + min/max swap + sync cities`
- `app/Http/Controllers/Site/CheckoutController.php:37 shipments + 53 setShipment + 63 addressPrice`
