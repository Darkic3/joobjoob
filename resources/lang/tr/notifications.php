<?php

return [
    'order' => [
        'created' => [
            'title' => 'Siparişiniz oluşturuldu',
            'body'  => ':order_id numaralı sipariş :amount IRT için oluşturuldu.',
            'label' => 'Sipariş oluşturma',
        ],
        'paid' => [
            'title' => 'Sipariş ödemesi onaylandı',
            'body'  => ':order_id numaralı sipariş için :amount IRT ödeme başarıyla tamamlandı.',
            'label' => 'Sipariş ödemesi',
        ],
        'shipped' => [
            'title' => 'Kargo durumu güncellendi',
            'body'  => ':order_id siparişinin kargo durumu ":status" olarak değiştirildi.',
            'label' => 'Sipariş kargosu',
        ],
        'cancelled' => [
            'title' => 'Siparişiniz iptal edildi',
            'body'  => ':order_id numaralı sipariş iptal edildi.',
            'label' => 'Sipariş iptali',
        ],
        'new' => [
            'title' => 'Yeni sipariş oluşturuldu',
            'body'  => ':user_name tarafından :amount IRT için :order_id numaralı sipariş oluşturuldu.',
            'label' => 'Yeni sipariş',
        ],
        'paid_admin' => [
            'title' => 'Sipariş ödemesi onaylandı',
            'body'  => ':user_name kullanıcısının :order_id siparişi için :amount IRT ödeme onaylandı.',
            'label' => 'Ödeme onayı',
        ],
        'card_to_card' => [
            'title' => 'Yeni havale/EFT makbuzu alındı',
            'body'  => ':user_name, :order_id siparişi (:tracking_code) için :amount IRT ödeme makbuzusunu gönderdi.',
            'label' => 'Havale makbuzu',
        ],
    ],
    'discount' => [
        'welcome' => [
            'title' => 'Hoş geldin indirim kodunu',
            'body'  => 'Tebrikler! İlk alışverişiniz için %10 indirim kodu oluşturuldu.',
            'label' => 'Hoş geldin indirimi',
        ],
        'expiring' => [
            'title' => 'İndirim kodunuzun süresi yakında dolacak',
            'body'  => 'İndirim kodunuzun süresi yakında dolacak. Mutlaka kullanın.',
            'label' => 'İndirim süresi',
        ],
    ],
    'review' => [
        'new' => [
            'title' => 'Yeni yorum girdi',
            'body'  => ':reviewer tarafından ":product_name" ürününe yeni bir yorum gönderildi.',
            'label' => 'Yeni yorum',
        ],
        'approved' => [
            'title' => 'Yorumunuz onaylandı',
            'body'  => '":product_name" ürünü için yorumunuz onaylandı ve yayınlandı.',
            'label' => 'Yorum onayı',
        ],
        'replied' => [
            'title' => 'Yorumunuza yeni yanıt',
            'body'  => '":product_name" ürünü hakkındaki yorumunuza yeni bir yanıt gönderildi.',
            'label' => 'Yorum yanıtı',
        ],
    ],
    'contact' => [
        'new' => [
            'title' => 'Yeni iletişim mesajı',
            'body'  => ':name (:email) adresinden yeni bir mesaj alındı.',
            'label' => 'İletişim mesajı',
        ],
    ],
];
