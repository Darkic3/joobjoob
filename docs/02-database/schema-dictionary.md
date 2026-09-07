# Schema Dictionary — Shopora Database

> Source of truth: `database/migrations` — 73 files read verbatim 2026-09-04. No inference.
> Conventions: Type = Blueprint method, Index = index/unique/primary, FK = constrained/foreignId, SoftDeletes = softDeletes.

## Legend

- PK = primary key (bigint auto-increment), UK = unique, FK = foreign key, IDX = index.
- json columns store i18n `{fa,en}` or config; softDeletes adds nullable `deleted_at`.
- `FK … nullOnDelete` survives parent deletion; `cascadeOnDelete` removes children.
- Columns marked **no FK** intentionally have no constraint (verified by absence of `constrained()`).

---

## 1. users

| Column | Type | Nullable | Default | Index | FK | Notes |
|---|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — | |
| name | string | Yes | — | — | — | |
| mobile | string | No | — | UK | — | unique, OTP login |
| email | string | Yes | — | UK (nullable) | — | |
| locale | string(5) | No | `fa` | — | — | added 2024-01-01_000008 |
| theme | string | No | `light-neumorphism` | — | — | **no FK** to themes (legacy) |
| is_admin | boolean | No | false | — | — | idempotent add 2026-08-06 |
| is_active | boolean | No | true | — | — | |
| password | string | No | — | — | — | |
| avatar | string | Yes | — | — | — | |
| confirm_code | string(10) | Yes | — | — | — | OTP |
| confirm_code_expires_at | timestamp | Yes | — | — | — | |
| email_verified_at | timestamp | Yes | — | — | — | |
| remember_token | string(100) | Yes | — | — | — | |
| deleted_at | timestamp | Yes | — | — | — | **SoftDeletes** |
| created_at / updated_at | timestamp | Yes | — | — | — | |

## 2. password_reset_tokens

| Column | Type | Nullable | Default | Index | FK |
|---|---|---|---|---|---|
| mobile | string PK | No | — | PK | — |
| token | string | No | — | — | — |
| created_at | timestamp | Yes | — | — | — |

No SoftDeletes.

## 3. sessions

| Column | Type | Nullable | Index | FK |
|---|---|---|---|---|
| id | string PK | No | PK | — |
| user_id | bigint FK | Yes | IDX | users.id (index only, nullable) |
| ip_address | string(45) | Yes | — | — |
| user_agent | text | Yes | — | — |
| payload | longText | No | — | — |
| last_activity | integer | No | IDX | — |

No SoftDeletes.

## 4. cache / cache_locks

| Table | Column | Type | Index |
|---|---|---|---|
| cache | key PK string | — | PK |
| cache | value mediumText | — | — |
| cache | expiration integer | — | — |
| cache_locks | key PK string | — | PK |
| cache_locks | owner string | — | — |
| cache_locks | expiration integer | — | — |

## 5. jobs / job_batches / failed_jobs

| Table | Column | Type | Notes |
|---|---|---|---|
| jobs | id bigint PK | auto | queue IDX, attempts tinyint, reserved/available/created int |
| job_batches | id string PK | — | total/pending/failed int, failed_job_ids longText |
| failed_jobs | id bigint PK | auto | uuid UK, failed_at timestamp useCurrent |

## 6. themes — Legacy

| Column | Type | Nullable | Default | Index | FK | Notes |
|---|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — | |
| name | string | No | — | — | — | |
| slug | string | No | — | UK | — | |
| preview_color | string | No | `#e0e5ec` | — | — | |
| css_variables | json | Yes | — | — | — | added 2026-07-29 |
| is_default | boolean | No | false | — | — | |
| created_at / updated_at | timestamp | — | — | — | — | |

No SoftDeletes. **No FK from users.theme** — plain string, legacy.

## 7. settings

| Column | Type | Nullable | Default | Index | FK |
|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — |
| key | string | No | — | UK + IDX | — |
| value | text | Yes | — | — | — |
| type | string | No | `text` | — | — |
| group | string | No | `general` | IDX | — |
| label | string | Yes | — | — | — |
| options | json | Yes | — | — | — |
| sort | integer | No | 0 | — | — |

No SoftDeletes.

## 8. blog_categories

