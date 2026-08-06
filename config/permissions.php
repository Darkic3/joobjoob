<?php

// درخت دسترسی‌های پنل ادمین — منبع واحد حقیقت
// کلید نهایی هر دسترسی = "admin.{group}.{access}" (نام روت واقعی)
// مقدار هر دسترسی = کلید ترجمه (lang/*/permissions.php)
// ⚠️ کلید هر گروه باید دقیقاً بخش نام روت واقعی باشد (جمع، مثل Route::resource)
return [

    // روت‌هایی که فقط «احراز هویت» می‌خواهند، نه دسترسی خاص
    'free' => [
        'admin.ckeditor.upload',
    ],

    'permissions' => [

        'dashboard' => [
            'title'  => 'permissions.groups.dashboard',
            'access' => ['index' => 'permissions.access.dashboard.index'],
        ],

        'products' => [
            'title'  => 'permissions.groups.products',
            'access' => [
                'index'   => 'permissions.access.products.index',
                'create'  => 'permissions.access.products.create',
                'store'   => 'permissions.access.products.create',   // لیبل مشترک → UI dedupe می‌کند
                'edit'    => 'permissions.access.products.edit',
                'update'  => 'permissions.access.products.edit',
                'destroy' => 'permissions.access.products.delete',
            ],
        ],

        'product-categories' => [
            'title'  => 'permissions.groups.product-categories',
            'access' => [
                'index'   => 'permissions.access.product-categories.index',
                'create'  => 'permissions.access.product-categories.create',
                'store'   => 'permissions.access.product-categories.create',
                'edit'    => 'permissions.access.product-categories.edit',
                'update'  => 'permissions.access.product-categories.edit',
                'destroy' => 'permissions.access.product-categories.delete',
            ],
        ],

        'brands' => [
            'title'  => 'permissions.groups.brands',
            'access' => [
                'index'   => 'permissions.access.brands.index',
                'create'  => 'permissions.access.brands.create',
                'store'   => 'permissions.access.brands.create',
                'edit'    => 'permissions.access.brands.edit',
                'update'  => 'permissions.access.brands.edit',
                'destroy' => 'permissions.access.brands.delete',
            ],
        ],

        'specifications' => [
            'title'  => 'permissions.groups.specifications',
            'access' => [
                'index'   => 'permissions.access.specifications.index',
                'create'  => 'permissions.access.specifications.create',
                'store'   => 'permissions.access.specifications.create',
                'edit'    => 'permissions.access.specifications.edit',
                'update'  => 'permissions.access.specifications.edit',
                'destroy' => 'permissions.access.specifications.delete',
            ],
        ],

        'blogs' => [
            'title'  => 'permissions.groups.blogs',
            'access' => [
                'index'   => 'permissions.access.blogs.index',
                'create'  => 'permissions.access.blogs.create',
                'store'   => 'permissions.access.blogs.create',
                'edit'    => 'permissions.access.blogs.edit',
                'update'  => 'permissions.access.blogs.edit',
                'destroy' => 'permissions.access.blogs.delete',
            ],
        ],

        'blog-categories' => [
            'title'  => 'permissions.groups.blog-categories',
            'access' => [
                'index'   => 'permissions.access.blog-categories.index',
                'create'  => 'permissions.access.blog-categories.create',
                'store'   => 'permissions.access.blog-categories.create',
                'edit'    => 'permissions.access.blog-categories.edit',
                'update'  => 'permissions.access.blog-categories.edit',
                'destroy' => 'permissions.access.blog-categories.delete',
            ],
        ],

        'hero-slides' => [
            'title'  => 'permissions.groups.hero-slides',
            'access' => [
                'index'   => 'permissions.access.hero-slides.index',
                'create'  => 'permissions.access.hero-slides.create',
                'store'   => 'permissions.access.hero-slides.create',
                'edit'    => 'permissions.access.hero-slides.edit',
                'update'  => 'permissions.access.hero-slides.edit',
                'destroy' => 'permissions.access.hero-slides.delete',
            ],
        ],

        'trust-badges' => [
            'title'  => 'permissions.groups.trust-badges',
            'access' => [
                'index'   => 'permissions.access.trust-badges.index',
                'create'  => 'permissions.access.trust-badges.create',
                'store'   => 'permissions.access.trust-badges.create',
                'edit'    => 'permissions.access.trust-badges.edit',
                'update'  => 'permissions.access.trust-badges.edit',
                'destroy' => 'permissions.access.trust-badges.delete',
            ],
        ],

        'settings' => [
            'title'  => 'permissions.groups.settings',
            'access' => [
                'edit'   => 'permissions.access.settings.edit',
                'update' => 'permissions.access.settings.edit',
            ],
        ],

        // ⏳ گروه‌های آینده (از الان در درخت، تا نقش main خودکار پوشش دهد)
        'users' => [
            'title'  => 'permissions.groups.users',
            'access' => [
                'index'   => 'permissions.access.users.index',
                'create'  => 'permissions.access.users.create',
                'store'   => 'permissions.access.users.create',
                'edit'    => 'permissions.access.users.edit',
                'update'  => 'permissions.access.users.edit',
                'destroy' => 'permissions.access.users.delete',
            ],
        ],

        'roles' => [
            'title'  => 'permissions.groups.roles',
            'access' => [
                'index'   => 'permissions.access.roles.index',
                'create'  => 'permissions.access.roles.create',
                'store'   => 'permissions.access.roles.create',
                'edit'    => 'permissions.access.roles.edit',
                'update'  => 'permissions.access.roles.edit',
                'destroy' => 'permissions.access.roles.delete',
            ],
        ],

        'orders' => [
            'title'  => 'permissions.groups.orders',
            'access' => [
                'index' => 'permissions.access.orders.index',
                'show'  => 'permissions.access.orders.show',
            ],
        ],

        'reviews' => [
            'title'  => 'permissions.groups.reviews',
            'access' => [
                'index'   => 'permissions.access.reviews.index',
                'destroy' => 'permissions.access.reviews.delete',
            ],
        ],
    ],
];
