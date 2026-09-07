# Request Logging

> **Source:** `app/Http/Middleware/LogAdminRequests.php`, `app/Models/RequestLog.php`, `routes/admin.php middleware log.admin`

## Middleware
```php
handle($request, $next):
  $response = $next($request);
  if Auth::check():
    payload = $request->input()
    unset password, password_confirmation, _token
    // BUG: no unset for receipt_tracking_code, receipt_payer_card, card_number
    RequestLog::create([user_id, method, url, ip, status_code, payload (json cast)])
  return $response
```
Applied to all admin routes (`log.admin` alias).

## Model
`request_logs: id, user_id FK, method, url, ip, status_code, payload json, timestamps` — no softDeletes.

## Risk
Payload contains card-to-card tracking data when admin edits orders via form? Actually request_logs logs admin's own payload (admin form), but if checkout card data is logged via admin? Still sensitive. Should filter `receipt_*`.

## AI gotcha for vibe coding
- Always filter sensitive fields when adding new forms that handle payment/receipt.
- Consider adding `protected $hidden` or middleware filter list.