| Column | Type | Nullable | Default | Index | FK |
|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — |
| parent_id | bigint FK | Yes | — | — | blog_categories.id nullOnDelete (self) |
| title | string | No | — | — | — |
| slug | string | No | — | UK + IDX | — |
| description | text | Yes | — | — | — |
| image | string | Yes | — | — | — |
| is_active | boolean | No | true | IDX | — |
| show_in_first_page | boolean | No | false | — | — |
| sort_order | smallint | No | 0 | — | — |
| deleted_at | timestamp | Yes | — | — | **SoftDeletes** |

## 9. blogs

| Column | Type | Nullable | Default | Index | FK |
|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — |
| blog_category_id | bigint FK | Yes | — | — | blog_categories.id nullOnDelete |
| user_id | bigint FK | Yes | — | — | users.id cascadeOnDelete |
| title | string | No | — | — | — |
| slug | string | No | — | UK + IDX | — |
| excerpt | text | Yes | — | — | — |
| content | longText | No | — | — | — |
| image | string | Yes | — | — | — |
| views | bigint | No | 0 | IDX | — |
| is_active | boolean | No | true | IDX | — |
| show_in_first_page | boolean | No | false | — | — |
| deleted_at | timestamp | Yes | — | — | **SoftDeletes** |

## 10. hero_slides

| Column | Type | Nullable | Default | Index | FK | Notes |
|---|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — | |
| badge | json | Yes | — | — | — | i18n |
| title | json | No | — | — | — | i18n |
| desc | json | Yes | — | — | — | i18n |
| btn_text | json | Yes | — | — | — | i18n |
| price | bigint | No | 0 | — | — | |
| old_price | bigint | Yes | — | — | — | strikethrough |
| currency | string(10) | No | `IRT` | — | — | |
| rating | string | Yes | — | — | — | e.g. 4.8 |
| viewers | integer | No | 0 | — | — | |
| trusts | json | Yes | — | — | — | added 2026-08-09: [{icon,text:{fa,en}}] |
| linkable_type | string | Yes | — | composite | — | polymorphic |
| linkable_id | string | Yes | — | composite | — | **bigint -> string 2026-08-08** |
| image | string | Yes | — | — | — | |
| is_active | boolean | No | true | — | — | |
| sort_order | integer | No | 0 | — | — | |

No SoftDeletes. Original hero_type dropped 2026-08-02. Composite index (linkable_type, linkable_id).

## 11. product_categories

| Column | Type | Nullable | Default | Index | FK |
|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — |
| parent_id | bigint FK | Yes | — | — | product_categories.id nullOnDelete (self) |
| name | json | No | — | — | — |
| slug | string | No | — | UK | — |
| icon | string | Yes | — | — | — |
| image | string | Yes | — | — | — |
| description | json | Yes | — | — | — |
| meta_title | json | Yes | — | — | — |
| meta_description | json | Yes | — | — | — |
| sort_order | smallint | No | 0 | — | — |
| is_active | boolean | No | true | — | — |
| deleted_at | timestamp | Yes | — | — | **SoftDeletes** |

## 12. products

| Column | Type | Nullable | Default | Index | FK | Notes |
|---|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — | |
| product_category_id | bigint FK | No | — | IDX | product_categories.id cascade | |
| brand_id | bigint FK | Yes | — | — | brands.id nullOnDelete | added 2026-08-04 |
| name | json | No | — | — | — | |
| slug | string | No | — | UK | — | |
| short_description | json | Yes | — | — | — | |
| description | json | Yes | — | — | — | |
| sku | string | Yes | — | UK (nullable) | — | |
| price | bigint | No | — | — | — | |
| discount_price | bigint | Yes | — | — | — | |
| final_price | bigint | No | 0 | IDX | — | backfilled COALESCE |
| stock | integer | No | 0 | — | — | |
| weight | integer | Yes | — | — | — | grams |
| main_image | string | Yes | — | — | — | backfilled from product_images.is_primary |
| is_active | boolean | No | true | IDX | — | replaces status enum 2026-08-03 |
| is_featured | boolean | No | false | — | — | |
| is_best_seller | boolean | No | false | — | — | |
| deal_ends_at | timestamp | Yes | — | — | — | legacy, migrated to end_timer |
| timer_active | boolean | No | false | — | — | |
| start_timer | timestamp | Yes | — | — | — | |
| end_timer | timestamp | Yes | — | — | — | |
| deleted_at | timestamp | Yes | — | — | — | **SoftDeletes** |

Removed: status enum (draft|published|archived), colors/sizes json, brand string.

