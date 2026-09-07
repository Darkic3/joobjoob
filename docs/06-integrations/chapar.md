# Chapar Shipping Integration — BROKEN

> **Source:** `app/Services/ChaparShipment.php`, `ShippingCalculator.php:chaparQuote`, `app/Models/ShippingMethod.php`, `State.php/City.php`

## Status: BROKEN (no authToken)

```php
// ChaparShipment.php:5-6
protected static string $baseUrl = 'http://api.krch.ir/v1/';
protected static string $authToken = ''; // empty hardcode
```

No env/config/setting is read. Every quote will fail (header APP-AUTH empty). `ShippingCalculator::chaparQuote` will catch exception and return "0" or "dynamic".

## API
- `quote(orderData)` -> `request('get_quote', ['input'=>json_encode(['order'=>orderData])])`
- `bulk(jsonInput)` -> bulk_import
- `agent()` -> fetch_agent
- `request(endpoint, postFields)` uses curl POST with `APP-AUTH: authToken`, timeout 30s.

## Integration point
`ShippingCalculator::calculate` -> match type chapar -> chaparQuote(shippingMethod, weightSum, finalPriceSum, address):
```php
ChaparShipment::quote([
  origin => shippingMethod->senderCity?->chapar_id,
  destination => address?->city?->chapar_id,
  weight => weightSum/1000 kg,
  value => finalPriceSum.'0' toman->rial,
  method => shippingMethod->chapar_type
])
if result===true -> totalCost = objects.order.price.fld_Total_Cost /10 rial->toman else 0
```
If freight_balance==1 -> price 0 regardless.
Weight stored in cities/states as `chapar_id`, `cod24_id`.

## ShippingMethod types
`fixed_price`, `with_weight ((weight/100)*price)`, `chapar` — plus price_ceiling and freight_balance.

## AI gotcha
- Must mark BROKEN in docs; don't try to use without fixing authToken source.
- Value/amount again uses `'0'` concat for rial.
