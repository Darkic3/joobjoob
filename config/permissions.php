<?php

// درخت دسترسی‌های پنل ادمین — منبع واحد حقیقت
// کلید نهایی هر دسترسی = "admin.{group}.{access}" (نام روت واقعی)
// مقدار هر دسترسی = کلید ترجمه (lang/*/permissions.php)
// ⚠️ کلید هر گروه باید دقیقاً بخش نام روت واقعی باشد (جمع، مثل Route::resource)
return [

    // روت‌هایی که فقط «احراز هویت» می‌خواهند، نه دسترسی خاص
    // (خروج و داشبورد برای هر ادمینِ لاگین‌شده آزادند — داشبورد صرفاً نمای کلی است)
    'free' => [
        // رفتن به مقصد اعلان (خوانده‌شده کردن اعلان خودِ کاربر + ریدایرکت) — مقصد نهایی همچنان پرمیشن خودش را دارد
        'admin.notifications.go',
        'admin.ckeditor.upload',
        'admin.logout',
        'admin.dashboard.index',
        'admin.monitoring.index',          // مانیتورینگ — فقط نمای کلی، بدون قدرت تخریبی
        'admin.monitoring.errors.resolve',  // علامت‌گذاری حل خطا — بی‌خطر
    ],

    'permissions' => [

        // ⚠️ گروه dashboard عمداً در درخت نیست — چون admin.dashboard.index در لیست free است
        // و هر ادمینِ لاگین‌شده به آن دسترسی دارد (نمای کلی، بدون قدرت تخریبی).

        'products' => [
            'title' => 'permissions.groups.products',
            'access' => [
                'index' => 'permissions.access.products.index',
                'create' => 'permissions.access.products.create',
                'store' => 'permissions.access.products.create',   // لیبل مشترک → UI dedupe می‌کند
                'edit' => 'permissions.access.products.edit',
                'update' => 'permissions.access.products.edit',
                'destroy' => 'permissions.access.products.delete',
            ],
        ],

        'product-categories' => [
            'title' => 'permissions.groups.product-categories',
            'access' => [
                'index' => 'permissions.access.product-categories.index',
                'create' => 'permissions.access.product-categories.create',
                'store' => 'permissions.access.product-categories.create',
                'edit' => 'permissions.access.product-categories.edit',
                'update' => 'permissions.access.product-categories.edit',
                'destroy' => 'permissions.access.product-categories.delete',
            ],
        ],

        'brands' => [
            'title' => 'permissions.groups.brands',
            'access' => [
                'index' => 'permissions.access.brands.index',
                'create' => 'permissions.access.brands.create',
                'store' => 'permissions.access.brands.create',
                'edit' => 'permissions.access.brands.edit',
                'update' => 'permissions.access.brands.edit',
                'reorder' => 'permissions.access.brands.edit',
                'destroy' => 'permissions.access.brands.delete',
            ],
        ],

        'specifications' => [
            'title' => 'permissions.groups.specifications',
            'access' => [
                'index' => 'permissions.access.specifications.index',
                'create' => 'permissions.access.specifications.create',
                'store' => 'permissions.access.specifications.create',
                'edit' => 'permissions.access.specifications.edit',
                'update' => 'permissions.access.specifications.edit',
                'destroy' => 'permissions.access.specifications.delete',
            ],
        ],

        'blogs' => [
            'title' => 'permissions.groups.blogs',
            'access' => [
                'index' => 'permissions.access.blogs.index',
                'create' => 'permissions.access.blogs.create',
                'store' => 'permissions.access.blogs.create',
                'edit' => 'permissions.access.blogs.edit',
                'update' => 'permissions.access.blogs.edit',
                'destroy' => 'permissions.access.blogs.delete',
            ],
        ],

        'blog-categories' => [
            'title' => 'permissions.groups.blog-categories',
            'access' => [
                'index' => 'permissions.access.blog-categories.index',
                'create' => 'permissions.access.blog-categories.create',
                'store' => 'permissions.access.blog-categories.create',
                'edit' => 'permissions.access.blog-categories.edit',
                'update' => 'permissions.access.blog-categories.edit',
                'destroy' => 'permissions.access.blog-categories.delete',
            ],
        ],

        'hero-slides' => [
            'title' => 'permissions.groups.hero-slides',
            'access' => [
                'index' => 'permissions.access.hero-slides.index',
                'create' => 'permissions.access.hero-slides.create',
                'store' => 'permissions.access.hero-slides.create',
                'edit' => 'permissions.access.hero-slides.edit',
                'update' => 'permissions.access.hero-slides.edit',
                'reorder' => 'permissions.access.hero-slides.edit',
                'search-model' => 'permissions.access.hero-slides.edit',
                'destroy' => 'permissions.access.hero-slides.delete',
            ],
        ],

        'trust-badges' => [
            'title' => 'permissions.groups.trust-badges',
            'access' => [
                'index' => 'permissions.access.trust-badges.index',
                'create' => 'permissions.access.trust-badges.create',
                'store' => 'permissions.access.trust-badges.create',
                'edit' => 'permissions.access.trust-badges.edit',
                'update' => 'permissions.access.trust-badges.edit',
                'reorder' => 'permissions.access.trust-badges.edit',
                'destroy' => 'permissions.access.trust-badges.delete',
            ],
        ],

        'promo-banners' => [
            'title' => 'permissions.groups.promo-banners',
            'access' => [
                'index' => 'permissions.access.promo-banners.index',
                'create' => 'permissions.access.promo-banners.create',
                'store' => 'permissions.access.promo-banners.create',
                'edit' => 'permissions.access.promo-banners.edit',
                'update' => 'permissions.access.promo-banners.edit',
                'reorder' => 'permissions.access.promo-banners.edit',
                'destroy' => 'permissions.access.promo-banners.delete',
            ],
        ],

        'settings' => [
            'title' => 'permissions.groups.settings',
            'access' => [
                'edit' => 'permissions.access.settings.edit',
                'update' => 'permissions.access.settings.edit',
            ],
        ],

        'languages' => [
            'title' => 'permissions.groups.languages',
            'access' => [
                'index' => 'permissions.access.languages.index',
                'create' => 'permissions.access.languages.create',
                'store' => 'permissions.access.languages.create',
                'edit' => 'permissions.access.languages.edit',
                'update' => 'permissions.access.languages.edit',
                // اکشن‌های سریع ایندکس (تغییر وضعیت / پیش‌فرض‌کردن / ترتیب درگ‌اندروپ) — همان لیبل ویرایش
                'toggle' => 'permissions.access.languages.edit',
                'make-default' => 'permissions.access.languages.edit',
                'reorder' => 'permissions.access.languages.edit',
                'destroy' => 'permissions.access.languages.delete',
            ],
        ],

        // ⏳ گروه‌های آینده (از الان در درخت، تا نقش main خودکار پوشش دهد)
        'users' => [
            'title' => 'permissions.groups.users',
            'access' => [
                'index' => 'permissions.access.users.index',
                'create' => 'permissions.access.users.create',
                'store' => 'permissions.access.users.create',
                'edit' => 'permissions.access.users.edit',
                'update' => 'permissions.access.users.edit',
                'destroy' => 'permissions.access.users.delete',
            ],
        ],

        'roles' => [
            'title' => 'permissions.groups.roles',
            'access' => [
                'index' => 'permissions.access.roles.index',
                'create' => 'permissions.access.roles.create',
                'store' => 'permissions.access.roles.create',
                'edit' => 'permissions.access.roles.edit',
                'update' => 'permissions.access.roles.edit',
                'destroy' => 'permissions.access.roles.delete',
            ],
        ],

        'orders' => [
            'title' => 'permissions.groups.orders',
            'access' => [
                'index' => 'permissions.access.orders.index',
                'show' => 'permissions.access.orders.show',
                'change-shipping-status' => 'permissions.access.orders.change_status',
                'change-order-status' => 'permissions.access.orders.change_status',
                'post-code' => 'permissions.access.orders.change_status',
                'factor' => 'permissions.access.orders.show',
                'bulk-factor' => 'permissions.access.orders.show',
            ],
        ],

        'reviews' => [
            'title' => 'permissions.groups.reviews',
            'access' => [
                'index' => 'permissions.access.reviews.index',
                'approval' => 'permissions.access.reviews.approve',
                'featured' => 'permissions.access.reviews.approve',
                'destroy' => 'permissions.access.reviews.delete',
            ],
        ],

        'contact' => [
            'title' => 'permissions.groups.contact',
            'access' => [
                'index' => 'permissions.access.contact.index',
                'show' => 'permissions.access.contact.show',
                'destroy' => 'permissions.access.contact.delete',
            ],
        ],

        'shipping-methods' => [
            'title' => 'permissions.groups.shipping_methods',
            'access' => [
                'index' => 'permissions.access.shipping_methods.index',
                'create' => 'permissions.access.shipping_methods.create',
                'store' => 'permissions.access.shipping_methods.create',
                'edit' => 'permissions.access.shipping_methods.edit',
                'update' => 'permissions.access.shipping_methods.edit',
                'destroy' => 'permissions.access.shipping_methods.delete',
                'toggle-status' => 'permissions.access.shipping_methods.edit',
            ],
        ],

        'banks' => [
            'title' => 'permissions.groups.banks',
            'access' => [
                'index' => 'permissions.access.banks.index',
                'create' => 'permissions.access.banks.create',
                'store' => 'permissions.access.banks.create',
                'edit' => 'permissions.access.banks.edit',
                'update' => 'permissions.access.banks.edit',
                'destroy' => 'permissions.access.banks.delete',
                'toggle-status' => 'permissions.access.banks.edit',
            ],
        ],

        'notifications' => [
            'title' => 'permissions.groups.notifications',
            'access' => [
                'index' => 'permissions.access.notifications.index',
                'mark-read' => 'permissions.access.notifications.index',
                'mark-all-read' => 'permissions.access.notifications.index',
                'unread-count' => 'permissions.access.notifications.index',
            ],
        ],

        'audit-logs' => [
            'title' => 'permissions.groups.audit_logs',
            'access' => [
                'index' => 'permissions.access.audit_logs.index',
                'show' => 'permissions.access.audit_logs.show',
                'order-history' => 'permissions.access.audit_logs.show',
                'product-history' => 'permissions.access.audit_logs.show',
            ],
        ],
    ],
];
