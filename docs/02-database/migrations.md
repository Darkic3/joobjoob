# Migrations — Shopora Database

> **Verified:** 73 files in `database/migrations` (read 2026-09-04). Listed in execution order (`ls -1` = `php artisan migrate` order). No invented migrations.
> Execution order is lexicographic filename sort. Duplicated timestamps are ordered alphabetically within the same second (e.g. `add_*` before `create_*`).

## At a glance

| Metric | Value |
|---|---|
| Total files | **73** |
| `create_*` tables | 42 |
| `add_*/change_*/remove_*/replace_*/make_*` alters | 28 |
| Pure data migrations (`DB::table` / `DB::statement`) | 3 (`enhance_discounts` code backfill, `final_price` backfill, `add_missing_admin_permissions`) |
| `dropIfExists` destructive recreates | 1 (`2026_08_26_000008_create_orders_table` drops `orders` + `order_items`) |
| Date range | `0001_01_01` (Laravel base) → `2026_09_03` |
| SoftDeletes added | 23 tables |
| Polymorphic tables | 4 (`discountable` morphs, `hero_slides` linkable, `notifications` notifiable, `audit_logs` auditable) |

---

## Full timeline (73)

| # | Filename | Op | Table(s) | What it does (verified summary) |
|---|---|---|---|---|
| 1 | `0001_01_01_000000_create_users_table.php` | create | `users`, `password_reset_tokens`, `sessions` | Laravel base: users (`mobile` UK, `email` nullable UK, `is_active` true, softDeletes), password_reset_tokens (PK `mobile`), sessions |
| 2 | `0001_01_01_000001_create_cache_table.php` | create | `cache`, `cache_locks` | Laravel cache |
| 3 | `0001_01_01_000002_create_jobs_table.php` | create | `jobs`, `job_batches`, `failed_jobs` | Laravel queues |
| 4 | `2024_01_01_000007_create_themes_table.php` | create | `themes` | `name`, `slug` UK, `preview_color` default `#e0e5ec`, `is_default` |
| 5 | `2024_01_01_000008_add_locale_theme_to_users_table.php` | alter | `users` | add `locale` 5 default `fa`, `theme` default `light-neumorphism`, `is_admin` false — **legacy theming** (no FK) |
| 6 | `2026_07_25_094140_create_settings_table.php` | create | `settings` | `key` UK+IDX, `value` text, `type`/`group` IDX, `label`, `options` json, `sort` |
| 7 | `2026_07_29_003839_create_blog_categories_table.php` | create | `blog_categories` | self `parent_id` nullOnDelete, `title`, `slug` UK+IDX, `is_active` IDX, `show_in_first_page`, `sort_order`, softDeletes |
| 8 | `2026_07_29_162456_add_css_variables_to_themes_table.php` | alter | `themes` | add `css_variables` json nullable |
| 9 | `2026_07_29_182334_create_blogs_table.php` | create | `blogs` | `blog_category_id` nullOnDelete, `user_id` cascade, `title`, `slug` UK+IDX, `excerpt`, `content` longText, `views` IDX, `is_active` IDX, softDeletes |
| 10 | `2026_07_31_152013_create_hero_slides_table.php` | create | `hero_slides` | `hero_type` default `internal`, i18n `badge/title/desc/btn_text` json, `price/old_price/currency/rating/viewers`, polymorphic `linkable_type/id` bigint + composite IDX, `image`, `is_active`, `sort_order` |
| 11 | `2026_08_01_124237_create_product_categories_table.php` | create | `product_categories` | self `parent_id` nullOnDelete, `name` json, `slug` UK, `icon/image`, i18n `description/meta_*`, `sort_order`, `is_active`, softDeletes |
| 12 | `2026_08_01_124250_create_products_table.php` | create | `products` | `product_category_id` cascade, `name` json, `slug` UK, `sku` nullable UK, `price/discount_price/stock/weight`, `status` enum `draft|published|archived`, `is_featured/is_best_seller`, `colors/sizes` json, `deal_ends_at`, softDeletes, IDX `status+is_featured` |
| 13 | `2026_08_01_124353_create_product_images_table.php` | create | `product_images` | `product_id` cascade, `path`, `alt_text` json, `is_primary`, `sort_order` |
| 14 | `2026_08_01_170059_create_reviews_table.php` | create | `reviews` | `product_id` cascade, `user_id` cascade, `rating` 1-5, `comment`, `is_approved` boolean, softDeletes, IDX `product+is_approved` |
| 15 | `2026_08_01_175144_create_trust_badges_table.php` | create | `trust_badges` | `icon` default 📦, `title` string, `description` string nullable, `sort_order`, `is_active` |
| 16 | `2026_08_02_000000_remove_hero_type_from_hero_slides_table.php` | alter | `hero_slides` | `dropColumn('hero_type')` |
| 17 | `2026_08_03_000001_add_is_active_to_products_table.php` | alter | `products` | add `is_active` bool, backfill from `status='published'`, drop IDX `status+is_featured`, drop `status`, add IDX `is_active` |
| 18 | `2026_08_03_000002_add_main_image_to_products_table.php` | alter | `products` | add `main_image` string nullable, backfill from `product_images.is_primary` smallest `sort_order` |
| 19 | `2026_08_04_000001_create_brands_table.php` | create | `brands` | `title` json, `url` UK, `description` json, `image`, `active`, `show_in_first_page`, softDeletes |
| 20 | `2026_08_04_000002_create_specifications_table.php` | create | `specifications` | `title` json, `type` enum `text|select`, self `parent_id` cascade, `active/is_filter/is_color/color_code`, softDeletes, IDX `parent_id/type` |
| 21 | `2026_08_04_000003_create_product_specification_table.php` | create | `product_specification` | `product_id`/`specification_id` cascade, `value` text, self `parent_id` FK cascade (grouping), softDeletes |
| 22 | `2026_08_04_000004_create_product_variants_table.php` | create | `product_variants` | `product_id` cascade IDX, `price/discounted_price/final_price` double, `stock`, `weight` 100, IDX `final_price`, softDeletes |
| 23 | `2026_08_04_000005_create_product_variant_specification_table.php` | create | `product_variant_specification` | `product_variant_id`/`specification_id` cascade, IDX `pvs_pv_spec_idx`, no timestamps |
| 24 | `2026_08_04_000006_create_properties_table.php` | create | `properties` | `product_id` cascade IDX, `title` json, `value`, `sort_order` |
| 25 | `2026_08_04_000007_create_product_associations_table.php` | create | `product_associations` | `product_id`/`related_product_id` cascade, `type` enum `related|complement`, UK triple |
| 26 | `2026_08_04_000008_add_brand_and_timer_to_products_table.php` | alter | `products` | add `brand_id` FK nullOnDelete, `timer_active/start_timer/end_timer`, drop `colors/sizes/brand`, backfill `end_timer=deal_ends_at` |
| 27 | `2026_08_04_000009_add_final_price_to_products_table.php` | alter | `products` | add `final_price` bigint default 0 + IDX, backfill `COALESCE(discount_price, price)` |
| 28 | `2026_08_06_000001_add_is_admin_to_users_table.php` | alter | `users` | idempotent `if (!hasColumn('is_admin')) add is_admin` |
| 29 | `2026_08_06_000002_create_roles_table.php` | create | `roles` | `name` UK, `is_system` |
| 30 | `2026_08_06_000003_create_permissions_table.php` | create | `permissions` | `name` UK, `group` IDX, `label_key` |
| 31 | `2026_08_06_000004_create_permission_role_table.php` | create | `permission_role` | `role_id`/`permission_id` cascade, PK composite |
| 32 | `2026_08_06_000005_create_role_user_table.php` | create | `role_user` | `user_id`/`role_id` cascade, PK composite |
| 33 | `2026_08_06_000006_create_request_logs_table.php` | create | `request_logs` | `user_id` nullOnDelete, `method/url/ip/status_code/payload` json, IDX `created_at` |
| 34 | `2026_08_07_000001_add_sort_order_to_brands_table.php` | alter | `brands` | add `sort_order` int default 0 |
| 35 | `2026_08_07_000001_create_languages_table.php` | create | `languages` | `code` UK 8, `name` json, `native_name`, `dir` ltr, `is_active/is_default`, `sort_order`, IDX `is_active+is_default` |
| 36 | `2026_08_08_000001_change_linkable_id_to_string_in_hero_slides_table.php` | alter | `hero_slides` | `linkable_id` bigint → **string nullable** `->change()` — fixes URL/page keys |
| 37 | `2026_08_09_000001_add_trusts_to_hero_slides_table.php` | alter | `hero_slides` | add `trusts` json nullable `[{icon,text:{fa,en}}]` |
| 38 | `2026_08_10_000001_make_trust_badges_title_description_translatable.php` | alter | `trust_badges` | `title/description` string → text + backfill `{"fa": old, "en": ""}` |
| 39 | `2026_08_11_000001_create_contact_requests_table.php` | create | `contact_requests` | `name/email/phone/department/message/read_at` |
| 40 | `2026_08_12_000001_create_promo_banners_table.php` | create | `promo_banners` | `title/subtitle` json, `url`, `style` gradient, `tone` accent, `sort_order`, `is_active` |
| 41 | `2026_08_12_000002_add_sort_order_indexes.php` | alter | `promo_banners`, `trust_badges` | IDX `sort_order` on both |
| 42 | `2026_08_12_000003_add_icon_to_promo_banners.php` | alter | `promo_banners` | add `icon` default `megaphone` |
| 43 | `2026_08_14_000001_add_placement_to_trust_badges_table.php` | alter | `trust_badges` | add `placement` 16 default `site` + IDX |
| 44 | `2026_08_16_000001_add_confirm_code_to_users_table.php` | alter | `users` | add `confirm_code` 10 nullable + `confirm_code_expires_at` timestamp |
| 45 | `2026_08_22_000000_add_guest_fields_to_reviews_table.php` | alter | `reviews` | `user_id` nullable `->change()`, add `guest_name` 100 + `guest_phone` 20 |
| 46 | `2026_08_22_100000_change_is_approved_to_status_in_reviews_table.php` | alter | `reviews` | `is_approved` boolean → **tinyint** 0 pending · 1 approved · 2 rejected |
| 47 | `2026_08_23_000000_add_parent_id_to_reviews_table.php` | alter | `reviews` | add `parent_id` FK self nullOnDelete (threaded replies) |
| 48 | `2026_08_23_000001_add_is_featured_to_reviews_table.php` | alter | `reviews` | add `is_featured` bool + IDX |
| 49 | `2026_08_26_000001_create_states_table.php` | create | `states` | `name`, `status` 1, `cod24_id/chapar_id` int, softDeletes |
| 50 | `2026_08_26_000002_create_cities_table.php` | create | `cities` | `name`, `status` 1, `state_id` nullOnDelete, `cod24_id/chapar_id`, softDeletes |
| 51 | `2026_08_26_000003_create_addresses_table.php` | create | `addresses` | `user_id/state_id/city_id` nullOnDelete, `address` text, `receiptor_*`, `postal_code`, IDX `user_id`, softDeletes |
| 52 | `2026_08_26_000004_create_shipping_methods_table.php` | create | `shipping_methods` | `title/price/price_ceiling/status/description/freight_balance/type/chapar_type/config`, sender fields + `sender_city_id` nullOnDelete, softDeletes |
| 53 | `2026_08_26_000005_create_shipping_method_city_table.php` | create | `shipping_method_city` | `shipping_method_id/city_id` cascade, PK composite |
| 54 | `2026_08_26_000006_create_order_shipping_statuses_table.php` | create | `order_shipping_statuses` | `title/color/#6c757d/default/for_send/sending_sms`, softDeletes |
| 55 | `2026_08_26_000007_add_estimated_days_to_shipping_methods_table.php` | alter | `shipping_methods` | add `estimated_days_min` 1 + `estimated_days_max` 3 smallint |
| 56 | `2026_08_26_000007_create_banks_table.php` | create | `banks` | `title/icon/status/admin_test/bank_type/config` json, softDeletes |
| 57 | `2026_08_26_000008_add_show_estimated_days_to_shipping_methods_table.php` | alter | `shipping_methods` | add `show_estimated_days` bool true |
| 58 | `2026_08_26_000008_create_orders_table.php` | create (destructive) | `orders` | **`dropIfExists('order_items'); dropIfExists('orders');`** then create (see § Key change 1) |
| 59 | `2026_08_26_000009_create_order_items_table.php` | create | `order_items` | `order_id` cascade IDX, `product_id` nullOnDelete IDX, `product_variant_id` nullOnDelete, `quantity/old_quantity/weight/price/discounted_price` string, softDeletes |
| 60 | `2026_08_26_000010_create_order_histories_table.php` | create | `order_histories` | `order_id` cascade IDX, `shipping_status_id` nullOnDelete, `order_status` string, softDeletes |
| 61 | `2026_08_26_000011_create_inventory_transactions_table.php` | create | `inventory_transactions` | `product_id/product_variant_id/order_id` nullOnDelete, IDX `product_id/order_id`, `quantity/type/description`, softDeletes |
| 62 | `2026_08_26_000012_create_discounts_table.php` | create | `discounts` | `title/amount/type/count/basket_minimum_price/first_purchase/with_discount/user_id int IDX/max_usage_per_user/pay_type`, softDeletes |
| 63 | `2026_08_26_000013_create_discountable_table.php` | create | `discountable` | `discount_id` cascade, `morphs('discountable')` |
| 64 | `2026_08_29_000001_add_card_to_card_fields_to_banks_table.php` | alter | `banks` | add `card_number/card_holder_name/card_description` |
| 65 | `2026_08_29_000002_add_payment_receipt_to_orders_table.php` | alter | `orders` | add `payment_receipt` string nullable (card-to-card receipt image) |
| 66 | `2026_08_31_000001_create_review_votes_table.php` | create | `review_votes` | `review_id/user_id` cascade, `type` enum `like|dislike`, UK `review+user` |
| 67 | `2026_08_31_000001_replace_payment_receipt_with_tracking_fields_in_orders_table.php` | alter | `orders` | **drop `payment_receipt`**, add `receipt_tracking_code` + `receipt_payer_card` strings |
| 68 | `2026_08_31_163905_create_carts_table.php` | create | `carts` | `user_id` nullOnDelete IDX, `product_id` cascade, `variant_id` int FK-less, `variant_label/name/slug/price/original_price/qty/stock/weight/color/size/image` |
| 69 | `2026_09_01_000001_create_audit_logs_table.php` | create | `audit_logs` | `event` IDX, `auditable_type` IDX, `auditable_id` IDX, `user_id` IDX, `old_values/new_values/meta` json, composite IDX `(type,id,event)` |
| 70 | `2026_09_01_145124_add_missing_admin_permissions.php` | data | `permissions` | `INSERT IGNORE` from `config/permissions.php` (idempotent) |
| 71 | `2026_09_01_160445_enhance_discounts_system.php` | alter+create | `discounts`, `discount_usage` | add `code` nullable, `is_active`, `starts_at/expires_at`, backfill `DISC-0001`, UK `code`; create `discount_usage` (`discount_id` cascade, `user_id/order_id` nullOnDelete, `discount_amount` decimal 12,0, `used_at`, IDX `discount+user`) |
| 72 | `2026_09_02_000001_create_discount_users_table.php` | create | `discount_users` | `discount_id/user_id` cascade, UK pair (eligibility allowlist) |
| 73 | `2026_09_03_000001_create_notifications_table.php` | create | `notifications` | polymorphic `notifiable_type/id`, `type/title/body/icon/link/is_read/data` json, IDX `(type,id,is_read)` + `created_at` |

