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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Quipu/ProCredit "3DSS2" card payment gateway. Auth is mutual TLS: the
    // cert's Common Name must equal merchant_id. Never point these at real
    // production credentials outside a dedicated production environment.
    'quipu' => [
        'merchant_id' => env('QUIPU_MERCHANT_ID'),
        'order_endpoint' => env('QUIPU_ORDER_ENDPOINT'),
        'cert_path' => env('QUIPU_CERT_PATH'),
        'key_path' => env('QUIPU_KEY_PATH'),
        'ca_path' => env('QUIPU_CA_PATH'),
    ],

];
