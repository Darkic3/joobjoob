# Audit / RequestLog — Gotchas & Known Bugs

- RequestLog only filters password -> card data leaks — LogAdminRequests.php:11
- OrderObserver created isDirty bug -> no history for new order
- Cache rememberForever settings/languages, direct DB edit misses flush

## Known Bugs (doc only)
- See `07-security/threats.md` + `04-workflows/` for relevant bugs.
- No fix in Phase 2; only document current behavior.

## Legacy Behavior
- Themes legacy, discount pay_type nullable = all products, with_discount controls stacking.
- Keep behavior for AI; don't refactor.

## AI Checklist
- [ ] Checked global-context.md
- [ ] Checked relevant workflow
- [ ] Verified unit if payment/shipping
- [ ] Checked cache flush if settings/languages

## Evidence
- Verified read-only from `app/` + `database/migrations/*.php` + `routes/*.php`.
- UNKNOWN preserved where caller not found (mergeGuestCart, themes roadmap).