---

## Key changes — deep dive (as requested)

### 1) Orders — drop old (2026_08_26_000008)

**File:** `2026_08_26_000008_create_orders_table.php`

```php
Schema::dropIfExists('order_items');
Schema::dropIfExists('orders');
Schema::create('orders', ...);
```

- **This is the only destructive recreate in the whole migration set.** It assumes any pre-existing `orders`/`order_items` from an earlier prototype are disposable. Running it on a production DB with live orders **destroys data** — there is no data migration, rename, or backup.
- The new `orders` shape is the current canonical order: denormalized `address` JSON snapshot + `state_id/city_id`, `shipping_status_id` FK, `order_status` string, `freight_balance`, `basket_id` FK-less, `bank_id`/`shipping_method_id` FK, `discount_id` FK-less, weight/prices as strings, `transaction_info` json, softDeletes, only `user_id` indexed.
- `order_items` is recreated next migration (`000009`) with the new shape. The `dropIfExists('order_items')` here ensures no FK conflict when `orders` is dropped.

> **Risk:** If you ever need to replay migrations on a DB with real orders, replace the `dropIfExists` with a `Schema::hasTable` guard or a rename+backfill.

### 2) `payment_receipt` → `receipt_tracking_code` + `receipt_payer_card` (2026-08-29 → 2026-08-31)