## 13. product_images

| Column | Type | Nullable | Default | Index | FK |
|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — |
| product_id | bigint FK | No | — | — | products.id cascade |
| path | string | No | — | — | — |
| alt_text | json | Yes | — | — | — |
| is_primary | boolean | No | false | — | — |
| sort_order | smallint | No | 0 | — | — |

No SoftDeletes.

## 14. brands

| Column | Type | Nullable | Default | Index | FK |
|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — |
| title | json | No | — | — | — |
| url | string | No | — | UK | — |
| description | json | Yes | — | — | — |
| image | string | Yes | — | — | — |
| sort_order | integer | No | 0 | — | — |
| active | boolean | No | true | — | — |
| show_in_first_page | boolean | No | false | — | — |
| deleted_at | timestamp | Yes | — | — | **SoftDeletes** |

## 15. specifications

| Column | Type | Nullable | Default | Index | FK |
|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — |
| title | json | No | — | — | — |
| type | enum(text,select) | No | `text` | IDX | — |
| parent_id | bigint FK | Yes | — | IDX | specifications.id cascade (self) |
| active | boolean | No | true | — | — |
| is_filter | boolean | No | false | — | — |
| is_color | boolean | No | false | — | — |
| color_code | string | Yes | — | — | — |
| deleted_at | timestamp | Yes | — | — | **SoftDeletes** |

## 16. product_specification (pivot with metadata)

| Column | Type | Nullable | Default | Index | FK |
|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — |
| product_id | bigint FK | No | — | IDX(product+spec) | products.id cascade |
| specification_id | bigint FK | No | — | IDX(product+spec) | specifications.id cascade |
| value | text | Yes | — | — | — |
| parent_id | bigint FK | Yes | — | IDX | product_specification.id cascade (self) |
| deleted_at | timestamp | Yes | — | — | **SoftDeletes** |

## 17. product_variants

| Column | Type | Nullable | Default | Index | FK |
|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — |
| product_id | bigint FK | No | — | IDX | products.id cascade |
| price | double | No | 0 | — | — |
| discounted_price | double | No | 0 | — | — |
| final_price | double | No | 0 | IDX | — |
| stock | integer | No | 0 | — | — |
| weight | double | No | 100 | — | — |
| deleted_at | timestamp | Yes | — | — | **SoftDeletes** |

## 18. product_variant_specification

| Column | Type | Nullable | Index | FK |
|---|---|---|---|---|
| id | bigint PK | No | PK | — |
| product_variant_id | bigint FK | No | IDX composite pvs_pv_spec_idx | product_variants.id cascade |
| specification_id | bigint FK | No | IDX composite | specifications.id cascade |

No timestamps, no SoftDeletes.

## 19. properties

| Column | Type | Nullable | Default | Index | FK |
|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — |
| product_id | bigint FK | No | — | IDX | products.id cascade |
| title | json | Yes | — | — | — |
| value | string | No | — | — | — |
| sort_order | integer | No | 0 | — | — |

No SoftDeletes.

## 20. product_associations

| Column | Type | Nullable | Default | Index | FK |
|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — |
| product_id | bigint FK | No | — | UK(product+related+type) | products.id cascade |
| related_product_id | bigint FK | No | — | UK | products.id cascade |
| type | enum(related,complement) | No | `related` | UK | — |

No SoftDeletes.

## 21. reviews

| Column | Type | Nullable | Default | Index | FK | Notes |
|---|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — | |
| product_id | bigint FK | No | — | IDX(product+approved) | products.id cascade | |
| user_id | bigint FK | Yes | — | — | users.id cascade | **nullable since 2026-08-22** (guests) |
| parent_id | bigint FK | Yes | — | — | reviews.id nullOnDelete | threading |
| rating | tinyint | No | — | — | — | 1-5 |
| comment | text | Yes | — | — | — | |
| is_approved | tinyint | No | 0 | IDX(product+approved) | — | 0 pending 1 approved 2 rejected (was boolean) |
| is_featured | boolean | No | false | IDX | — | |
| guest_name | string(100) | Yes | — | — | — | |
| guest_phone | string(20) | Yes | — | — | — | |
| deleted_at | timestamp | Yes | — | — | — | **SoftDeletes** |

## 22. review_votes

| Column | Type | Nullable | Index | FK |
|---|---|---|---|---|
| id | bigint PK | No | PK | — |
| review_id | bigint FK | No | UK(review+user) | reviews.id cascade |
| user_id | bigint FK | No | UK | users.id cascade |
| type | enum(like,dislike) | No | — | — |

