<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | ZarinPal Payment Gateway (https://www.zarinpal.com/docs/paymentGateway/connectToGateway)
    |--------------------------------------------------------------------------
    |
    | مبالغ فروشگاه به تومان ذخیره می‌شوند؛ هنگام ارسال به زرین‌پال به ریال
    | تبدیل می‌شوند (×۱۰) و currency=IRR ارسال می‌شود.
    | حالت sandbox برای تست بدون پرداخت واقعی است.
    |
    */
    'zarinpal' => [
        'merchant_id' => env('ZARINPAL_MERCHANT_ID'),
        'sandbox' => env('ZARINPAL_SANDBOX', false),
        'currency' => env('ZARINPAL_CURRENCY', 'IRR'),
        'sandbox_merchant_id' => env('ZARINPAL_SANDBOX_MERCHANT_ID', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'),
    ],

];