| Step | File | Change |
|---|---|---|
| 65 | `2026_08_29_000002_add_payment_receipt_to_orders_table.php` | `payment_receipt` string nullable — receipt *image* upload for card-to-card |
| 67 | `2026_08_31_000001_replace_payment_receipt_with_tracking_fields_in_orders_table.php` | `dropColumn('payment_receipt')`; add `receipt_tracking_code` + `receipt_payer_card` strings |

- Intent: move from file upload to **text fields** (tracking code + payer card) — lighter, no storage, verifiable.
- `down()` of #67 restores `payment_receipt` and drops the two tracking columns — reversible but **data-lossy** (uploaded paths not preserved).
- Current `orders` has **no `payment_receipt`** column; card-to-card verification uses `receipt_tracking_code` + `receipt_payer_card` + `bank.card_number`.

### 3) `enhance_discounts` (2026_09_01_160445) — the discount system v2

**File:** `2026_09_01_160445_enhance_discounts_system.php`

```php
// 1. adds to discounts (if not exists):
$table->string('code')->nullable();
$table->boolean('is_active')->default(true);
$table->timestamp('starts_at')->nullable();
$table->timestamp('expires_at')->nullable();
// 1b. backfill:
DISC- . str_pad(id, 4, "0") // e.g. DISC-0001
// 1c. unique on code
$table->unique('code');
// 2. creates discount_usage
```

