<?php

return [
    'order' => [
        'created' => [
            'title' => 'تم إبرام طلبك',
            'body'  => 'تم إبرام الطلب رقم :order_id بمبلغ :amount IRT.',
        ],
        'paid' => [
            'title' => 'تم تأكيد دفع الطلب',
            'body'  => 'تمت عملية دفع الطلب رقم :order_id بنجاح بمبلغ :amount IRT.',
        ],
        'shipped' => [
            'title' => 'تم تحديث حالة الشحن',
            'body'  => 'تم تغيير حالة شحن الطلب رقم :order_id إلى ":status".',
        ],
        'cancelled' => [
            'title' => 'تم إلغاء طلبك',
            'body'  => 'تم إلغاء الطلب رقم :order_id.',
        ],
        'new' => [
            'title' => 'طلب جديد تم إبرامه',
            'body'  => 'تم إبرام الطلب رقم :order_id من :user_name بمبلغ :amount IRT.',
        ],
        'paid_admin' => [
            'title' => 'تم تأكيد دفع الطلب',
            'body'  => 'تم تأكيد دفع الطلب رقم :order_id من :user_name بمبلغ :amount IRT.',
        ],
        'card_to_card' => [
            'title' => 'تم استقبال إيصال تحويل بنكي جديد',
            'body'  => 'أرسل :user_name إيصال دفع الطلب رقم :order_id (رمز التتبع: :tracking_code) بمبلغ :amount IRT.',
        ],
    ],
    'discount' => [
        'welcome' => [
            'title' => 'رمز خصم الترحيب',
            'body'  => 'تهانينا! تم إنشاء رمز خصم بنسبة ١٠٪ لطلبك الأول.',
        ],
        'expiring' => [
            'title' => 'رمز الخصم ستنتهي قريباً',
            'body'  => 'رمز الخصم ستنتهي صلاحيته قريباً. يرجى استخدامه.',
        ],
    ],
    'review' => [
        'new' => [
            'title' => 'تم إرسال مراجعة جديدة',
            'body'  => 'تم إرسال مراجعة جديدة من :reviewer للمنتج ":product_name".',
        ],
    ],
    'contact' => [
        'new' => [
            'title' => 'رسالة اتصال جديدة',
            'body'  => 'تم استقبال رسالة جديدة من :name (:email).',
        ],
    ],
];
