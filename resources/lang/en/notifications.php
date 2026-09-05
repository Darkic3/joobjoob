<?php

return [
    'order' => [
        'created' => [
            'title' => 'Your order has been placed',
            'body'  => 'Order #:order_id has been placed for :amount IRT.',
        ],
        'paid' => [
            'title' => 'Order payment confirmed',
            'body'  => 'Payment for order #:order_id in the amount of :amount IRT was completed successfully.',
        ],
        'shipped' => [
            'title' => 'Shipping status updated',
            'body'  => 'Shipping status for order #:order_id has changed to ":status".',
        ],
        'cancelled' => [
            'title' => 'Your order has been cancelled',
            'body'  => 'Order #:order_id has been cancelled.',
        ],
        'new' => [
            'title' => 'New order placed',
            'body'  => 'Order #:order_id from :user_name for :amount IRT has been placed.',
        ],
        'paid_admin' => [
            'title' => 'Order payment confirmed',
            'body'  => 'Payment for order #:order_id from :user_name in the amount of :amount IRT has been confirmed.',
        ],
        'card_to_card' => [
            'title' => 'New bank transfer receipt received',
            'body'  => ':user_name submitted the payment receipt for order #:order_id (tracking: :tracking_code) for :amount IRT.',
        ],
    ],
    'discount' => [
        'welcome' => [
            'title' => 'Welcome discount code',
            'body'  => 'Congratulations! A 10% discount code has been created for your first purchase.',
        ],
        'expiring' => [
            'title' => 'Your discount code is expiring soon',
            'body'  => 'Your discount code will expire soon. Make sure to use it.',
        ],
    ],
    'review' => [
        'new' => [
            'title' => 'New review submitted',
            'body'  => 'A new review from :reviewer for the product ":product_name" has been submitted.',
        ],
    ],
    'contact' => [
        'new' => [
            'title' => 'New contact message',
            'body'  => 'A new message from :name (:email) has been received.',
        ],
    ],
];