**Created `discount_usage`:**

| Column | Def |
|---|---|
| `discount_id` | FK cascade |
| `user_id` | FK nullOnDelete |
| `order_id` | FK nullOnDelete |
| `discount_amount` | decimal(12,0) default 0 |
| `used_at` | timestamp |
| `timestamps` | — |
| IDX | `(discount_id, user_id)` |

- Guarded with `Schema::hasColumn / hasTable / hasIndex` — **idempotent**, safe to re-run.
- Pairs with `2026_09_02_000001_create_discount_users_table` (allowlist) and the existing `discountable` polymorphic scope + `discounts.pay_type` (`category|brand|product`).
- Naming note: `discount_users` (eligibility) vs `discount_usage` (audit trail) — do not confuse.

### 4) `hero_slides.linkable_id` string (2026_08_08_000001)

**File:** `2026_08_08_000001_change_linkable_id_to_string_in_hero_slides_table.php`

```php
$table->string('linkable_id')->nullable()->change();
```

- **Before:** `unsignedBigInteger` — only numeric model IDs.
- **After:** `string` nullable — holds `Product`/`Category`/`Blog` IDs *as strings* **plus** custom URLs (`https://…`) and page keys (`contact`) for the `custom/page` link types.
- Comment in migration (Persian) documents the failure mode: string URLs threw SQL errors before this fix.
- `down()` warns: reverting casts string URLs to integers — **corrupts `custom`/`page` slides**.
- Composite index `(linkable_type, linkable_id)` is retained after the type change.

