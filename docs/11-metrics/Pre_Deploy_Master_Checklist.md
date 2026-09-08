# Pre-Deploy Master Checklist

> **Date:** 2026-09-08
> **Author:** Hermes + Buffy (collaborative)
> **Purpose:** Final checklist before `git push` and production deployment
> **Status:** Ready for manual execution

---

## 1️⃣ Code & Database Readiness

### Migrations to Run

```bash
php artisan migrate --force
```

**New migrations (must run in order):**

| Migration | Table | Purpose |
|-----------|-------|---------|
| `2026_09_08_000001_create_wishlists_table` | `wishlists` | Dual-mode wishlist (session + DB) |
| (existing) | `settings` | Smart Notifications toggle fields |
| (existing) | `notifications` | Notification system |

**Verify after migration:**
- [ ] `wishlists` table exists with unique constraint `(user_id, product_id)`
- [ ] `settings` table has `smart_notifications` group entries
- [ ] No migration errors in `storage/logs/laravel.log`

### Environment Configuration

- [ ] `APP_DEBUG=false` in `.env`
- [ ] `APP_ENV=production` in `.env`
- [ ] `APP_URL=https://shopora.ir` (or actual domain)
- [ ] `DB_CONNECTION=mysql` (NOT sqlite)
- [ ] `DB_HOST=127.0.0.1` (or correct DB host)
- [ ] `DB_DATABASE=shopora` (correct DB name)
- [ ] `DB_USERNAME=shopora_user` (correct DB user)
- [ ] `DB_PASSWORD=***` (strong password)
- [ ] `QUEUE_CONNECTION=database` (or `sync` for single-server)
- [ ] `SESSION_DRIVER=database` (or `file` — NOT `array`)
- [ ] `CACHE_DRIVER=file` (or `redis` if available)
- [ ] `MAIL_MAILER=log` (or correct mail config)

### Composer & NPM

```bash
composer install --no-dev --optimize-autoloader
npm install && npm run build
```

- [ ] `composer.json` has no dev dependencies in production
- [ ] `package.json` build completes without errors
- [ ] `public/build/` directory exists with compiled assets

---

## 2️⃣ Security & Performance Final Check

### Artisan Caching

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

**Verify:**
- [ ] `bootstrap/cache/config.php` exists
- [ ] `bootstrap/cache/routes-v7.php` exists
- [ ] `storage/framework/views/` has compiled views
- [ ] No cache errors in logs

### File Permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

- [ ] `storage/` is writable by web server
- [ ] `bootstrap/cache/` is writable by web server
- [ ] `public/uploads/` is writable (for product images)
- [ ] `php artisan storage:link` executed (symlink created)

### Cron Jobs

```bash
crontab -e
```

**Required cron entry:**
```
* * * * * cd /path/to/shopora && php artisan schedule:run >> /dev/null 2>&1
```

**Verify:**
- [ ] Cron runs every minute
- [ ] `php artisan schedule:list` shows `orders:cancel-expired --minutes=15`
- [ ] `CancelExpiredOrders` command runs (P0-06 fix)

### Security Headers

**Already implemented via `SecureHeadersMiddleware`:**
- [ ] `X-Frame-Options: SAMEORIGIN` (Clickjacking protection)
- [ ] `X-Content-Type-Options: nosniff` (MIME sniffing protection)
- [ ] `Referrer-Policy: strict-origin-when-cross-origin`
- [ ] `Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()`
- [ ] `Strict-Transport-Security: max-age=31536000; includeSubDomains` (HTTPS only)

### Rate Limiting

**Already implemented via named limiters:**
- [ ] `reviews` — 5/min per IP
- [ ] `wishlist` — 30/min per user/IP
- [ ] `cart_actions` — 30/min per user/IP
- [ ] `checkout` — 10/min per user
- [ ] `discount` — 10/min per user

---

## 3️⃣ Third-Party Services Setup (Admin Panel)

### Payment Gateway — ZarinPal

- [ ] Login to admin panel → Settings → Payment Gateways
- [ ] Add/Edit ZarinPal bank entry
- [ ] Set `MerchantId` (from ZarinPal panel)
- [ ] Set `card_number` (for display)
- [ ] Set `card_holder_name`
- [ ] Test with sandbox amount (1000 Toman)

**Verify:**
- [ ] `config('services.zarinpal.MerchantId')` is NOT empty
- [ ] ZarinPal test transaction succeeds

### SMS Service — Kavenegar

