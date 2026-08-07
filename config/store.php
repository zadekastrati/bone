<?php

return [

    'currency' => env('STORE_CURRENCY', 'EUR'),
    'currency_symbol' => env('STORE_CURRENCY_SYMBOL', '€'),

    /*
    | All product/order amounts are stored in the base currency above (EUR).
    | These are only used to convert and format amounts for display based on
    | the shopper's selected country — the stored/charged amount never changes.
    |
    | Rates are EUR -> target currency (multiply a EUR amount by "rate").
    |
    | MKD: the Macedonian denar has been de facto pegged to the euro by the
    | National Bank of North Macedonia since 2002, held within ~1% of
    | 61.5 MKD/EUR for over two decades — 61.5 is a safe hardcoded default.
    |
    | ALL: the Albanian lek floats freely and has moved a lot (~123 in 2022
    | down to ~93-94 as of Aug 2026 amid a multi-year lek appreciation), so
    | the default below WILL drift out of date. Override it via STORE_RATE_ALL
    | in .env, and for a production store, replace the static config with a
    | scheduled job that refreshes it from a rates API (e.g. exchangerate.host)
    | instead of relying on a hardcoded number.
    */
    'currencies' => [
        'EUR' => [
            'label' => 'Euro',
            'symbol' => '€',
            'decimals' => 2,
            'decimal_separator' => '.',
            'thousands_separator' => ',',
            'symbol_position' => 'before',
            'rate' => 1,
        ],
        'ALL' => [
            'label' => 'Albanian Lek',
            'symbol' => 'L',
            'decimals' => 0,
            'decimal_separator' => ',',
            'thousands_separator' => '.',
            'symbol_position' => 'after',
            'rate' => env('STORE_RATE_ALL', 93.5),
        ],
        'MKD' => [
            'label' => 'Macedonian Denar',
            'symbol' => 'ден',
            'decimals' => 0,
            'decimal_separator' => ',',
            'thousands_separator' => '.',
            'symbol_position' => 'after',
            'rate' => env('STORE_RATE_MKD', 61.5),
        ],
    ],

    /*
    | Only these destinations are available at checkout. "amount" (shipping
    | fee) stays in the base store currency (EUR) — it's what's actually
    | charged. "currency" is the currency prices are displayed in for that
    | country and is unrelated to which country an order ships to.
    | XK = Kosovo, AL = Albania, MK = North Macedonia
    */
    'shipping' => [
        'countries' => [
            'XK' => ['label' => 'Kosovo', 'amount' => '2.50', 'currency' => 'EUR'],
            'AL' => ['label' => 'Albania', 'amount' => '6.00', 'currency' => 'ALL'],
            'MK' => ['label' => 'Macedonia', 'amount' => '6.00', 'currency' => 'MKD'],
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