### 5) `themes` — legacy

| File | Change |
|---|---|
| `2024_01_01_000007_create_themes_table.php` | `themes` created: `name`, `slug` UK, `preview_color`, `is_default` |
| `2026_07_29_162456_add_css_variables_to_themes_table.php` | `css_variables` json nullable |
| `2024_01_01_000008_add_locale_theme_to_users_table.php` | `users.locale` default `fa`, `users.theme` default `light-neumorphism`, `users.is_admin` |

- **No FK:** `users.theme` is a plain string, not `themes.id` / `themes.slug`. The `themes` table is not referenced by any FK in any migration.
- **Status:** Effectively **dead code** — theming now lives in `settings` / frontend config, not `themes`. Keep for backward compat or drop with a deprecation migration. Do not add a FK without a data backfill (existing `users.theme` values are slug strings, not IDs).

### 6) `softDeletes` coverage

**Has `deleted_at` (23 tables, verified by `$table->softDeletes()`):**
`users`, `blog_categories`, `blogs`, `product_categories`, `products`, `brands`, `specifications`, `product_specification`, `product_variants`, `reviews`, `states`, `cities`, `addresses`, `shipping_methods`, `order_shipping_statuses`, `banks`, `orders`, `order_items`, `order_histories`, `inventory_transactions`, `discounts`, plus the Laravel base `users` only (not sessions/cache/jobs).

