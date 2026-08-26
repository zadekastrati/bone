@extends('layouts.app')

@section('title', __('Order :number', ['number' => $order->order_number]))
@section('noindex', 'true')

@section('content')
    <nav class="crumbs" aria-label="Breadcrumb">
        <a href="{{ route('orders.index') }}">{{ __('Orders') }}</a>
        <span class="mx-1.5 text-ink-300">/</span>
        <span class="text-ink-800">{{ $order->order_number }}</span>
    </nav>

    <x-page-header :title="__('Order :number', ['number' => $order->order_number])" :subtitle="__('Save this page for your records. Contact support if anything looks wrong.')" />

    <div class="mt-10 grid gap-10 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="panel">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-ink-950">{{ __('Items') }}</h2>
                <ul class="mt-4 divide-y divide-ink-100 text-sm">
                    @foreach ($order->items as $item)
                        @php
                            $thumb = $item->variant?->product?->thumbnailImage();
                        @endphp
                        <li class="flex flex-wrap items-center justify-between gap-3 py-4">
                            <div class="flex items-center gap-4">
                                <x-product-image-thumb :path="$thumb?->path" :thumb-src="$thumb?->thumbUrl()" :alt="$item->product_name" size="sm" />
                                <div>
                                    <p class="font-semibold text-ink-950">{{ $item->product_name }}</p>
                                    <p class="text-xs text-ink-500">{{ $item->color }} · {{ $item->size }} @if ($item->sku) · {{ $item->sku }} @endif</p>
                                </div>
                            </div>
                            <div class="text-right text-sm">
                                <p class="text-ink-600">× {{ $item->quantity }}</p>
                                <p class="font-semibold text-ink-900"><x-price :amount="$item->line_total" /></p>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4 border-t border-ink-200/80 pt-4 text-sm">
                    <div class="flex justify-between text-ink-600">
                        <span>{{ __('Subtotal') }}</span>
                        <span><x-price :amount="$order->subtotal" /></span>
                    </div>
                    <div class="mt-2 flex justify-between text-ink-600">
                        <span>{{ __('Shipping') }}</span>
                        <span><x-price :amount="$order->shipping_amount" /></span>
                    </div>
                    <div class="mt-4 flex justify-between text-base font-bold text-ink-950">
                        <span>{{ __('Total') }}</span>
                        <span><x-price :amount="$order->total" /></span>
                    </div>
                </div>
            </div>

            <div class="panel">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-ink-950">{{ __('Ship to') }}</h2>
                <p class="mt-3 text-sm leading-relaxed text-ink-700">
                    {{ $order->shipping_first_name }} {{ $order->shipping_last_name }}<br>
                    {{ $order->shipping_street }}<br>
                    @if ($order->shipping_building)
                        {{ $order->shipping_building }}<br>
                    @endif
                    {{ $order->shipping_city }}@if ($order->shipping_region), {{ $order->shipping_region }}@endif
                    @if ($order->shipping_postal_code) {{ $order->shipping_postal_code }}@endif<br>
                    {{ __(config('store.shipping.countries.'.$order->shipping_country.'.label') ?? $order->shipping_country) }} ({{ $order->shipping_country }})<br>
                    <span class="text-ink-500">{{ __('Phone:') }} {{ $order->shipping_phone }}</span>
                </p>
                @if ($order->shipping_delivery_notes)
                    <p class="mt-4 rounded-2xl bg-ink-50/90 p-4 text-sm text-ink-700">
                        <span class="text-xs font-bold uppercase tracking-mega text-ink-400">{{ __('Delivery instructions') }}</span><br>
                        {{ $order->shipping_delivery_notes }}
                    </p>
                @endif
                @if ($order->customer_notes)
                    <p class="mt-4 rounded-2xl border border-ink-100 bg-white p-4 text-sm text-ink-700">
                        <span class="text-xs font-bold uppercase tracking-mega text-ink-400">{{ __('Order notes') }}</span><br>
                        {{ $order->customer_notes }}
                    </p>
                @endif
            </div>
        </div>

        <aside class="space-y-6">
            <div class="border border-ink-200/50 bg-ink-50/60 p-6 shadow-soft sm:rounded-2xl">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-ink-950">{{ __('Status') }}</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-mega text-ink-400">{{ __('Fulfillment') }}</dt>
                        <dd class="mt-1 font-semibold text-ink-900">{{ $order->status->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-mega text-ink-400">{{ __('Payment') }}</dt>
                        <dd class="mt-1 font-semibold text-ink-900">{{ $order->payment_method->label() }} · {{ $order->payment_status->label() }}</dd>
                    </div>
                </dl>
            </div>

            @if ($order->payment_method === \App\Enums\PaymentMethod::BankTransfer)
                <div class="rounded-2xl border border-accent-200/60 bg-accent-50/90 p-6 shadow-soft">
                    <h2 class="font-display text-sm font-bold uppercase tracking-wide text-accent-950">{{ __('Bank transfer') }}</h2>
                    <p class="mt-3 text-sm leading-relaxed text-accent-950/90">{{ config('store.bank.instructions') }}</p>
                    <dl class="mt-4 space-y-2 text-sm text-accent-950">
                        @if (config('store.bank.account_name'))
                            <div><span class="font-semibold">{{ __('Beneficiary:') }}</span> {{ config('store.bank.account_name') }}</div>
                        @endif
                        @if (config('store.bank.iban'))
                            <div><span class="font-semibold">{{ __('IBAN:') }}</span> <span class="font-mono text-xs">{{ config('store.bank.iban') }}</span></div>
                        @endif
                        @if (config('store.bank.bic_swift'))
                            <div><span class="font-semibold">{{ __('BIC / SWIFT:') }}</span> <span class="font-mono text-xs">{{ config('store.bank.bic_swift') }}</span></div>
                        @endif
                        <div>
                            <span class="font-semibold">{{ __('Reference:') }}</span>
                            <span class="font-mono text-xs">{{ config('store.bank.reference_prefix') }}-{{ $order->order_number }}</span>
                        </div>
                    </dl>
                    @if ($order->payment_status !== \App\Enums\PaymentStatus::Paid)
                        <p class="mt-4 text-xs font-medium text-accent-900/80">{{ __('Your order stays in a pending payment state until we confirm the transfer.') }}</p>
                    @endif
                </div>
            @endif
        </aside>
    </div>

    <div class="mt-10">
        <a href="{{ route('shop.index') }}" class="btn-secondary">{{ __('Continue shopping') }}</a>
    </div>
@endsection
