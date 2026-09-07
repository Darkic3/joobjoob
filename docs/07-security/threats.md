# Threats & Risks (Verified)

> **Source:** `Sms.php`, `LogAdminRequests.php`, `OtpService.php`, `OrderService.php`, `ChaparShipment.php`, migrations

| # | Title | Severity | Evidence | Impact |
|---|-------|----------|----------|--------|
|1| Sms SSL verification disabled | High | `Sms.php:43 CURLOPT_SSL_VERIFYPEER=0,44 VERIFYHOST=0` in execute + executePost | MITM on OTP SMS API |
|2| RequestLog logs sensitive payment data | High | `LogAdminRequests.php:11 unset only password/_token` -> card_number, receipt_tracking_code stored plain in request_logs.payload | Data leak, PCI-like risk |
|3| No index on orders(order_status,created_at) | Medium | `create_orders_table.php:45` only user_id index; query `CancelExpiredOrders where paying && created_at<cutoff` full scan | Performance on large orders |
|4| Chapar authToken empty | Medium | `ChaparShipment.php:6 ''` | Shipping quotes always fail, fallback to 0/dynamic |
|5| OrderObserver double restoreStock | Medium | Observer updated + OrderService cancelOrder both call restoreStock without idempotency guard | Stock double increment if both triggered |
|6| OTP 4-digit | Medium | `OtpService::CODE_LENGTH=4` | 10k brute force, mitigated by 2min TTL + throttle 5/1 |
|7| Hard delete on payment token failure | Medium | `BankPaymentService::handlePayment if failed -> order->delete()` | Order lost, not cancelled+audited |
|8| OrderObserver created bug | Medium | `Observer::created isDirty` always false -> no OrderHistory for new order | Audit gap |
|9| is_admin bypass all RBAC | High (by design) | `AdminPermission.php: superadmin check` | Compromise of is_admin = full admin |
|10| Cart N+1 | Low | `CartService dbContent Product::find per item` | Performance |

No Critical RCE/SQLi found with evidence. Xsrf via web middleware, throttle on otp/contact.

## Mitigation notes for AI
- Don't disable SSL verification elsewhere.
- Filter sensitive fields in LogAdminRequests before create.
- Add composite index in future migration (out of scope Phase2 doc only).
