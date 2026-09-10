<?php

return [
    'tabs' => [
        'general' => 'عمومی',
        'sms' => 'پیامک',
        'home' => 'صفحه اصلی',
        'home_about' => 'صفحه اصلی - درباره',
        'feature_home' => 'ویژگی‌ها',
        'announce' => 'نوار اعلان',
        'newsletter' => 'خبرنامه',
        'socials' => 'شبکه‌های اجتماعی',
        'contact' => 'تماس با ما',
        'about' => 'درباره ما',
        'footer' => 'فوتر',
        'pages' => 'عناوین صفحات',
        'appearance' => 'ظاهر و تم',
        'smart_notifications' => 'اعلانات هوشمند',
    ],

    'fields' => [
        // ==================== عمومی ====================
        'site_name' => [
            'label' => 'نام سایت',
            'type' => 'text',
            'tab' => 'general',
            'required' => true,
            'default' => 'فروشگاه شاپورا',
        ],
        'site_slogan' => [
            'label' => 'شعار سایت',
            'type' => 'text',
            'tab' => 'general',
            'required' => false,
            'default' => 'خرید آسان، سریع و مطمئن',
        ],
        'logo' => [
            'label' => 'لوگو',
            'type' => 'file',
            'tab' => 'general',
            'required' => false,
        ],
        'favicon' => [
            'label' => 'فاوآیکون',
            'type' => 'file',
            'tab' => 'general',
            'required' => false,
        ],

        // ==================== صفحه اصلی ====================
        'slider_title' => [
            'label' => 'عنوان اسلایدر',
            'type' => 'text',
            'tab' => 'home',
            'required' => false,
            'default' => 'به فروشگاه شاپورا خوش آمدید',
        ],
        'slider_description' => [
            'label' => 'توضیحات اسلایدر',
            'type' => 'textarea',
            'tab' => 'home',
            'required' => false,
            'default' => 'بهترین محصولات با بهترین قیمت',
        ],
        'home_deals_title' => [
            'label' => 'عنوان بخش پیشنهادهای ویژه',
            'type' => 'text',
            'tab' => 'home',
            'required' => false,
            'default' => 'پیشنهادهای ویژه',
        ],
        'home_deals_subtitle' => [
            'label' => 'زیرعنوان بخش پیشنهادهای ویژه',
            'type' => 'text',
            'tab' => 'home',
            'required' => false,
            'default' => 'از این پیشنهادها جا نمانید!',
        ],
        'home_categories_title' => [
            'label' => 'عنوان بخش دسته‌بندی‌ها',
            'type' => 'text',
            'tab' => 'home',
            'required' => false,
            'default' => 'دسته‌بندی‌ها',
        ],
        'home_featured_title' => [
            'label' => 'عنوان بخش محصولات ویژه',
            'type' => 'text',
            'tab' => 'home',
            'required' => false,
            'default' => 'محصولات ویژه',
        ],
        'home_best_sellers_title' => [
            'label' => 'عنوان بخش پرفروش‌ترین‌ها',
            'type' => 'text',
            'tab' => 'home',
            'required' => false,
            'default' => 'پرفروش‌ترین‌ها',
        ],
        'home_reviews_title' => [
            'label' => 'عنوان بخش نظرات مشتریان',
            'type' => 'text',
            'tab' => 'home',
            'required' => false,
            'default' => 'نظرات مشتریان',
        ],
        'home_view_all' => [
            'label' => 'متن «مشاهده همه»',
            'type' => 'text',
            'tab' => 'home',
            'required' => false,
            'default' => 'مشاهده همه',
        ],
        'home_blogs_title' => [
            'label' => 'عنوان بخش آخرین مقالات',
            'type' => 'text',
            'tab' => 'home',
            'required' => false,
            'default' => 'آخرین مقالات',
        ],

        // ==================== نوار اعلان ====================
        'announce_1' => [
            'label' => 'اعلان اول',
            'type' => 'text',
            'tab' => 'announce',
            'required' => false,
            'default' => 'ارسال رایگان برای خریدهای بالای ۵۰۰ هزار تومان',
        ],
        'announce_2' => [
            'label' => 'اعلان دوم',
            'type' => 'text',
            'tab' => 'announce',
            'required' => false,
            'default' => 'با کد WELCOME10، ۱۰٪ تخفیف اولین خرید بگیر',
        ],
        'announce_3' => [
            'label' => 'اعلان سوم',
            'type' => 'text',
            'tab' => 'announce',
            'required' => false,
            'default' => 'پیشنهادهای لحظه‌ای فقط تا پایان امشب',
        ],

        // ==================== خبرنامه ====================
        'newsletter_enabled' => [
            'label' => 'فعال‌سازی بخش خبرنامه',
            'type' => 'boolean',
            'tab' => 'newsletter',
            'required' => false,
            'default' => false,
            'guide' => 'اگر روشن باشد، فرم عضویت در فوتر نمایش داده می‌شود و کاربران می‌توانند از پنل کاربری عضو/لغو عضویت کنند. اگر خاموش باشد، فرم فوتر مخفی و دکمه عضویت در پنل کاربری غیرفعال می‌شود.',
        ],
        'newsletter_title' => [
            'label' => 'عنوان بخش خبرنامه',
            'type' => 'text',
            'tab' => 'newsletter',
            'required' => false,
            'default' => '۱۰٪ تخفیف خرید اول، هدیه‌ی ماست',
        ],
        'newsletter_desc' => [
            'label' => 'توضیحات خبرنامه',
            'type' => 'textarea',
            'tab' => 'newsletter',
            'required' => false,
            'default' => 'با عضویت در خبرنامه، کد تخفیف رو همین الان بگیر',
        ],
        'newsletter_placeholder' => [
            'label' => 'متن placeholder ایمیل',
            'type' => 'text',
            'tab' => 'newsletter',
            'required' => false,
            'default' => 'ایمیل شما',
        ],
        'newsletter_button' => [
            'label' => 'متن دکمه عضویت',
            'type' => 'text',
            'tab' => 'newsletter',
            'required' => false,
            'default' => 'عضویت',
        ],

        // ==================== شبکه‌های اجتماعی ====================
        'social_instagram' => [
            'label' => 'اینستاگرام',
            'type' => 'text',
            'tab' => 'socials',
            'required' => false,
        ],
        'social_telegram' => [
            'label' => 'تلگرام',
            'type' => 'text',
            'tab' => 'socials',
            'required' => false,
        ],
        'social_whatsapp' => [
            'label' => 'واتساپ',
            'type' => 'text',
            'tab' => 'socials',
            'required' => false,
        ],
        'social_twitter' => [
            'label' => 'توییتر / ایکس',
            'type' => 'text',
            'tab' => 'socials',
            'required' => false,
        ],
        'social_youtube' => [
            'label' => 'یوتیوب',
            'type' => 'text',
            'tab' => 'socials',
            'required' => false,
        ],

        // ==================== تماس با ما ====================
        'email' => [
            'label' => 'ایمیل',
            'type' => 'text',
            'tab' => 'contact',
            'required' => false,
        ],
        'phone' => [
            'label' => 'تلفن تماس',
            'type' => 'text',
            'tab' => 'contact',
            'required' => false,
        ],
        'mobile' => [
            'label' => 'موبایل / واتساپ',
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
        'working_hours' => [
            'label' => 'ساعات کاری',
            'type' => 'text',
            'tab' => 'contact',
            'required' => false,
            'default' => 'شنبه تا پنجشنبه، ۹ صبح تا ۶ عصر',
        ],

        // ==================== درباره ما ====================
        'about_title' => [
            'label' => 'عنوان درباره ما',
            'type' => 'text',
            'tab' => 'about',
            'required' => false,
            'default' => 'درباره ما',
        ],
        'about_description' => [
            'label' => 'متن درباره ما',
            'type' => 'textarea',
            'tab' => 'about',
            'required' => false,
            'ckeditor' => true,
            'default' => 'فروشگاه شاپورا با هدف ارائه بهترین محصولات با کیفیت و قیمت مناسب فعالیت می‌کند.',
        ],
        'compony_history' => [
            'label' => 'تاریخچه شرکت',
            'type' => 'textarea',
            'tab' => 'about',
            'required' => false,
            'ckeditor' => true,
        ],

        // ==================== فوتر ====================
        'footer_description' => [
            'label' => 'توضیحات فوتر',
            'type' => 'textarea',
            'tab' => 'footer',
            'required' => false,
            'default' => 'فروشگاه آنلاین با بهترین کیفیت و قیمت',
        ],
        'footer_copyright' => [
            'label' => 'متن کپی‌رایت فوتر',
            'type' => 'text',
            'tab' => 'footer',
            'required' => false,
            'default' => '© تمامی حقوق محفوظ است.',
        ],
        'footer_newsletter_title' => [
            'label' => 'عنوان خبرنامه در فوتر',
            'type' => 'text',
            'tab' => 'footer',
            'required' => false,
            'default' => 'خبرنامه',
        ],

        // ==================== پیامک (کاوه‌نگار) ====================
        'kavenegar_key' => [
            'label' => 'کلید API کاوه‌نگار',
            'type' => 'text',
            'tab' => 'sms',
            'required' => false,
            'guide' => 'از پنل کاوه‌نگار → «تنظیمات» → «کلید API» کپی کنید. بدون این کلید هیچ پیامکی ارسال نمی‌شود.',
        ],
        'kavenegar_sender' => [
            'label' => 'شماره خط خدماتی (ارسال‌کننده)',
            'type' => 'text',
            'tab' => 'sms',
            'required' => false,
            'guide' => 'شماره خط خدماتی/اختصاصی شما؛ برای ارسال الگودار (lookup — مثل OTP) الزامی نیست.',
        ],
        'kavenegar_number_test' => [
            'label' => 'شماره تست خط',
            'type' => 'text',
            'tab' => 'sms',
            'required' => false,
            'guide' => 'شماره‌ای که پیامک تست اعتبارسنجی خط به آن ارسال می‌شود.',
        ],
        'otp_template' => [
            'label' => 'نام الگوی کد تایید (OTP)',
            'type' => 'text',
            'tab' => 'sms',
            'required' => false,
            'default' => 'verify',
            'guide' => 'نام الگوی تعریف‌شده در پنل کاوه‌نگار → «اعتبارسنجی» → «تعریف الگوی اعتبارسنجی». متن الگو باید شامل %token باشد.',
        ],

        // ==================== جشن و ظاهر سایت ====================
        'festive_mode' => [
            'label' => 'حالت جشن (بارش کانفتی روی هیرو)',
            'type' => 'boolean',
            'tab' => 'appearance',
            'required' => false,
            'default' => false,
            'guide' => 'برای بلک فرایدی، نوروز و جشن‌ها بارش کانفتی روی اسلایدر هیرو فعال می‌شود.',
        ],
        'festive_style' => [
            'label' => 'سبک بارش جشن',
            'type' => 'select',
            'tab' => 'appearance',
            'required' => false,
            'default' => 'gold',
            'preview' => true,
            'options' => [
                'gold' => 'طلایی',
                'colorful' => 'رنگارنگ',
                'snow' => 'برفی نوروزی',
                'hearts' => 'قلبی (عاشقانه)',
            ],
            'guide' => 'نوع بارش روی هیرو: طلایی (بلک فرایدی/جشن‌ها)، رنگارنگ (تولد/مراسم) یا برفی (نوروز/زمستان).',
        ],
        'festive_badge_text' => [
            'label' => 'متن بنر/بج تخفیف جشن',
            'type' => 'text',
            'tab' => 'appearance',
            'required' => false,
            'default' => 'تخفیف جشن',
            'guide' => 'این متن وقتی حالت جشن فعال است روی هیرو نمایش داده می‌شود. اگر خالی بماند، متن پیش‌فرضِ زبان جاری سایت نمایش داده می‌شود.',
        ],

        // ==================== اعلانات هوشمند ====================
        'allow_open_requests' => [
            'label' => 'اجازه درخواست‌های باز',
            'type' => 'boolean',
            'tab' => 'smart_notifications',
            'required' => false,
            'default' => false,
            'guide' => 'فعال‌سازی این گزینه به کاربران اجازه می‌دهد درخواست‌های باز و آزاد ثبت کنند. (فعلاً غیرفعال — برای پیاده‌سازی آینده)',
        ],
        'enable_ai' => [
            'label' => 'فعال‌سازی هوش مصنوعی',
            'type' => 'boolean',
            'tab' => 'smart_notifications',
            'required' => false,
            'default' => false,
            'guide' => 'فعال‌سازی قابلیت‌های هوش مصنوعی در سایت. (فعلاً غیرفعال — برای پیاده‌سازی آینده)',
        ],

    ],
];
