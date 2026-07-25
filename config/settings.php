<?php

return [
    'general' => [
        'label' => 'تنظیمات عمومی',
        'fields' => [
            'site_name' => [
                'type' => 'text',
                'label' => 'نام سایت',
                'default' => 'فروشگاه شاپورا',
                'validation' => 'required|string|max:100',
            ],
            'site_logo' => [
                'type' => 'file',
                'label' => 'لوگوی سایت',
                'default' => null,
                'validation' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'options' => ['width' => 200, 'height' => 80], // برای نمایش در فرم
            ],
            'site_description' => [
                'type' => 'textarea',
                'label' => 'توضیحات کوتاه سایت (Meta Description)',
                'default' => '',
                'validation' => 'nullable|string|max:500',
            ],
        ],
    ],
    'payment' => [
        'label' => 'تنظیمات پرداخت',
        'fields' => [
            'zarinpal_merchant_id' => [
                'type' => 'text',
                'label' => 'مرچنت کد زرین‌پال',
                'default' => '',
                'validation' => 'required|string|size:36',
            ],
            'is_payment_active' => [
                'type' => 'select',
                'label' => 'وضعیت درگاه پرداخت',
                'default' => '1',
                'options' => ['1' => 'فعال', '0' => 'غیرفعال'],
                'validation' => 'required|in:0,1',
            ],
        ],
    ],
];
