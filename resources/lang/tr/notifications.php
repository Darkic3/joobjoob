<?php

return [
    'order' => [
        'created' => [
            'title' => 'Siparişiniz oluşturuldu',
            'body'  => ':order_id numaralı sipariş :amount IRT için oluşturuldu.',
        ],
        'paid' => [
            'title' => 'Sipariş ödemesi onaylandı',
            'body'  => ':order_id numaralı sipariş için :amount IRT ödeme başarıyla tamamlandı.',
        ],
        'shipped' => [
            'title' => 'Kargo durumu güncellendi',
            'body'  => ':order_id siparişinin kargo durumu ":status" olarak değiştirildi.',
        ],
        'cancelled' => [
            'title' => 'Siparişiniz iptal edildi',
            'body'  => ':order_id numaralı sipariş iptal edildi.',
        ],
        'new' => [
            'title' => 'Yeni sipariş oluşturuldu',
            'body'  => ':user_name tarafından :amount IRT için :order_id numaralı sipariş oluşturuldu.',
        ],
        'paid_admin' => [
            'title' => 'Sipariş ödemesi onaylandı',
            'body'  => ':user_name kullanıcısının :order_id siparişi için :amount IRT ödeme onaylandı.',
        ],
        'card_to_card' => [
            'title' => 'Yeni havale/EFT makbuzu alındı',
            'body'  => ':user_name, :order_id siparişi (:tracking_code) için :amount IRT ödeme makbuzusunu gönderdi.',
        ],
    ],
    'discount' => [
        'welcome' => [
            'title' => 'Hoş geldin indirim kodunu',
            'body'  => 'Tebrikler! İlk alışverişiniz için %10 indirim kodu oluşturuldu.',
        ],
        'expiring' => [
            'title' => 'İndirim kodunuzun süresi yakında dolacak',
            'body'  => 'İndirim kodunuzun süresi yakında dolacak. Mutlaka kullanın.',
        ],
    ],
    'review' => [
        'new' => [
            'title' => 'Yeni yorum girdi',
            'body'  => ':reviewer tarafından ":product_name" ürününe yeni bir yorum gönderildi.',
        ],
    ],
    'contact' => [
        'new' => [
            'title' => 'Yeni iletişim mesajı',
            'body'  => ':name (:email) adresinden yeni bir mesaj alındı.',
        ],
    ],
];