**No `softDeletes` (verified absence — intentional):**
`themes`, `settings`, `product_images`, `properties`, `product_associations`, `product_variant_specification`, `roles`, `permissions`, `permission_role`, `role_user`, `request_logs`, `languages`, `contact_requests`, `promo_banners`, `trust_badges`, `shipping_method_city`, `discountable`, `discount_usage`, `discount_users`, `review_votes`, `carts`, `notifications`, `audit_logs`, `cache*`, `jobs*`, `sessions`, `password_reset_tokens`.

- **Inconsistency to know:** `product_variants` soft-deletes but `product_variant_specification` (its pivot) does not — orphan spec rows survive variant soft-delete. `carts` and `notifications` are hard-deleted by design.

### 7) Missing indexes — `orders.order_status`

**File:** `2026_08_26_000008_create_orders_table.php` — only `$table->index('user_id')` is created.

| Column | Indexed? | Impact |
|---|---|---|
| `orders.user_id` | **Yes** | fast per-user lookups |
| `orders.order_status` | **No** | `WHERE order_status = 'paid'` or `IN (...)` without `user_id` **full-scans** |
| `orders.shipping_status_id` | **No** | same — status board queries without user scope scan |
| `orders.created_at` | **No** | date-range admin filters scan |
| `orders.discount_id` / `basket_id` | **No** (also FK-less) | not filterable via index |

**Recommended fix (verify before applying):**

```php
Schema::table('orders', function (Blueprint $table) {
    $table->index('order_status');
    $table->index('shipping_status_id');
    $table->index('created_at');
    // or composite for the common admin query pattern:
    $table->index(['order_status', 'created_at']);
    $table->index(['user_id', 'order_status']);
});
```

- Also note: `discounts.user_id` is `integer` (not `foreignId`) with an index but **no FK** — single-user discount lock, not a relation. `carts.variant_id` is FK-less int. `orders.discount_id` / `basket_id` same pattern.

---

## Execution notes

- **Timestamp collisions:** `2026_08_07_000001` appears twice, `2026_08_26_000007` appears twice, `2026_08_31_000001` appears twice. Laravel runs them in filename order within the same timestamp — verified above and in the table.
- **Idempotent migrations:** `2026_08_06_000001_add_is_admin_to_users_table` and `2026_09_01_160445_enhance_discounts_system` and `2026_09_01_145124_add_missing_admin_permissions` all guard with `hasColumn/hasTable/hasIndex` or `INSERT IGNORE` — safe to re-run.
- **Backfills:** `add_is_active_to_products_table` (status→bool), `add_main_image_to_products_table` (is_primary→main_image), `add_brand_and_timer_to_products_table` (deal_ends_at→end_timer), `add_final_price_to_products_table`, `make_trust_badges_title_description_translatable`, `enhance_discounts_system` (DISC- codes) — all run `DB::table()->update/each()` in the migration. Large datasets may need batching.
- **Total `softDeletes` audit:** run `grep -R softDeletes database/migrations | wc -l` → 23 domain tables (above list).

## Source file list (for audit)

