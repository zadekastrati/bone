<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/jpeg" href="{{ asset('logo.jpeg') }}">
    <title>Invoice {{ $order->order_number }} - {{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>

<body class="min-h-full bg-zinc-100/70 font-sans text-base leading-relaxed text-ink-900 antialiased">
    <div class="mx-auto max-w-3xl px-6 py-10">
        <div class="no-print mb-6 flex justify-end">
            <button type="button" onclick="window.print()" class="btn-primary">Print invoice</button>
        </div>

        <div class="rounded-2xl border border-zinc-200/80 bg-white p-8 shadow-soft sm:p-10">
            <div class="flex flex-wrap items-start justify-between gap-6 border-b border-ink-200/70 pb-6">
                <div>
                    <img src="{{ asset('logo.png') }}" alt="{{ config('app.name') }}" class="h-6 w-auto">
                    <p class="mt-3 text-xs font-bold uppercase tracking-mega text-ink-500">Invoice</p>
                </div>
                <div class="text-right text-sm text-ink-600">
                    <p class="font-mono text-base font-semibold text-ink-950">{{ $order->order_number }}</p>
                    <p class="mt-1">{{ $order->created_at->format('M j, Y H:i') }}</p>
                    <p class="mt-2">
                        <x-admin.badge :tone="$order->status->tone()">{{ $order->status->label() }}</x-admin.badge>
                        <x-admin.badge :tone="$order->payment_status->tone()">{{ $order->payment_status->label() }}</x-admin.badge>
                    </p>
                </div>
            </div>

            <div class="mt-6 grid gap-6 sm:grid-cols-2">
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-mega text-ink-500">Billed to</h2>
                    <p class="mt-2 text-sm leading-relaxed text-ink-700">
                        @if ($order->user)
                            {{ $order->user->name }}<br>
                            {{ $order->user->email }}
                        @else
                            {{ trim($order->shipping_first_name.' '.$order->shipping_last_name) }}<br>
                            Guest checkout
                        @endif
                    </p>
                </div>
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-mega text-ink-500">Ship to</h2>
                    <p class="mt-2 text-sm leading-relaxed text-ink-700">
                        {{ $order->shipping_first_name }} {{ $order->shipping_last_name }}<br>
                        {{ $order->shipping_street }}<br>
                        @if ($order->shipping_building)
                            {{ $order->shipping_building }}<br>
                        @endif
                        {{ $order->shipping_city }}@if ($order->shipping_region), {{ $order->shipping_region }}@endif
                        @if ($order->shipping_postal_code) {{ $order->shipping_postal_code }}@endif<br>
                        {{ config('store.shipping.countries.'.$order->shipping_country.'.label') ?? $order->shipping_country }} ({{ $order->shipping_country }})<br>
                        <span class="text-ink-500">Phone: {{ $order->shipping_phone }}</span>
                    </p>
                </div>
            </div>

            <table class="mt-8 w-full border-collapse text-left text-sm">
                <thead>
                    <tr class="border-b border-ink-200/80 text-xs font-semibold uppercase tracking-wider text-ink-500">
                        <th class="py-2">Item</th>
                        <th class="py-2">Qty</th>
                        <th class="py-2 text-right">Unit price</th>
                        <th class="py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr class="border-b border-ink-100/80">
                            <td class="py-3">
                                <p class="font-semibold text-ink-950">{{ $item->product_name }}</p>
                                <p class="text-xs text-ink-500">{{ $item->color }} · {{ $item->size }} @if ($item->sku) · {{ $item->sku }} @endif</p>
                            </td>
                            <td class="py-3 text-ink-600">{{ $item->quantity }}</td>
                            <td class="py-3 text-right text-ink-600">{{ config('store.currency_symbol') }}{{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="py-3 text-right font-semibold text-ink-900">{{ config('store.currency_symbol') }}{{ number_format((float) $item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4 flex justify-end">
                <div class="w-full max-w-xs space-y-2 text-sm">
                    <div class="flex justify-between text-ink-600">
                        <span>Subtotal</span>
                        <span>{{ config('store.currency_symbol') }}{{ number_format((float) $order->subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-ink-600">
                        <span>Shipping</span>
                        <span>{{ config('store.currency_symbol') }}{{ number_format((float) $order->shipping_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-ink-200/80 pt-2 text-base font-bold text-ink-950">
                        <span>Total</span>
                        <span>{{ config('store.currency_symbol') }}{{ number_format((float) $order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            @if ($order->payment_method === \App\Enums\PaymentMethod::BankTransfer)
                <div class="mt-8 rounded-2xl border border-accent-200/60 bg-accent-50/70 p-5 text-sm text-accent-950">
                    <p class="text-xs font-bold uppercase tracking-mega text-accent-800/80">Bank transfer reference</p>
                    <p class="mt-1 font-mono">{{ config('store.bank.reference_prefix') }}-{{ $order->order_number }}</p>
                </div>
            @endif
        </div>
    </div>
</body>

</html>
