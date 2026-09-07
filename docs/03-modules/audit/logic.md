# Audit / RequestLog — Logic

> **Models:** AuditLog, RequestLog, AuditService
> **Desc:** AuditService::log auditable, RequestLog payload only password filtered

## Purpose
AuditService::log auditable, RequestLog payload only password filtered. Core domain of Shopora.

## Responsibilities
- CRUD via Admin resources (except show)
- Business rules per Audit / RequestLog
- Integration with related domains (see dependencies.md)

## Controllers
- `app/Http/Controllers/Admin/` for admin side, `app/Http/Controllers/Site/` for site side where applicable.
- Actual list in `api-contract.md`. Verified via 37 controllers count (25 Admin + 11 Site).

## Services
- Primary service(s) in `app/Services/` for Audit / RequestLog. See `_shared/dependencies.md`.
- Helpers: `setting()`, `locale()`, `format_price()` as service locator.

## Models & Relations
- AuditLog, RequestLog, AuditService — columns/indexes/FK/SoftDeletes in `02-database/schema-dictionary.md`.
- Relations verified in Phase 1.5 ERD (mermaid in 02-database/erd.md).

## DTOs / Requests
- Admin uses DTO if complex (9 DTOs). Audit / RequestLog uses FormRequest inline validate if simple.
- Requests in `app/Http/Requests/Admin/*` or `Site/*`.

## Database
- Tables per schema-dictionary. SoftDeletes per conventions.

## Business Logic
- Detailed traces in `04-workflows/` where relevant; otherwise CRUD with Filterable trait + HasSlug/HasImageAttributes.
- Money stored in toman; gateway needs rial `.'0'`.

## Side Effects / Transactions / Cache
- AuditService::log on create/update, NotificationService on order flows, StockService lockForUpdate for inventory-related.
- Cache rememberForever for settings/languages, flush() on update.

## Authorization
- Admin: `adminPermission` middleware + Gate admin + is_admin bypass per `07-security/rbac.md`.
- Site: auth session middleware where needed.

## Evidence
- `app/Models/*.php` + `app/Services/*.php` + `routes/*.php` + `database/migrations/*.php` — Phase 1/1.5 verified read-only.
