# RBAC / Admin — API Contract

> Source: `routes/site.php`, `routes/admin.php`, `web.php` (localized duplicates)

| Method | URI | Name | Middleware | Controller@Method | Auth |
|--------|-----|------|------------|-------------------|------|
| GET | /{locale}/rbac-admin | rbac-admin.index | web, SetLocale | Site/Admin Controller@index | auth if needed |
| GET | /admin/rbac-admin | admin.rbac-admin.index | auth,adminPermission,log.admin | Admin Controller@index | admin |
| POST | /admin/rbac-admin | admin.rbac-admin.store | auth,adminPermission | Admin Controller@store | admin |
| PUT | /admin/rbac-admin/{id} | admin.rbac-admin.update | auth,adminPermission | Admin Controller@update | admin |
| DELETE | /admin/rbac-admin/{id} | admin.rbac-admin.destroy | auth,adminPermission | Admin Controller@destroy | admin |

Notes:
- Actual routes per `Route::resource except show` + custom (e.g., checkout/finish, otp/send). Check `routes/*.php`.
- Site routes duplicated under `{locale}` prefix (locale.* names). $siteRoutes closure registered twice.
- Throttle on OTP (5,1), resend (3,1), contact (5,1).
- Validation: Admin via FormRequest, Site via inline or CardToCardCheckoutRequest.
