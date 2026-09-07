# Audit / RequestLog — API Contract

> Source: `routes/site.php` (localized $siteRoutes x2), `routes/admin.php` (auth+adminPermission+log.admin), `web.php`

| Method | URI | Name | Middleware | Controller@Method | Auth |
|--------|-----|------|------------|-------------------|------|
| GET | /{locale}/audit | audit.index | web, SetLocale | Site Controller@index | guest/auth |
| GET | /admin/audit | admin.audit.index | auth,adminPermission,log.admin | Admin Controller@index | admin |
| POST | /admin/audit | admin.audit.store | auth,adminPermission | Admin Controller@store | admin |
| PUT | /admin/audit/{id} | admin.audit.update | auth,adminPermission | Admin Controller@update | admin |
| DELETE | /admin/audit/{id} | admin.audit.destroy | auth,adminPermission | Admin Controller@destroy | admin |

Notes:
- `Route::resource except show` pattern; custom routes (e.g., hero_slides search, bank toggle) are extra.
- Site routes duplicated under `{locale}` prefix with `locale.` name prefix — keep both.
- Throttle 5,1 on OTP/contact; 3,1 on resend.
- Validation: Admin FormRequest, Site inline or CardToCardCheckoutRequest.
- Check `routes/*.php` for exact URIs.
