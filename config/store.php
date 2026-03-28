<?php

return [

    'currency' => env('STORE_CURRENCY', 'EUR'),
    'currency_symbol' => env('STORE_CURRENCY_SYMBOL', '€'),

    /*
    | Only these destinations are available at checkout. Amounts in store currency (default EUR).
    | XK = Kosovo, AL = Albania, MK = North Macedonia
    */
    'shipping' => [
        'countries' => [
            'XK' => ['label' => 'Kosovo', 'amount' => '2.50'],
            'AL' => ['label' => 'Albania', 'amount' => '6.00'],
            'MK' => ['label' => 'Macedonia', 'amount' => '6.00'],
        ],
        'default_country' => env('STORE_DEFAULT_COUNTRY', 'XK'),
    ],

    'bank' => [
        'account_name' => env('STORE_BANK_ACCOUNT_NAME', ''),
        'iban' => env('STORE_BANK_IBAN', ''),
        'bic_swift' => env('STORE_BANK_BIC', ''),
        'reference_prefix' => env('STORE_BANK_REFERENCE_PREFIX', 'BONE'),
        'instructions' => env('STORE_BANK_INSTRUCTIONS', 'Use your order number as the payment reference. Transfer may take 1–2 business days.'),
    ],

];