No SoftDeletes.

## 23. trust_badges

| Column | Type | Nullable | Default | Index | FK |
|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — |
| icon | string | No | 📦 | — | — |
| title | text | No | — | — | — | was string, changed to text + JSON backfill |
| description | text | Yes | — | — | — |
| sort_order | integer | No | 0 | IDX | — | |
| is_active | boolean | No | true | — | — | |
| placement | string(16) | No | `site` | IDX | — | site|footer|both |

No SoftDeletes.

## 24. languages

| Column | Type | Nullable | Default | Index | FK |
|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — |
| code | string(8) | No | — | UK | — |
| name | json | No | — | — | — |
| native_name | string | No | — | — | — |
| dir | string(3) | No | `ltr` | — | — |
| is_active | boolean | No | true | IDX(active+default) | — |
| is_default | boolean | No | false | IDX | — |
| sort_order | smallint | No | 0 | — | — |

No SoftDeletes.

## 25. contact_requests

| Column | Type | Nullable | Index | FK |
|---|---|---|---|---|
| id | bigint PK | No | PK | — |
| name | string | No | — | — |
| email | string | No | — | — |
| phone | string(40) | Yes | — | — |
| department | string(40) | Yes | — | — |
| message | text | No | — | — |
| read_at | timestamp | Yes | — | — |

No SoftDeletes.

## 26. promo_banners

| Column | Type | Nullable | Default | Index | FK |
|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — |
| title | json | No | — | — | — |
| subtitle | json | Yes | — | — | — |
| url | string | Yes | — | — | — |
| style | string | No | `gradient` | — | — |
| tone | string | No | `accent` | — | — |
| icon | string | No | `megaphone` | — | — |
| sort_order | integer | No | 0 | IDX | — |
| is_active | boolean | No | true | — | — |

No SoftDeletes.

## 27. states

| Column | Type | Nullable | Default | FK | SoftDeletes |
|---|---|---|---|---|---|
| id | bigint PK | No | auto | — | **Yes** |
| name | string | Yes | — | — | |
| status | tinyint | No | 1 | — | |
| cod24_id | integer | Yes | — | — | Cod24 API |
| chapar_id | integer | Yes | — | — | Chapar API |

## 28. cities

| Column | Type | Nullable | Default | FK | SoftDeletes |
|---|---|---|---|---|---|
| id | bigint PK | No | auto | — | **Yes** |
| name | string | Yes | — | — | |
| status | tinyint | No | 1 | — | |
| state_id | bigint FK | Yes | — | states.id nullOnDelete | |
| cod24_id | integer | Yes | — | — | |
| chapar_id | integer | Yes | — | — | |

## 29. addresses

| Column | Type | Nullable | Default | Index | FK | SoftDeletes |
|---|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — | **Yes** |
| user_id | bigint FK | Yes | — | IDX | users.id nullOnDelete | |
| state_id | bigint FK | Yes | — | — | states.id nullOnDelete | |
| city_id | bigint FK | Yes | — | — | cities.id nullOnDelete | |
| address | text | Yes | — | — | — | |
| receiptor_full_name | string | Yes | — | — | — | |
| receiptor_mobile | string | Yes | — | — | — | |
| postal_code | string | Yes | — | — | — | |

## 30. shipping_methods

| Column | Type | Nullable | Default | FK | SoftDeletes | Notes |
|---|---|---|---|---|---|---|
| id | bigint PK | No | auto | — | **Yes** | |
| title | string | Yes | — | — | | |
| price | string | Yes | — | — | | تومان — string not decimal |
| price_ceiling | double | No | 0 | — | | 0 = no free threshold |
| status | tinyint | No | 0 | — | | |
| description | text | Yes | — | — | | |
| estimated_days_min | smallint | No | 1 | — | | added 2026-08-26 |
| estimated_days_max | smallint | No | 3 | — | | |
| show_estimated_days | boolean | No | true | — | | |
| freight_balance | tinyint | No | 0 | — | | 1 = پسکرایه |
| type | string | No | `fixed_price` | — | | fixed_price|with_weight|chapar|cod24 |
| chapar_type | string | Yes | — | — | | |
| config | json | Yes | — | — | | user/pass |
| sender_name | string | Yes | — | — | | |
| sender_company | string | Yes | — | — | | |
| sender_city_id | bigint FK | Yes | — | cities.id nullOnDelete | | |
| sender_phone | string | Yes | — | — | | |
| sender_mobile | string | Yes | — | — | | |
| sender_email | string | Yes | — | — | | |
| sender_address | text | Yes | — | — | | |
| sender_postal_code | string | Yes | — | — | | |

