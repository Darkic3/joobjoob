# Search — Dependencies

## Direct dependencies
- Models: Filterable trait, HeroSlideSearchController, Product
- Services: see `_shared/dependencies.md` for service->model graph.
- Helpers: `setting()`, `locale()`, `jdate()`, `format_price()` via composer autoload.files.
- External: none unless Kavenegar (Auth) / Chapar (Shipping) / ZarinPal (Payments) — for Search none.

## Calls
- Controller -> Service/DTO/Helper -> Model (approx 60% via Service, 40% direct Model).
- Observer: OrderObserver for orders only; others synchronous via Service.

## Coupling
- Reusable: LocaleService, SettingService, AuditService, PaymentGateway interface.
- Tightly coupled: Checkout session (Cart+Orders+Shipping+Discount) only.

## Evidence
- `app/Services/` + `app/Http/Controllers/` + `app/Models/` verified read-only.
