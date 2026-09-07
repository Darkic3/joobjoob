# Discounts — Gotchas & Known Bugs

- Order status 7 values, no StateMachine, admin can set any
- Cart guest session `cart` (md5 rowId) + auth DB `carts`; mergeGuestCart has no caller — UNKNOWN
- is_admin bypasses all RBAC
- Cache rememberForever for settings/languages, direct DB edit misses flush

## Known Bugs (document only, no fix)
- See `07-security/threats.md` and `04-workflows/` for module-specific bugs.
- OrderObserver created history missing, double restore, hard delete on payment token fail, missing index on orders.

## Legacy Behavior
- Themes legacy, discountable pay_type nullable = all products, with_discount controls stacking.
- keep current behavior for AI; do not refactor.

## AI Checklist before coding Discounts
- [ ] Checked global-context.md
- [ ] Checked workflows relevant to Discounts
- [ ] Verified amount unit if payment/shipping
- [ ] Checked session keys if cart/checkout
- [ ] Checked cache flush if settings/languages

## Evidence
- `app/Services/OrderService.php`, `StockService.php`, `ZarinPal.php:20`, `LogAdminRequests.php:11`, `ChaparShipment.php:6`
- UNKNOWN preserved where caller not found.
