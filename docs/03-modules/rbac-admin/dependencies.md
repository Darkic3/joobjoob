# RBAC / Admin — Dependencies

## Direct dependencies
- Models: User, Role, Permission, HasAdminPermissions, AdminPermission middleware
- Services: see `_shared/dependencies.md` graph.
- Helpers: `setting()`, `locale()`, `jdate()`, `format_price()` via service locator.
- External: none

## Calls
- Controllers -> Services -> Models (with ~40% direct Model calls).
- Observer: OrderObserver for related.

## Coupling
- Reusable: ShippingCalculator, LocaleService, PaymentGateway interface, AuditService

## Evidence
- `app/Services/` + `app/Http/Controllers/` + `app/Models/` verified.
