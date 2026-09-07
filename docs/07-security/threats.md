# Threats & Risks (Verified — Audit 2026-09-07)

> **Source:** `app/Helpers/Sms.php`, `app/Services/ChaparShipment.php`, `LogAdminRequests.php`, `OtpService.php`, `OrderService.php`, migrations
> **Mode:** Pure Audit — hich code fix nashod, faghat doc update.

| # | Title | Severity | Evidence | Impact | Status 2026-09-07 |
|---|-------|----------|----------|--------|-------------------|
|1| **Sms SSL verification** | **FIXED** | `Sms.php:77-78 CURLOPT_SSL_VERIFYPEER=true, VERIFYHOST=2` | MITM on OTP SMS API | **FIXED 2026-09-07** |
|2| RequestLog logs sensitive payment data | High | `LogAdminRequests.php:11 unset only password/_token` -> card_number, receipt_tracking_code stored plain in request_logs.payload | Data leak, PCI-like risk | **OPEN — Known Threat** |
|3| No index on orders(order_status,created_at) | Medium | `create_orders_table.php:45` only user_id index; query `CancelExpiredOrders where paying && created_at<cutoff` full scan | Performance on large orders | OPEN |
|4| Chapar authToken empty | **BROKEN by design** | `ChaparShipment.php:8 ''` | Shipping quotes always fail, fallback to 0/dynamic | **BROKEN — P0-05** |
|5| OrderObserver double restoreStock | **FIXED** | Observer updated + OrderService cancelOrder both call restoreStock without idempotency guard — **FIXED 2026-09-07: Observer restore hazf** | Stock double increment | **FIXED 2026-09-07** |
|6| OTP 4-digit | Medium | `OtpService::CODE_LENGTH=4` | 10k brute force, mitigated by 2min TTL + throttle 5/1 | OPEN (by design) |
|7| Hard delete on payment token failure | **FIXED** | `BankPaymentService::handlePayment if failed -> order->delete()` — **FIXED 2026-09-07 -> unpaid + restore + audit** | Order lost | **FIXED 2026-09-07** |
|8| OrderObserver created bug | **FIXED** | `Observer::created isDirty` always false -> no OrderHistory — **FIXED 2026-09-07: unconditional create** | Audit gap | **FIXED 2026-09-07** |
|9| is_admin bypass all RBAC | High (by design) | `AdminPermission.php: superadmin check` | Compromise of is_admin = full admin | OPEN (by design) |
|10| Cart N+1 | Low | `CartService dbContent Product::find per item` | Performance | OPEN |
|11| **Chapar MITM (HTTP, no SSL)** | High | `ChaparShipment.php:7 baseUrl=http://api.krch.ir/v1/` — plain HTTP, hich TLS nist | MITM on shipping quote API | **OPEN — Known Threat** |
|12| **RequestLog payload leak (card/payer)** | High | `LogAdminRequests: payload = input()` faghat 3 field filter — `receipt_tracking_code`, `receipt_payer_card`, `card_number`, `mobile` log mishavad | PII leak in DB | **OPEN — Known Threat** |

> P0-08 tafkik: **Sms FIXED** (true/2) ama **Chapar hanuz BROKEN + HTTP** — do threat joda.

No Critical RCE/SQLi found with evidence. Xsrf via web middleware, throttle on otp/contact.

## Known Threats — Detail (P0-08 Payload Leak & MITM)

### T11 — Chapar HTTP MITM
- **Evidence:** `ChaparShipment.php:7 http://api.krch.ir/v1/` — hich `https://`, hich `CURLOPT_SSL_*` (chون HTTP ast, SSL aslan check nemishavad).
- **Risk:** Quote request (orderData JSON) dar transit sniffable.
- **Doc-e ghabli:** Faghat `authToken empty` minevesht — MITM-e HTTP mostanad nashode bood.

### T12 — RequestLog Payload Leak
- **Evidence:** `LogAdminRequests.php: handle() { payload=input(); unset password/_token }` — faghat 2 field filter.
- **Leak fields:** `receipt_tracking_code`, `receipt_payer_card`, `card_number`, `email`, `mobile`, `address` — hame dar `request_logs.payload` (json) zakhire mishavad.
- **Fix idea (doc only, no code):** `unset($payload['receipt_tracking_code'], $payload['receipt_payer_card'], $payload['card_number'])` + config allowlist.

## Mitigation notes for AI
- Don't disable SSL verification elsewhere — Sms alan FIXED, Chapar HTTP bayad be HTTPS migrate shavad ya disable shavad.
- Filter sensitive fields in LogAdminRequests before create — 4 field-e bala.
- Add composite index in future migration (out of scope Phase2 doc only).
