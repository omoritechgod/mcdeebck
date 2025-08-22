<?php

return [
    'default' => env('MAIL_MAILER', 'smtp'),

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'mail.mdoilandgas.com'),
            'port' => env('MAIL_PORT', 465),
            'encryption' => 'ssl',
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'auth_mode' => null,
        ],
        'log' => ['transport' => 'log'],
        'array' => ['transport' => 'array'],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'info@mdoilandgas.com'),
        'name' => env('MAIL_FROM_NAME', 'MC Dee Platform'),
    ],
];
