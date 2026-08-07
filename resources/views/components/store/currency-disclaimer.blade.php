@props(['amount' => null])

@php
    $svc = app(\App\Services\CurrencyService::class);
    $currency = $svc->currentCurrency();
@endphp

@if ($currency !== 'EUR')
    <p {{ $attributes->merge(['class' => 'text-xs text-ink-500']) }}>
        Prices are set in euros (EUR) — the {{ $currency }} amount{{ $amount !== null ? ' above' : '' }} is a converted estimate for reference.
        @if ($amount !== null)
            You're charged <strong>{{ $svc->format((float) $amount, 'EUR') }}</strong>.
        @endif
    </p>
@endif
