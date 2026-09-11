<?php

return [
    'order' => [
        'created' => [
            'title' => 'Your order has been placed',
            'body'  => 'Order #:order_id has been placed for :amount IRT.',
            'label' => 'Order placed',
        ],
        'paid' => [
            'title' => 'Order payment confirmed',
            'body'  => 'Payment for order #:order_id in the amount of :amount IRT was completed successfully.',
            'label' => 'Order payment',
        ],
        'shipped' => [
            'title' => 'Shipping status updated',
            'body'  => 'Shipping status for order #:order_id has changed to ":status".',
            'label' => 'Order shipment',
        ],
        'cancelled' => [
            'title' => 'Your order has been cancelled',
            'body'  => 'Order #:order_id has been cancelled.',
            'label' => 'Order cancelled',
        ],
        'new' => [
            'title' => 'New order placed',
            'body'  => 'Order #:order_id from :user_name for :amount IRT has been placed.',
            'label' => 'New order',
        ],
        'paid_admin' => [
            'title' => 'Order payment confirmed',
            'body'  => 'Payment for order #:order_id from :user_name in the amount of :amount IRT has been confirmed.',
            'label' => 'Payment confirmed',
        ],
        'card_to_card' => [
            'title' => 'New bank transfer receipt received',
            'body'  => ':user_name submitted the payment receipt for order #:order_id (tracking: :tracking_code) for :amount IRT.',
            'label' => 'Transfer receipt',
        ],
    ],
    'discount' => [
        'welcome' => [
            'title' => 'Welcome discount code',
            'body'  => 'Congratulations! A 10% discount code has been created for your first purchase.',
            'label' => 'Welcome discount',
        ],
        'expiring' => [
            'title' => 'Your discount code is expiring soon',
            'body'  => 'Your discount code will expire soon. Make sure to use it.',
            'label' => 'Discount expiring',
        ],
    ],
    'review' => [
        'new' => [
            'title' => 'New review submitted',
            'body'  => 'A new review from :reviewer for the product ":product_name" has been submitted.',
            'label' => 'New review',
        ],
        'approved' => [
            'title' => 'Your review has been approved',
            'body'  => 'Your review for the product ":product_name" has been approved and published.',
            'label' => 'Review approved',
        ],
        'replied' => [
            'title' => 'New reply to your review',
            'body'  => 'A new reply has been posted to your review of the product ":product_name".',
            'label' => 'Review reply',
        ],
    ],
    'contact' => [
        'new' => [
            'title' => 'New contact message',
            'body'  => 'A new message from :name (:email) has been received.',
            'label' => 'Contact message',
        ],
    ],
];
