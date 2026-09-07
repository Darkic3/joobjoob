# Discounts — API Contract

> Source: `routes/site.php`, `routes/admin.php`, `web.php` (localized duplicates)

| Method | URI | Name | Middleware | Controller@Method | Auth |
|--------|-----|------|------------|-------------------|------|
| GET | /{locale}/discounts | discounts.index | web, SetLocale | Site/Admin Controller@index | guest/auth |
| GET | /admin/discounts | admin.discounts.index | auth,adminPermission,log.admin | Admin Controller@index | admin |
| POST | /admin/discounts | admin.discounts.store | auth,adminPermission | Admin Controller@store | admin |
| PUT | /admin/discounts/{id} | admin.discounts.update | auth,adminPermission | Admin Controller@update | admin |
| DELETE | /admin/discounts/{id} | admin.discounts.destroy | auth,adminPermission | Admin Controller@destroy | admin |

Notes:
- Actual routes per `Route::resource except show` + custom (e.g., checkout/finish, otp/send). Check `routes/*.php`.
- Site routes duplicated under `{locale}` prefix (locale.* names). $siteRoutes closure registered twice.
- Throttle on OTP (5,1), resend (3,1), contact (5,1).
- Validation: Admin via FormRequest, Site via inline or CardToCardCheckoutRequest.