## 31. shipping_method_city (pivot)

| Column | Type | PK | FK |
|---|---|---|---|
| shipping_method_id | bigint FK | PK composite | shipping_methods.id cascade |
| city_id | bigint FK | PK composite | cities.id cascade |

No timestamps, no SoftDeletes.

## 32. order_shipping_statuses

| Column | Type | Nullable | Default | SoftDeletes |
|---|---|---|---|---|
| id | bigint PK | No | auto | **Yes** |
| title | string | Yes | — | |
| color | string | Yes | `#6c757d` | |
| `default` | tinyint | No | 0 | is default? |
| for_send | tinyint | No | 0 | ready to ship? |
| sending_sms | tinyint | No | 1 | send SMS? |

## 33. banks

| Column | Type | Nullable | Default | SoftDeletes | Notes |
|---|---|---|---|---|---|
| id | bigint PK | No | auto | **Yes** | |
| title | string | Yes | — | | |
| icon | string | Yes | — | | |
| status | tinyint | No | 0 | | |
| admin_test | boolean | No | 0 | | admin-only gateway |
| bank_type | string | Yes | — | | zarinPal|sep|sadad|zibal… |
| card_number | string | Yes | — | | card-to-card |
| card_holder_name | string | Yes | — | | |
| card_description | text | Yes | — | | |
| config | json | Yes | — | | MerchantId / TerminalId |

## 34. orders — Drops old table on creation

| Column | Type | Nullable | Default | Index | FK | Notes |
|---|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — | |
| user_id | bigint FK | Yes | — | **IDX** | users.id nullOnDelete | **only indexed FK** |
| address_id | bigint FK | Yes | — | — | addresses.id nullOnDelete | |
| state_id | bigint FK | Yes | — | — | states.id nullOnDelete | denormalized + JSON snapshot |
| city_id | bigint FK | Yes | — | — | cities.id nullOnDelete | |
| address | text | Yes | — | — | — | JSON: state,city,address,mobile,postal |
| receiptor_full_name | string | Yes | — | — | — | |
| shipping_status_id | bigint FK | Yes | — | — | order_shipping_statuses.id nullOnDelete | |
| order_status | string | Yes | — | **NO INDEX** | — | paying|paid|deposit_paid|unpaid|cancelled|wait_for_verification |
| freight_balance | integer | No | 0 | — | — | 1 = پسکرایه |
| receipt_tracking_code | string | Yes | — | — | — | replaces payment_receipt |
| receipt_payer_card | string | Yes | — | — | — | card-to-card payer card |
| basket_id | bigint | Yes | — | — | **no FK** | cart reference |
| bank_id | bigint FK | Yes | — | — | banks.id nullOnDelete | |
| shipping_method_id | bigint FK | Yes | — | — | shipping_methods.id nullOnDelete | |
| discount_id | bigint | Yes | — | — | **no FK** | FK-less on purpose |
| total_weight | double | Yes | — | — | — | grams |
| post_code | string | Yes | — | — | — | postal tracking |
| shipping_price | string | Yes | — | — | — | string (تومان) |
| total_price | string | Yes | — | — | — | |
| discount_price | string | Yes | — | — | — | |
| payment_price | string | Yes | — | — | — | |
| tax_price | string | Yes | — | — | — | |
| current_tax | string | Yes | — | — | — | percent |
| deposit_price | string | Yes | — | — | — | بیعانه |
| remaining_price | string | Yes | — | — | — | باقیمانده |
| user_description | text | Yes | — | — | — | |
| transaction_info | json | Yes | — | — | — | post + verify payload |
| deleted_at | timestamp | Yes | — | — | — | **SoftDeletes** |

## 35. order_items

| Column | Type | Nullable | Index | FK | SoftDeletes |
|---|---|---|---|---|---|
| id | bigint PK | No | PK | — | **Yes** |
| order_id | bigint FK | No | IDX | orders.id cascade | |
| product_id | bigint FK | Yes | IDX | products.id nullOnDelete | |
| product_variant_id | bigint FK | Yes | — | product_variants.id nullOnDelete | |
| quantity | integer | Yes | — | — | |
| old_quantity | string | Yes | — | — | before return |
| weight | double | Yes | — | — | grams |
| price | string | Yes | — | — | unit price تومان |
| discounted_price | string | Yes | — | — | |

