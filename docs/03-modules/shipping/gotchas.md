# Shipping — Gotchas & Known Bugs (Verified Audit)

---

## 1. P0-05 — CLIENT-CONTROLLED `shipping_price` — STILL UNFIXED ⚠️

**Bug:** `CheckoutController::setShipment` (line 73-76) stores BOTH values verbatim from client:
```php
session([
    'checkout_shipping_method_id' => $request->get('shipping_method_id'),
    'checkout_shipping_price'     => $request->get('shipping_price', 0),
]);
```
No validation whatsoever. `checkout/create` (line 172) reads `session('checkout_shipping_price')` and passes it to `OrderService::create` as `shipping_price`. A tampered `shipping_price=0` means free shipping — no server-side recalculation.

**Impact:** HIGH — user can set any shipping price, including 0, for any method.

**Fix:** Server-side recalculation at `checkout/create`:
```php
$shippingPrice = ShippingCalculator::calculate($shippingMethod, $cart->roundedWeight(), $cart->total())['price'];
```
Ignore session value entirely.

---

## 2. ChaparShipment — FULLY DEAD CODE (confirmed zero callers)

**Bug:** `ChaparShipment` class has **zero callers** anywhere in the codebase (grep verified). Methods `bulk/quote/agent` are unused. `ShippingCalculator::chaparQuote` method **no longer exists** — only an orphan comment `// استعلام قیمت چاپار` remains at line ~46.

**If ever revived, note the triple landmine:**
1. `baseUrl` is `http://api.krch.ir/v1/` (PLAIN HTTP — token travels in the clear, MITM risk)
2. `$authToken` hardcodes `''` with no config/env setter
3. Raw `curl` + `json_decode($response, true) ?? []` silently swallows malformed responses

**Status:** Dead code — delete the class + orphan comment + unused `sender_*/chapar_type/config` columns remain schema-only.

---

## 3. `cod24` type — stale price, no match in calculate

**Bug:** Admin validation allows `type: cod24` (`ShippingMethodController:21`), but `ShippingCalculator::calculate` has no `cod24` match case → falls through to `default => price = method->price ?? 0`. The `cod24` price stored in DB is static and never recalculated via any API.

**Impact:** Medium — admin creates `cod24` method, sets a price, it works as fixed_price. But the intent was dynamic pricing via API.

**Fix:** Either remove `cod24` from validation or implement the API integration.

---

## 4. `price` is cast as `string` — loose comparison risks

**Bug:** `ShippingMethod::$casts = ['price' => 'string']`. `ShippingCalculator::calculate` returns `['price' => $price]` where `$price` could be string or int. `getAvailableMethods` does `is_numeric($price) && $price == 0` — loose comparison.

**Impact:** Low — works in practice, but `0 == "0" == "" == false` in PHP loose comparison.

**Fix:** Cast to `floatval()` consistently.

---

## 5. `min/max` swap in admin — cosmetic only

**Bug:** `ShippingMethodController::store/update` does:
```php
$validated['estimated_days_min'] = min($validated['estimated_days_min'], $validated['estimated_days_max']);
$validated['estimated_days_max'] = max($validated['estimated_days_min'], $validated['estimated_days_max']);
```
This swaps if min > max, but the second line uses the ALREADY-swapped min. So if input is `min=10, max=5`:
- Line 1: `min = min(10, 5) = 5`
- Line 2: `max = max(5, 5) = 5` ← both become 5!

**Impact:** Low — edge case, but correct behavior should preserve the larger value.

**Fix:** Capture original values before swap:
```php
$min = min($validated['estimated_days_min'], $validated['estimated_days_max']);
$max = max($validated['estimated_days_min'], $validated['estimated_days_max']);
$validated['estimated_days_min'] = $min;
$validated['estimated_days_max'] = $max;
```

---

## 6. `freight_balance` overrides `price_ceiling` — ordering matters

**Bug:** In `ShippingCalculator::calculate`, `freight_balance==1` sets `price=0` BEFORE `price_ceiling` check. If both are enabled, `freight_balance` wins. This is correct behavior but undocumented — admin might expect both to apply.

**Evidence:** `ShippingCalculator.php:28-36`

---

## 7. `with_weight` rounding — ceil to 100g

**Bug:** `getAvailableMethods` rounds weight: `ceil($weightSum / 100) * 100`. A 150g product becomes 200g. `calculate` then does `($weightSum / 100) * price`. Double rounding possible.

**Impact:** Low — intentional design (shipping by 100g increments).

---

## 8. City pivot empty → silent zero shipping

**Bug:** If `address->city?->availableShippingMethods` is empty (no methods assigned to city), `getAvailableMethods` returns `[]`. Then `setShipment` stores `shipping_price=0` (default). Order created with free shipping.

**Impact:** Medium — admin must remember to assign methods to ALL cities.

**Fix:** Validate `shipping_method_id` is in available methods list at `checkout/create`.

---

## 9. No `ShippingMethodRequest` — inline validation duplicated

**Bug:** `ShippingMethodController::store` and `update` have identical inline validation rules (~15 rules each). No FormRequest class exists.

**Impact:** Low — code duplication, not a bug. But violates DRY.

---

## AI Checklist before coding Shipping

- [ ] **P0-05**: Server-side recalculate shipping price at `checkout/create` — ignore session value
- [ ] Checked `chapar` dead code — don't call `ChaparShipment` methods
- [ ] Verified `cod24` type falls through to default price
- [ ] Used `floatval()` for price comparison, not loose `==`
- [ ] Checked city has methods assigned before offering shipping
- [ ] Verified `freight_balance` overrides `price_ceiling` (correct order)

## Evidence

- `app/Services/ShippingCalculator.php:15-40` calculate, 46 orphan comment, 78 getAvailableMethods
- `app/Services/ChaparShipment.php:7-8` dead code, MITM, empty token
- `app/Models/ShippingMethod.php:8-14` fillable, 16 casts (price string)
- `app/Http/Controllers/Admin/ShippingMethodController.php:21-48` store validate + swap
- `app/Http/Controllers/Site/CheckoutController.php:71-79` P0-05 setShipment
- `routes/admin.php:92-93` resource + toggle-status
