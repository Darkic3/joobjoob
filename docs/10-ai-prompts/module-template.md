# Module Template for AI

Use this template when documenting a new module or prompting AI for a feature.

```markdown
## Module: {Name}
Purpose: ...
Responsibilities: ...
Controllers: App\Http\Controllers\... (list)
Services: App\Services\... (list)
Models: App\Models\... (files)
DTOs/Requests: App\DTOs / Http\Requests (list)
Views: resources/views/{path}
Routes: routes/{file} -> name -> middleware (list table)
Database: tables, columns, indexes, FK, SoftDeletes (from migration)
Business Logic: step-by-step with code evidence
Dependencies: service->model graph + external (Kavenegar/Chapar)
Side Effects: Audit, Notification, Stock, Log
Transactions: DB::transaction ? lockForUpdate ?
Caching: rememberForever ? flush ?
Sessions: keys if any
Authorization: Gate/Middleware/Policy/is_admin?
Known Bugs: ...
Legacy Behavior: ...
Gotchas: (unit, string id, cache, double restore, etc)
Evidence: file:line references
```

## Required sections for AI prompt
1. Purpose + Responsibilities (1 paragraph)
2. Files involved (Controllers/Services/Models/DTOs)
3. Routes table (method, uri, name, middleware)
4. DB schema snippet (from migration)
5. Business logic trace with evidence
6. Dependencies list
7. Gotchas specific to module

Keep under 300 lines per module.
