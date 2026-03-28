@extends('layouts.admin')

@section('title', 'Order '.$order->order_number)

@section('content')
    <nav class="crumbs crumbs--admin" aria-label="Breadcrumb">
        <a href="{{ route('admin.orders.index') }}">Orders</a>
        <span class="mx-1.5 text-pink-300">/</span>
        <span class="text-ink-800">{{ $order->order_number }}</span>
    </nav>

    <x-page-header :title="'Order '.$order->order_number" :subtitle="'Customer: '.$order->user->email" />

    <div class="mt-10 grid gap-10 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="admin-panel p-6">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-ink-900">Line items</h2>
                <ul class="mt-4 divide-y divide-ink-100 text-sm">
                    @foreach ($order->items as $item)
                        <li class="flex flex-wrap items-center justify-between gap-3 py-4">
                            <div>
                                <p class="font-semibold text-ink-950">{{ $item->product_name }}</p>
                                <p class="text-xs text-ink-500">{{ $item->color }} · {{ $item->size }} @if ($item->sku) · {{ $item->sku }} @endif</p>
                            </div>
                            <div class="text-right text-sm">
                                <p class="text-ink-600">× {{ $item->quantity }}</p>
                                <p class="font-semibold text-ink-900">{{ config('store.currency_symbol') }}{{ number_format((float) $item->line_total, 2) }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4 border-t border-ink-200/80 pt-4 text-sm">
                    <div class="flex justify-between text-ink-600">
                        <span>Subtotal</span>
                        <span>{{ config('store.currency_symbol') }}{{ number_format((float) $order->subtotal, 2) }}</span>
                    </div>
                    <div class="mt-2 flex justify-between text-ink-600">
                        <span>Shipping</span>
                        <span>{{ config('store.currency_symbol') }}{{ number_format((float) $order->shipping_amount, 2) }}</span>
                    </div>
                    <div class="mt-4 flex justify-between text-base font-bold text-ink-950">
                        <span>Total</span>
                        <span>{{ config('store.currency_symbol') }}{{ number_format((float) $order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="admin-panel p-6">
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-ink-900">Shipping address</h2>
                <p class="mt-3 text-sm leading-relaxed text-ink-700">
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
                @if ($order->shipping_delivery_notes)
                    <p class="mt-4 rounded-2xl bg-amber-50/90 p-4 text-sm text-ink-800 ring-1 ring-amber-200/60">
                        <span class="text-xs font-bold uppercase tracking-mega text-amber-800/80">Delivery instructions</span><br>
                        {{ $order->shipping_delivery_notes }}
                    </p>
                @endif
                @if ($order->customer_notes)
                    <p class="mt-4 rounded-2xl bg-ink-50/90 p-4 text-sm text-ink-700">
                        <span class="text-xs font-bold uppercase tracking-mega text-ink-400">Customer note</span><br>
                        {{ $order->customer_notes }}
                    </p>
                @endif
            </div>
        </div>

        <aside>
            <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="admin-form-surface space-y-4">
                @csrf
                @method('PATCH')
                <h2 class="font-display text-sm font-bold uppercase tracking-wide text-ink-950">Admin</h2>
                <div>
                    <label for="status" class="form-label">Order status</label>
                    <select name="status" id="status" class="form-input" required>
                        @foreach (\App\Enums\OrderStatus::cases() as $st)
                            <option value="{{ $st->value }}" @selected(old('status', $order->status->value) === $st->value)>{{ $st->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="payment_status" class="form-label">Payment status</label>
                    <select name="payment_status" id="payment_status" class="form-input" required>
                        @foreach (\App\Enums\PaymentStatus::cases() as $ps)
                            <option value="{{ $ps->value }}" @selected(old('payment_status', $order->payment_status->value) === $ps->value)>{{ $ps->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tracking_number" class="form-label">Tracking number</label>
                    <input type="text" name="tracking_number" id="tracking_number" value="{{ old('tracking_number', $order->tracking_number) }}" class="form-input">
                </div>
                <div>
                    <label for="shipped_at" class="form-label">Shipped at</label>
                    <input type="datetime-local" name="shipped_at" id="shipped_at" value="{{ old('shipped_at', $order->shipped_at?->format('Y-m-d\TH:i')) }}" class="form-input">
                </div>
                <div>
                    <label for="admin_notes" class="form-label">Internal notes</label>
                    <textarea name="admin_notes" id="admin_notes" rows="3" class="form-input">{{ old('admin_notes', $order->admin_notes) }}</textarea>
                </div>
                <button type="submit" class="btn-primary w-full justify-center">Save</button>
            </form>
        </aside>
    </div>
@endsection