- [ ] Login to admin panel → Settings → SMS
- [ ] Set `kavenegar_key` (API key from Kavenegar panel)
- [ ] Set `kavenegar_sender` (sender number)
- [ ] Set `kavenegar_number_test` (test number)
- [ ] Set `otp_template` (template name, default: `verify`)

**Verify:**
- [ ] Test OTP send works (try login with test number)
- [ ] SMS received on test phone

### Shipping — Chapar

- [ ] **DISABLE Chapar** (currently broken — P0-05)
- [ ] Only `fixed_price` and `with_weight` methods should be active
- [ ] Verify no `chapar` type shipping methods are enabled

### Smart Notifications Toggle

- [ ] Login to admin panel → Settings → Smart Notifications
- [ ] Set `allow_open_requests` = OFF (default)
- [ ] Set `enable_ai` = OFF (default)
- [ ] These features are NOT implemented yet — keep OFF

### Festive Mode

- [ ] Login to admin panel → Settings → Appearance
- [ ] Set `festive_mode` = OFF (unless holiday)
- [ ] Set `festive_style` = `gold` (default)
- [ ] Set `festive_badge_text` = desired text

---

## 4️⃣ Smoke Test — Post-Deploy (Final Verification)

**Domain:** `https://shopora.ir` (or actual production URL)

### Test 1: Guest Cart → OTP Login → Cart Merge (P1-05 ✅)

- [ ] Open site as guest (incognito/private window)
- [ ] Add 2-3 products to cart
- [ ] Go to login page
- [ ] Enter phone number → receive OTP
- [ ] Enter OTP code → login
- [ ] **Verify:** Cart items preserved after login (merge worked)
- [ ] **Verify:** Wishlist items preserved (new dual-mode)

### Test 2: Checkout → Payment → Order Creation

- [ ] Login as test user
- [ ] Add product to cart
- [ ] Go to checkout
- [ ] Select address (or add new)
- [ ] Select shipping method
- [ ] Apply coupon code (if available)
- [ ] Select ZarinPal payment
- [ ] Complete payment in sandbox
- [ ] **Verify:** Redirect to success page
- [ ] **Verify:** Order status = `paid` in admin panel
- [ ] **Verify:** Stock decreased by ordered quantity
- [ ] **Verify:** `order.created` notification sent to admin

### Test 3: Admin Cancel Order → Stock Restore (P0-02 ✅)

- [ ] Login as admin
- [ ] Find a `paid` order in admin panel
- [ ] Cancel the order
- [ ] **Verify:** Stock restored (check product stock in admin)
- [ ] **Verify:** `OrderHistory` entry created
- [ ] **Verify:** No duplicate `restoreStock` in logs
- [ ] **Verify:** `order.cancelled` notification sent to user

### Test 4: SEO — JSON-LD Schema

- [ ] Go to any product page
- [ ] View Page Source (Ctrl+U)
- [ ] Search for `application/ld+json`
- [ ] **Verify:** JSON-LD block exists with `@type: Product`
- [ ] **Verify:** `name`, `image`, `offers.price`, `offers.availability` present
- [ ] **Verify:** `brand` present (if product has brand)

### Test 5: Error Log Check

- [ ] SSH into server
- [ ] Check `storage/logs/laravel.log`
- [ ] **Verify:** No new errors after deployment
- [ ] **Verify:** No `500` or `SQL` errors
- [ ] **Verify:** No `Duplicate entry` errors
- [ ] **Verify:** No `Class not found` errors

---

## 📋 Quick Reference Commands

```bash
# Cache everything
php artisan config:cache && php artisan route:cache && php artisan view:cache

# Run migrations
php artisan migrate --force

# Clear cache (if issues)
php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear

# Check cron
php artisan schedule:list

# Check logs
tail -f storage/logs/laravel.log

# Check routes
php artisan route:list --path=checkout

# Test SMS (manual)
php artisan tinker
> app(App\Services\OtpService::class)->send('09121234567');
```

---

## 🚨 Rollback Plan

If deployment fails:

1. **Restore database backup**
2. **Restore `.env` from backup**
3. **Clear all caches:** `php artisan cache:clear && php artisan config:clear`
4. **Revert git:** `git revert HEAD`
5. **Run migrations rollback:** `php artisan migrate:rollback`

---

## ✅ Final Sign-off

- [ ] All 4 sections above completed
- [ ] No critical errors in logs
- [ ] All smoke tests passed
- [ ] Admin panel accessible
- [ ] Payment gateway working
- [ ] SMS OTP working
- [ ] Site loads correctly on mobile and desktop

**Deployed by:** _________________
**Date/Time:** _________________
**Verified by:** _________________
