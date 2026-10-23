# ERD — Joobjoob Database

> Source: `database/migrations` — 73 files verified 2026-09-04. All relationships from FK/morph definitions only.

```mermaid
erDiagram
    users ||--o{ addresses : owns
    users ||--o{ orders : places
    users ||--o{ reviews : writes
    users ||--o{ review_votes : votes
    users ||--o{ carts : owns
    users ||--o{ blogs : authors
    users }o--o{ roles : role_user
    roles }o--o{ permissions : permission_role
    users ||--o{ request_logs : generates
    users ||--o{ audit_logs : performs
    users ||--o{ notifications : receives
    users ||--o{ discount_usage : uses
    users }o--o{ discounts : discount_users

    states ||--o{ cities : has
    states ||--o{ addresses : state
    cities ||--o{ addresses : city
    cities }o--o{ shipping_methods : shipping_method_city
    cities ||--o{ shipping_methods : sender_city

    product_categories ||--o{ product_categories : parent
    product_categories ||--o{ products : contains
    brands ||--o{ products : brand
    products ||--o{ product_images : images
    products ||--o{ product_variants : variants
    products ||--o{ properties : properties
    products ||--o{ product_specification : specs
    products }o--o{ products : associations
    specifications ||--o{ specifications : parent
    specifications ||--o{ product_specification : values
    specifications ||--o{ product_variant_specification : variant_specs
    product_variants ||--o{ product_variant_specification : has
    product_variants ||--o{ order_items : ordered_variant
    product_variants ||--o{ inventory_transactions : stock
    products ||--o{ order_items : ordered
    products ||--o{ inventory_transactions : stock
    products ||--o{ reviews : reviewed
    products ||--o{ carts : in_cart

    addresses ||--o{ orders : address
    states ||--o{ orders : state_snapshot
    cities ||--o{ orders : city_snapshot
    order_shipping_statuses ||--o{ orders : shipping_status
    order_shipping_statuses ||--o{ order_histories : status
    shipping_methods ||--o{ orders : shipping_method
    banks ||--o{ orders : bank
    discounts ||--o{ orders : applied
    orders ||--o{ order_items : items
    orders ||--o{ order_histories : history
    orders ||--o{ inventory_transactions : inventory
    orders ||--o{ discount_usage : usage

    discounts ||--o{ discountable : polymorphic
    discounts ||--o{ discount_usage : usages
    discounts }o--o{ users : eligibility

    blog_categories ||--o{ blog_categories : parent
    blog_categories ||--o{ blogs : category
    hero_slides }o--o{ products : linkable
    hero_slides }o--o{ product_categories : linkable
    hero_slides }o--o{ blogs : linkable

    themes ||--o{ users : legacy_string_no_FK

    users {
        bigint id PK
        string mobile UK
        string email UK_nullable
        string locale
        string theme
        boolean is_admin
        boolean is_active
        string password
        string avatar_nullable
        string confirm_code_nullable
        timestamp deleted_at
    }
    themes {
        bigint id PK
        string name
        string slug UK
        string preview_color
        json css_variables_nullable
        boolean is_default
    }
    product_categories {
        bigint id PK
        bigint parent_id_FK_nullable
        json name
        string slug UK
        string icon_nullable
        string image_nullable
        json description_nullable
        smallint sort_order
        boolean is_active
        timestamp deleted_at
    }
    products {
        bigint id PK
        bigint product_category_id_FK
        bigint brand_id_FK_nullable
        json name
        string slug UK
        string sku_nullable_UK
        bigint price
        bigint discount_price_nullable
        bigint final_price_IDX
        int stock
        int weight_nullable
        string main_image_nullable
        boolean is_active_IDX
        boolean is_featured
        boolean is_best_seller
        boolean timer_active
        timestamp start_timer_nullable
        timestamp end_timer_nullable
        timestamp deleted_at
    }
    product_images {
        bigint id PK
        bigint product_id_FK
        string path
        json alt_text_nullable
        boolean is_primary
        smallint sort_order
    }
    brands {
        bigint id PK
        json title
        string url UK
        json description_nullable
        string image_nullable
        int sort_order
        boolean active
        boolean show_in_first_page
        timestamp deleted_at
    }
    specifications {
        bigint id PK
        json title
        enum type_text_select_IDX
        bigint parent_id_FK_nullable
        boolean active
        boolean is_filter
        boolean is_color
        string color_code_nullable
        timestamp deleted_at
    }
    product_specification {
        bigint id PK
        bigint product_id_FK
        bigint specification_id_FK
        text value_nullable
        bigint parent_id_FK_nullable
        timestamp deleted_at
    }
    product_variants {
        bigint id PK
        bigint product_id_FK_IDX
        double price
        double discounted_price
        double final_price_IDX
        int stock
        double weight
        timestamp deleted_at
    }
    product_variant_specification {
        bigint id PK
        bigint product_variant_id_FK
        bigint specification_id_FK
    }
    properties {
        bigint id PK
        bigint product_id_FK_IDX
        json title_nullable
        string value
        int sort_order
    }
    product_associations {
        bigint id PK
        bigint product_id_FK
        bigint related_product_id_FK
        enum type_related_complement_UK
    }
    states {
        bigint id PK
        string name_nullable
        tinyint status
        int cod24_id_nullable
        int chapar_id_nullable
        timestamp deleted_at
    }
    cities {
        bigint id PK
        string name_nullable
        tinyint status
        bigint state_id_FK_nullable
        int cod24_id_nullable
        int chapar_id_nullable
        timestamp deleted_at
    }
    addresses {
        bigint id PK
        bigint user_id_FK_nullable_IDX
        bigint state_id_FK_nullable
        bigint city_id_FK_nullable
        text address_nullable
        string receiptor_full_name_nullable
        string receiptor_mobile_nullable
        string postal_code_nullable
        timestamp deleted_at
    }
    shipping_methods {
        bigint id PK
        string title_nullable
        string price_nullable
        double price_ceiling
        tinyint status
        text description_nullable
        smallint estimated_days_min
        smallint estimated_days_max
        boolean show_estimated_days
        tinyint freight_balance
        string type
        string chapar_type_nullable
        json config_nullable
        bigint sender_city_id_FK_nullable
        timestamp deleted_at
    }
    shipping_method_city {
        bigint shipping_method_id_FK_PK
        bigint city_id_FK_PK
    }
    order_shipping_statuses {
        bigint id PK
        string title_nullable
        string color_nullable
        tinyint default
        tinyint for_send
        tinyint sending_sms
        timestamp deleted_at
    }
    banks {
        bigint id PK
        string title_nullable
        string icon_nullable
        tinyint status
        boolean admin_test
        string bank_type_nullable
        string card_number_nullable
        string card_holder_name_nullable
        text card_description_nullable
        json config_nullable
        timestamp deleted_at
    }
    orders {
        bigint id PK
        bigint user_id_FK_nullable_IDX
        bigint address_id_FK_nullable
        bigint state_id_FK_nullable
        bigint city_id_FK_nullable
        text address_nullable_JSON
        string receiptor_full_name_nullable
        bigint shipping_status_id_FK_nullable
        string order_status_nullable_NO_IDX
        int freight_balance
        string receipt_tracking_code_nullable
        string receipt_payer_card_nullable
        bigint basket_id_nullable_noFK
        bigint bank_id_FK_nullable
        bigint shipping_method_id_FK_nullable
        bigint discount_id_nullable_noFK
        double total_weight_nullable
        string post_code_nullable
        string shipping_price_nullable
        string total_price_nullable
        json transaction_info_nullable
        timestamp deleted_at
    }
    order_items {
        bigint id PK
        bigint order_id_FK_IDX
        bigint product_id_FK_nullable_IDX
        bigint product_variant_id_FK_nullable
        int quantity_nullable
        string old_quantity_nullable
        double weight_nullable
        string price_nullable
        string discounted_price_nullable
        timestamp deleted_at
    }
    order_histories {
        bigint id PK
        bigint order_id_FK_IDX
        bigint shipping_status_id_FK_nullable
        string order_status_nullable
        timestamp deleted_at
    }
    inventory_transactions {
        bigint id PK
        bigint product_id_FK_nullable_IDX
        bigint product_variant_id_FK_nullable
        int quantity_nullable
        string type_nullable
        bigint order_id_FK_nullable_IDX
        text description_nullable
        timestamp deleted_at
    }
    discounts {
        bigint id PK
        string title_nullable
        string code_UK_nullable
        string amount_nullable
        string type_nullable
        int count
        string basket_minimum_price_nullable
        tinyint first_purchase
        tinyint with_discount
        int user_id_nullable_IDX_noFK
        int max_usage_per_user
        string pay_type_nullable
        boolean is_active
        timestamp starts_at_nullable
        timestamp expires_at_nullable
        timestamp deleted_at
    }
    discountable {
        bigint discount_id_FK
        string discountable_type
        bigint discountable_id
    }
    discount_usage {
        bigint id PK
        bigint discount_id_FK
        bigint user_id_FK_nullable
        bigint order_id_FK_nullable
        decimal discount_amount
        timestamp used_at
    }
    discount_users {
        bigint id PK
        bigint discount_id_FK
        bigint user_id_FK
    }
    carts {
        bigint id PK
        bigint user_id_FK_nullable_IDX
        bigint product_id_FK
        int variant_id_nullable_noFK
        string variant_label_nullable
        string name
        string slug_nullable
        int price
        int qty
        int stock
        int weight
        string image_nullable
    }
    reviews {
        bigint id PK
        bigint product_id_FK
        bigint user_id_FK_nullable
        bigint parent_id_FK_nullable_self
        tinyint rating
        text comment_nullable
        tinyint is_approved
        boolean is_featured_IDX
        string guest_name_nullable
        string guest_phone_nullable
        timestamp deleted_at
    }
    review_votes {
        bigint id PK
        bigint review_id_FK
        bigint user_id_FK
        enum type_like_dislike_UK
    }
    blog_categories {
        bigint id PK
        bigint parent_id_FK_nullable_self
        string title
        string slug_UK_IDX
        text description_nullable
        string image_nullable
        boolean is_active_IDX
        smallint sort_order
        timestamp deleted_at
    }
    blogs {
        bigint id PK
        bigint blog_category_id_FK_nullable
        bigint user_id_FK_nullable
        string title
        string slug_UK_IDX
        text excerpt_nullable
        longText content
        string image_nullable
        bigint views_IDX
        boolean is_active_IDX
        timestamp deleted_at
    }
    hero_slides {
        bigint id PK
        json badge_nullable
        json title
        json desc_nullable
        json btn_text_nullable
        bigint price
        bigint old_price_nullable
        string currency
        string rating_nullable
        int viewers
        json trusts_nullable
        string linkable_type_nullable
        string linkable_id_string_nullable_IDX
        string image_nullable
        boolean is_active
        int sort_order
    }
    notifications {
        bigint id PK
        string notifiable_type
        bigint notifiable_id
        string type
        string title
        text body_nullable
        string icon_nullable
        string link_nullable
        boolean is_read
        json data_nullable
    }
    audit_logs {
        bigint id PK
        string event_IDX
        string auditable_type_IDX
        bigint auditable_id_IDX
        bigint user_id_nullable_IDX
        json old_values_nullable
        json new_values_nullable
        json meta_nullable
        text description_nullable
    }
```

## Notes

- `discountable` uses `morphs('discountable')` — composite index on `(discountable_type, discountable_id)`.
- `hero_slides.linkable_id` migrated from `unsignedBigInteger` to `string` to hold URLs/page keys (verified 2026-08-08).
- `themes` has no FK from `users.theme` — `users.theme` is a plain string (legacy).
- `orders.order_status` has **no index** — only `user_id` is indexed (verified gap).
- `discounts.user_id` and `carts.variant_id` and `orders.discount_id`/`basket_id` are FK-less integers — intentional, no constraint.
- SoftDeletes on 23 tables — see schema-dictionary.md for coverage map.
