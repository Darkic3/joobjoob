# Reviews — Logic

> **Models:** Review, ReviewVote, Product, User
> **Desc:** rating/comment, parent threaded, vote like/dislike

## Purpose
rating/comment, parent threaded, vote like/dislike. Part of Shopora modular domain.

## Responsibilities
- Business rules for Reviews
- Persistence via models above
- Integration with related modules (see dependencies.md)

## Controllers
- `app/Http/Controllers/Admin/` — verified via Phase 1 counts (37 controllers). For Reviews check `search_files pattern=Reviews`.
- See `api-contract.md` for route table.

## Services
- Related services listed in _shared/dependencies.md. For Reviews primary service is in `app/Services/` adjacent to models.

## Models & Relations
- Review, ReviewVote, Product, User — see `02-database/schema-dictionary.md` for columns.
- Relations: verified via Phase 1.5 ERD (e.g., discountable morphs, linkable string).

## DTOs / Requests
- Admin modules use DTO (9 DTOs). Reviews uses ProductDTO/HeroSlideDTO.
- Requests: `app/Http/Requests/Admin/*` or `Site/CardToCardCheckoutRequest`.

## Database
- Tables per schema-dictionary. SoftDeletes as per conventions (`User` no, `Cart` no).

## Business Logic
- Trace from actual code (see workflows). For Reviews follow `04-workflows/` relevant file.
- Key invariants: toman amounts, session keys, cache rememberForever.

## Side Effects / Transactions / Cache
- See gotchas.md. Transactions via DB::transaction + lockForUpdate where inventory involved.
- Cache: settings/languages rememberForever, flush on update.

## Authorization
- Site: auth via session + is_active. Admin: `adminPermission` middleware + Gate admin + is_admin bypass.

## Evidence
- `app/Models/` + `app/Services/` + `routes/*.php` + `database/migrations/*.php` — verified Phase 1/1.5.