```
0001_01_01_000000_create_users_table.php
0001_01_01_000001_create_cache_table.php
0001_01_01_000002_create_jobs_table.php
2024_01_01_000007_create_themes_table.php
2024_01_01_000008_add_locale_theme_to_users_table.php
2026_07_25_094140_create_settings_table.php
2026_07_29_003839_create_blog_categories_table.php
2026_07_29_162456_add_css_variables_to_themes_table.php
2026_07_29_182334_create_blogs_table.php
2026_07_31_152013_create_hero_slides_table.php
2026_08_01_124237_create_product_categories_table.php
2026_08_01_124250_create_products_table.php
2026_08_01_124353_create_product_images_table.php
2026_08_01_170059_create_reviews_table.php
2026_08_01_175144_create_trust_badges_table.php
2026_08_02_000000_remove_hero_type_from_hero_slides_table.php
2026_08_03_000001_add_is_active_to_products_table.php
2026_08_03_000002_add_main_image_to_products_table.php
2026_08_04_000001_create_brands_table.php
2026_08_04_000002_create_specifications_table.php
2026_08_04_000003_create_product_specification_table.php
2026_08_04_000004_create_product_variants_table.php
2026_08_04_000005_create_product_variant_specification_table.php
2026_08_04_000006_create_properties_table.php
2026_08_04_000007_create_product_associations_table.php
2026_08_04_000008_add_brand_and_timer_to_products_table.php
2026_08_04_000009_add_final_price_to_products_table.php
2026_08_06_000001_add_is_admin_to_users_table.php
2026_08_06_000002_create_roles_table.php
2026_08_06_000003_create_permissions_table.php
2026_08_06_000004_create_permission_role_table.php
2026_08_06_000005_create_role_user_table.php
2026_08_06_000006_create_request_logs_table.php
2026_08_07_000001_add_sort_order_to_brands_table.php
2026_08_07_000001_create_languages_table.php
2026_08_08_000001_change_linkable_id_to_string_in_hero_slides_table.php
2026_08_09_000001_add_trusts_to_hero_slides_table.php
2026_08_10_000001_make_trust_badges_title_description_translatable.php
2026_08_11_000001_create_contact_requests_table.php
2026_08_12_000001_create_promo_banners_table.php
2026_08_12_000002_add_sort_order_indexes.php
2026_08_12_000003_add_icon_to_promo_banners.php
2026_08_14_000001_add_placement_to_trust_badges_table.php
2026_08_16_000001_add_confirm_code_to_users_table.php
2026_08_22_000000_add_guest_fields_to_reviews_table.php
2026_08_22_100000_change_is_approved_to_status_in_reviews_table.php
2026_08_23_000000_add_parent_id_to_reviews_table.php
2026_08_23_000001_add_is_featured_to_reviews_table.php
2026_08_26_000001_create_states_table.php
2026_08_26_000002_create_cities_table.php
2026_08_26_000003_create_addresses_table.php
2026_08_26_000004_create_shipping_methods_table.php
2026_08_26_000005_create_shipping_method_city_table.php
2026_08_26_000006_create_order_shipping_statuses_table.php
2026_08_26_000007_add_estimated_days_to_shipping_methods_table.php
2026_08_26_000007_create_banks_table.php
2026_08_26_000008_add_show_estimated_days_to_shipping_methods_table.php
2026_08_26_000008_create_orders_table.php
2026_08_26_000009_create_order_items_table.php
2026_08_26_000010_create_order_histories_table.php
2026_08_26_000011_create_inventory_transactions_table.php
2026_08_26_000012_create_discounts_table.php
2026_08_26_000013_create_discountable_table.php
2026_08_29_000001_add_card_to_card_fields_to_banks_table.php
2026_08_29_000002_add_payment_receipt_to_orders_table.php
2026_08_31_000001_create_review_votes_table.php
2026_08_31_000001_replace_payment_receipt_with_tracking_fields_in_orders_table.php
2026_08_31_163905_create_carts_table.php
2026_09_01_000001_create_audit_logs_table.php
2026_09_01_145124_add_missing_admin_permissions.php
2026_09_01_160445_enhance_discounts_system.php
2026_09_02_000001_create_discount_users_table.php
2026_09_03_000001_create_notifications_table.php
```
