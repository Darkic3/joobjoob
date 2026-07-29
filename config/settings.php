<?php

return [
    'tabs' => [
        'general' => 'عمومی',
        'home' => 'صفحه اصلی',
        'home_about' => 'صفحه اصلی - درباره',
        'feature_home' => 'ویژگی‌ها',
        'socials' => 'شبکه‌های اجتماعی',
        'banners' => 'بنرها',
        'contact' => 'تماس با ما',
        'about' => 'درباره ما',
        'footer' => 'فوتر',
        'pages' => 'عناوین صفحات',
    ],

    'fields' => [
        // --- عمومی ---
        'site_name' => [
            'label' => 'نام سایت',
            'type' => 'text',
            'tab' => 'general',
            'required' => true,
        ],
        'logo' => [
            'label' => 'لوگو',
            'type' => 'file',
            'tab' => 'general',
            'required' => false,
        ],

        // --- صفحه اصلی ---
        'slider_title' => [
            'label' => 'عنوان اسلایدر',
            'type' => 'text',
            'tab' => 'home',
            'required' => false,
        ],
        'slider_description' => [
            'label' => 'توضیحات اسلایدر',
            'type' => 'textarea',
            'tab' => 'home',
            'required' => false,
        ],

        // --- درباره ما (مثال) ---
        'home_info_title' => [
            'label' => 'عنوان درباره ما',
            'type' => 'text',
            'tab' => 'home_about',
            'required' => false,
        ],
        'compony_history' => [
            'label' => 'تاریخچه شرکت',
            'type' => 'textarea',
            'tab' => 'about',
            'required' => false,
            'ckeditor' => true,
        ],

        // --- تماس با ما (مثال) ---
        'email' => [
            'label' => 'ایمیل',
            'type' => 'text',
            'tab' => 'contact',
            'required' => false,
        ],
        'address_one' => [
            'label' => 'آدرس دفتر مرکزی',
            'type' => 'textarea',
            'tab' => 'contact',
            'required' => false,
        ],

        // --- فوتر (مثال) ---
        'footer_description' => [
            'label' => 'توضیحات فوتر',
            'type' => 'textarea',
            'tab' => 'footer',
            'required' => false,
        ],

    ],
];
