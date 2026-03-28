@extends('layouts.app')

@section('title', 'Cart')

@section('content')
    <x-page-header title="Cart" subtitle="Review items before checkout. Prices reflect the catalog at the time you added each line.">
        @guest
            <a href="{{ route('login') }}" class="btn-primary">Log in to checkout</a>
        @else
            @if ($lines->isNotEmpty())
                <a href="{{ route('checkout.create') }}" class="btn-primary">Checkout</a>
            @endif
        @endguest
    </x-page-header>

    @if ($lines->isEmpty())
        <p class="mt-10 text-ink-600">Your cart is empty.</p>
        <a href="{{ route('shop.index') }}" class="btn-secondary mt-6 inline-flex">Continue shopping</a>
    @else
        <div class="mt-10 overflow-hidden rounded-3xl border border-ink-200/60 bg-white/95 shadow-soft ring-1 ring-ink-950/[0.03]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-200 text-left text-sm">
                    <thead class="bg-ink-50/90">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold text-ink-700">Product</th>
                            <th class="px-5 py-3.5 font-semibold text-ink-700">Variant</th>
                            <th class="px-5 py-3.5 font-semibold text-ink-700">Price</th>
                            <th class="px-5 py-3.5 font-semibold text-ink-700">Qty</th>
                            <th class="px-5 py-3.5 font-semibold text-ink-700">Line</th>
                            <th class="px-5 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @foreach ($lines as $line)
                            @php
                                $v = $line['variant'];
                                $p = $v->product;
                            @endphp
                            <tr>
                                <td class="px-5 py-4">
                                    <a href="{{ route('shop.product', [$p->category, $p]) }}" class="font-medium text-ink-950 hover:text-accent-700">{{ $p->name }}</a>
                                </td>
                                <td class="px-5 py-4 text-ink-600">{{ $v->color }} · {{ $v->size }}</td>
                                <td class="px-5 py-4">{{ config('store.currency_symbol') }}{{ number_format((float) $p->price, 2) }}</td>
                                <td class="px-5 py-4">
                                    <form method="POST" action="{{ route('cart.update', $v->id) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="0" max="99" class="form-input w-20 py-1.5 text-sm">
                                        <button type="submit" class="link-brand text-xs">Update</button>
                                    </form>
                                </td>
                                <td class="px-5 py-4 font-semibold text-ink-900">{{ config('store.currency_symbol') }}{{ number_format((float) $line['line_total'], 2) }}</td>
                                <td class="px-5 py-4 text-right">
                                    <form method="POST" action="{{ route('cart.destroy', $v->id) }}" onsubmit="return confirm('Remove this item?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 flex flex-col items-end gap-4">
            <p class="text-lg font-semibold text-ink-950">
                Subtotal:
                <span class="text-accent-700">{{ config('store.currency_symbol') }}{{ number_format((float) $subtotal, 2) }}</span>
            </p>
            @guest
                <p class="max-w-md text-right text-sm text-ink-600">Log in or register to enter shipping details and place your order.</p>
                <div class="flex flex-wrap justify-end gap-3">
                    <a href="{{ route('login') }}" class="btn-primary">Log in</a>
                    <a href="{{ route('register') }}" class="btn-secondary">Register</a>
                </div>
            @else
                <a href="{{ route('checkout.create') }}" class="btn-primary px-10 py-3">Proceed to checkout</a>
            @endguest
        </div>
    @endif
@endsection
