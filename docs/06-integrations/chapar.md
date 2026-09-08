# Chapar Shipping Integration — BROKEN

> **Source:** `app/Services/ChaparShipment.php`, `ShippingCalculator.php:chaparQuote`, `app/Models/ShippingMethod.php`, `State.php/City.php`
> **Audit:** 2026-09-07 — Pure Audit, hich code fix nashod.

## Status: BROKEN (no authToken + plain HTTP) — P0-05

```php
// ChaparShipment.php:5-6
protected static string $baseUrl = 'http://api.krch.ir/v1/';
protected static string $authToken = ''; // empty hardcode
```

No env/config/setting is read. Every quote will fail (header APP-AUTH empty). `ShippingCalculator::chaparQuote` will catch exception and return "0" or "dynamic".

## Known Threat — HTTP MITM (P0-08 tafkik)

```php
// ChaparShipment.php:7
protected static string $baseUrl = 'http://api.krch.ir/v1/'; // plain HTTP, no TLS
```

- Hich `CURLOPT_SSL_*` aslan set nashode chon HTTP ast — SSL verify concept nadarad.
- Quote payload (`orderData` JSON) sniffable dar transit — MITM risk.
- Doc-e ghabli faghat `authToken empty` minevesht — MITM HTTP mostanad nashode bood.
- **Fix idea (doc only):** `http://` -> `https://` + `CURLOPT_SSL_VERIFYPEER=true/VERIFYHOST=2` (agar endpoint TLS dashte bashad), ya type `chapar` ra dar `ShippingMethod` ghire-faal kon.

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
- HTTP MITM + authToken empty = double reason to keep disabled (P0-05 / T11).
