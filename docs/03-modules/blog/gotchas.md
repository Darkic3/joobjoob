# Blog — Gotchas & Known Bugs

- Cache rememberForever settings/languages, direct DB edit misses flush
- Images become WebP via GD in public/uploads
- is_admin bypasses all RBAC — AdminPermission middleware

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