## 36. order_histories

| Column | Type | Nullable | Index | FK | SoftDeletes |
|---|---|---|---|---|---|
| id | bigint PK | No | PK | — | **Yes** |
| order_id | bigint FK | No | IDX | orders.id cascade | |
| shipping_status_id | bigint FK | Yes | — | order_shipping_statuses.id nullOnDelete | |
| order_status | string | Yes | — | — | string snapshot |

## 37. inventory_transactions

| Column | Type | Nullable | Index | FK | SoftDeletes |
|---|---|---|---|---|---|
| id | bigint PK | No | PK | — | **Yes** |
| product_id | bigint FK | Yes | IDX | products.id nullOnDelete | |
| product_variant_id | bigint FK | Yes | — | product_variants.id nullOnDelete | |
| quantity | integer | Yes | — | — | |
| type | string | Yes | — | — | add|subtract |
| order_id | bigint FK | Yes | IDX | orders.id nullOnDelete | |
| description | text | Yes | — | — | |

## 38. discounts

| Column | Type | Nullable | Default | Index | FK | Notes |
|---|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — | |
| title | string | Yes | — | — | — | |
| code | string | Yes | — | **UK** | — | added enhance_discounts, backfilled DISC-0001 |
| amount | string | Yes | — | — | — | cash amount or percent |
| type | string | Yes | — | — | — | cash|percent |
| count | integer | No | 1 | — | — | total uses |
| basket_minimum_price | string | Yes | — | — | — | min basket |
| first_purchase | tinyint | No | 0 | — | — | first order only |
| with_discount | tinyint | No | 0 | — | — | stack with product discount? |
| user_id | integer | Yes | — | IDX | **no FK** | single-user lock (FK-less) |
| max_usage_per_user | integer | No | 1 | — | — | |
| pay_type | string | Yes | — | — | — | category|brand|product |
| is_active | boolean | No | true | — | — | added enhance_discounts |
| starts_at | timestamp | Yes | — | — | — | |
| expires_at | timestamp | Yes | — | — | — | |
| deleted_at | timestamp | Yes | — | — | — | **SoftDeletes** |

## 39. discountable (polymorphic pivot)

| Column | Type | Index | FK |
|---|---|---|---|
| discount_id | bigint FK | — | discounts.id cascade |
| discountable_type | string | morph IDX | — |
| discountable_id | bigint | morph IDX | — |

morphs('discountable') creates discountable_type + discountable_id + composite index. No PK, no timestamps, no SoftDeletes.

## 40. discount_usage

| Column | Type | Nullable | Default | Index | FK |
|---|---|---|---|---|---|
| id | bigint PK | No | PK | — | |
| discount_id | bigint FK | No | — | IDX(discount+user) | discounts.id cascade |
| user_id | bigint FK | Yes | — | IDX | users.id nullOnDelete |
| order_id | bigint FK | Yes | — | — | orders.id nullOnDelete |
| discount_amount | decimal(12,0) | No | 0 | — | — |
| used_at | timestamp | No | — | — | — |

No SoftDeletes.

## 41. discount_users

| Column | Type | Index | FK |
|---|---|---|---|
| id | bigint PK | PK | — |
| discount_id | bigint FK | UK(pair) | discounts.id cascade |
| user_id | bigint FK | UK(pair) | users.id cascade |

Unique (discount_id, user_id).

## 42. carts

| Column | Type | Nullable | Default | Index | FK | Notes |
|---|---|---|---|---|---|---|
| id | bigint PK | No | auto | PK | — | |
| user_id | bigint FK | Yes | — | IDX | users.id nullOnDelete | |
| product_id | bigint FK | No | — | — | products.id cascade | |
| variant_id | integer | Yes | — | — | **no FK** | FK-less, references product_variants.id |
| variant_label | string | Yes | — | — | — | |
| name | string | No | — | — | — | denormalized |
| slug | string | Yes | — | — | — | |
| price | integer | No | — | — | — | |
| original_price | integer | Yes | — | — | — | |
| qty | integer | No | 1 | — | — | |
| stock | integer | No | 0 | — | — | snapshot |
| weight | integer | No | 0 | — | — | |
| color | string | Yes | — | — | — | legacy |
| size | string | Yes | — | — | — | legacy |
| image | string | Yes | — | — | — | |

