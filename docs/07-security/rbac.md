# RBAC — Role Based Access Control

> **Source:** `app/Models/Traits/HasAdminPermissions.php`, `app/Http/Middleware/AdminPermission.php`, `config/permissions.php`, `AppServiceProvider.php: Gate::define`

## Models
- `roles: id, name unique, is_system`
- `permissions: id, name unique, group, label_key`
- Pivots: `permission_role`, `role_user`
- User `is_admin` boolean -> superadmin bypass.

## HasAdminPermissions trait
- `roles() belongsToMany Role`, `canAccess(routeName): bool` checks `config('permissions')` tree.
- Permissions tree in `config/permissions.php`: groups -> {label, permissions: [{name: admin.{group}.{access}, label}]} + `free` whitelist (no check).

## Middleware AdminPermission
```
1. if !Auth::check -> 401 json or redirect admin.login
2. if user->is_admin -> pass (bypass all)
3. if routeName in config('permissions.free') -> pass
4. if !user->canAccess(routeName) -> abort 403 "دسترسی غیرمجاز"
5. else pass
```
Applied to all `admin.php` routes: `middleware(['auth','adminPermission','log.admin'])`

## Gate
`Gate::define('admin', fn($user)=>$user->isAdmin())` in AppServiceProvider boot — used via `@can('admin')` or Gate::allows.

## Admin routes protection
All admin resources use `except('show')` to avoid 403 on show (maybe free?).

## AI gotcha
- is_admin bypasses all permission checks — don't add permission gate for superadmin.
- Route name is permission key: `admin.{resource}.{action}`.
- No Policy files — manual Gate/Middleware only.
