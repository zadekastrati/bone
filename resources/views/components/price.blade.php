@props(['amount'])

{{ app(\App\Services\CurrencyService::class)->format((float) $amount) }}
