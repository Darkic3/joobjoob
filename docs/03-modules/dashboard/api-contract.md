# Dashboard — API Contract

> Source: `routes/site.php` (localized $siteRoutes x2), `routes/admin.php` (auth+adminPermission+log.admin), `web.php`

| Method | URI | Name | Middleware | Controller@Method | Auth |
|--------|-----|------|------------|-------------------|------|
| GET | /{locale}/dashboard | dashboard.index | web, SetLocale | Site Controller@index | guest/auth |
| GET | /admin/dashboard | admin.dashboard.index | auth,adminPermission,log.admin | Admin Controller@index | admin |
| POST | /admin/dashboard | admin.dashboard.store | auth,adminPermission | Admin Controller@store | admin |
| PUT | /admin/dashboard/{id} | admin.dashboard.update | auth,adminPermission | Admin Controller@update | admin |
| DELETE | /admin/dashboard/{id} | admin.dashboard.destroy | auth,adminPermission | Admin Controller@destroy | admin |

Notes:
- `Route::resource except show` pattern; custom routes (e.g., hero_slides search, bank toggle) are extra.
- Site routes duplicated under `{locale}` prefix with `locale.` name prefix — keep both.
- Throttle 5,1 on OTP/contact; 3,1 on resend.
- Validation: Admin FormRequest, Site inline or CardToCardCheckoutRequest.
- Check `routes/*.php` for exact URIs.
