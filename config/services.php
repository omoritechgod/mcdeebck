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
    'africastalking' => [
        'username' => env('AFRICASTALKING_USERNAME'),
        'key'      => env('AFRICASTALKING_API_KEY'),
        'from'     => env('AFRICASTALKING_FROM', ''),
        ],


    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'paystack' => [
        'secret' => env('PAYSTACK_SECRET_KEY'),
        'public' => env('PAYSTACK_PUBLIC_KEY'),
        'payment_url' => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
    ],
    'flutterwave' => [
        'secret' => env('FLW_SECRET_KEY'),
        'public' => env('FLW_PUBLIC_KEY'),
        'payment_url' => env('FLW_BASE_URL', 'https://api.flutterwave.com/v3'),
        'secret_hash' => env('FLW_SECRET_HASH'),
    ],




];