No SoftDeletes.

## 43. roles

| Column | Type | Nullable | Default | Index |
|---|---|---|---|---|
| id | bigint PK | No | auto | PK |
| name | string | No | — | UK |
| is_system | boolean | No | false | — |

## 44. permissions

| Column | Type | Nullable | Index |
|---|---|---|---|
| id | bigint PK | No | PK |
| name | string | No | UK (admin.products.edit) |
| group | string | No | IDX (UI grouping) |
| label_key | string | Yes | — |

## 45. permission_role / role_user (pivots)

| Table | Column | FK | PK |
|---|---|---|---|
| permission_role | role_id bigint FK | roles.id cascade | PK(role+permission) |
| permission_role | permission_id bigint FK | permissions.id cascade | |
| role_user | user_id bigint FK | users.id cascade | PK(user+role) |
| role_user | role_id bigint FK | roles.id cascade | |

## 46. request_logs

| Column | Type | Nullable | Index | FK |
|---|---|---|---|---|
| id | bigint PK | No | PK | — |
| user_id | bigint FK | Yes | — | users.id nullOnDelete |
| method | string(10) | No | — | — |
| url | text | No | — | — |
| ip | string(45) | Yes | — | — |
| status_code | integer | Yes | — | — |
| payload | json | Yes | — | — |
| created_at | timestamp | — | IDX | for periodic purge |

No SoftDeletes.

## 47. notifications (polymorphic)

| Column | Type | Nullable | Default | Index |
|---|---|---|---|---|
| id | bigint PK | No | PK | |
| notifiable_type | string | No | — | IDX(type+id+is_read) composite |
| notifiable_id | bigint | No | — | composite |
| type | string | No | — | — |
| title | string | No | — | — |
| body | text | Yes | — | — |
| icon | string | Yes | — | — |
| link | string | Yes | — | — |
| is_read | boolean | No | false | composite |
| data | json | Yes | — | — |
| created_at | timestamp | — | — | IDX |

No SoftDeletes. Polymorphic — no FK.

## 48. audit_logs (polymorphic)

| Column | Type | Nullable | Index |
|---|---|---|---|
| id | bigint PK | No | PK |
| event | string | No | IDX + composite |
| auditable_type | string | No | IDX + composite |
| auditable_id | bigint | No | IDX + composite |
| user_id | bigint | Yes | IDX (no FK) |
| old_values | json | Yes | — |
| new_values | json | Yes | — |
| meta | json | Yes | — |
| description | text | Yes | — |

Composite index (auditable_type, auditable_id, event). No SoftDeletes.

## 49. Other system tables

| Table | SoftDeletes | Notes |
|---|---|---|
| cache, cache_locks, jobs, job_batches, failed_jobs, sessions, password_reset_tokens | No | Laravel internals |
| languages, trust_badges, promo_banners, contact_requests | No | Content/config intentionally without SoftDeletes |
| product_images, properties, product_associations, product_variant_specification, shipping_method_city, permission_role, role_user, discountable, review_votes, carts, notifications, audit_logs | No | Pivots / logs / snapshots |

---

## SoftDeletes coverage map

**Have deleted_at (23 tables):** users, blog_categories, blogs, product_categories, products, brands, specifications, product_specification, product_variants, reviews, states, cities, addresses, shipping_methods, order_shipping_statuses, banks, orders, order_items, order_histories, inventory_transactions, discounts.

**Do NOT have SoftDeletes (verified absence):** themes, settings, product_images, properties, product_associations, product_variant_specification, roles, permissions, permission_role, role_user, request_logs, languages, contact_requests, promo_banners, trust_badges, shipping_method_city, discountable, discount_usage, discount_users, review_votes, carts, notifications, audit_logs, cache*, jobs*, sessions, password_reset_tokens.

## Index gaps (verified)

- **orders.order_status has NO index.** Only user_id is indexed. Filtering by order_status without WHERE user_id = ? will full-scan.
- **orders.discount_id / basket_id** are FK-less with no index — not filterable efficiently.
- **discounts.user_id** is integer (not foreignId) with an index but no FK — intentional single-user lock, not a relation.
- **carts.variant_id** is FK-less unsignedInteger — no constraint to product_variants.id.
