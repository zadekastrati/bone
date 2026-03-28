@extends('layouts.app')

@section('title', 'Cart')

@section('content')
    <x-page-header title="Cart" subtitle="Review items before checkout. Prices reflect the catalog at the time you added each line.">
        @guest
            <a href="{{ route('login') }}" class="btn-primary">Log in to checkout</a>
        @else
            @if ($lines->isNotEmpty())
                @if (auth()->user()->hasVerifiedEmail())
                    <a href="{{ route('checkout.create') }}" class="btn-primary">Checkout</a>
                @else
                    <a href="{{ route('verification.notice') }}" class="btn-primary">Verify email to checkout</a>
                @endif
            @endif
        @endguest
    </x-page-header>

    @if ($lines->isEmpty())
        <div class="surface-muted mt-10 flex flex-col items-center px-8 py-16 text-center sm:py-20">
            <div class="flex size-16 items-center justify-center rounded-2xl border border-ink-200/60 bg-white shadow-soft">
                <svg class="size-8 text-ink-400" fill="none" viewBox="0 0 24 24" stroke-width="1.25" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.25 10.5a3.75 3.75 0 1 1 7.5 0" />
                </svg>
            </div>
            <p class="font-display mt-6 text-xl font-bold uppercase tracking-wide text-ink-950">Your bag is empty</p>
            <p class="mt-2 max-w-sm text-sm text-ink-600 text-pretty">Add pieces from the shop — they&apos;ll show up here with size and colour.</p>
            <a href="{{ route('shop.index') }}" class="btn-primary mt-8 inline-flex px-10">Continue shopping</a>
        </div>
    @else
        <div class="table-shell mt-10">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Variant</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Line</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lines as $line)
                            @php
                                $v = $line['variant'];
                                $p = $v->product;
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('shop.product', [$p->category, $p]) }}" class="font-medium text-ink-950 hover:text-accent-700">{{ $p->name }}</a>
                                </td>
                                <td class="text-ink-600">{{ $v->color }} · {{ $v->size }}</td>
                                <td class="tabular-nums">{{ config('store.currency_symbol') }}{{ number_format((float) $p->price, 2) }}</td>
                                <td>
                                    <form method="POST" action="{{ route('cart.update', $v->id) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="0" max="99" class="form-input w-20 py-1.5 text-sm">
                                        <button type="submit" class="link-brand text-xs">Update</button>
                                    </form>
                                </td>
                                <td class="tabular-nums font-semibold text-ink-900">{{ config('store.currency_symbol') }}{{ number_format((float) $line['line_total'], 2) }}</td>
                                <td class="text-right">
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

        <div class="surface-muted mt-10 flex max-w-md flex-col items-end gap-4 sm:ml-auto">
            <p class="text-lg font-semibold text-ink-950">
                Subtotal
                <span class="ml-2 text-accent-700">{{ config('store.currency_symbol') }}{{ number_format((float) $subtotal, 2) }}</span>
            </p>
            @guest
                <p class="max-w-md text-right text-sm text-ink-600">Log in or register to enter shipping details and place your order.</p>
                <div class="flex flex-wrap justify-end gap-3">
                    <a href="{{ route('login') }}" class="btn-primary">Log in</a>
                    <a href="{{ route('register') }}" class="btn-secondary">Register</a>
                </div>
            @else
                @if (auth()->user()->hasVerifiedEmail())
                    <a href="{{ route('checkout.create') }}" class="btn-primary px-10 py-3">Proceed to checkout</a>
                @else
                    <p class="max-w-md text-right text-sm text-ink-600">Confirm your email address before you can place an order.</p>
                    <a href="{{ route('verification.notice') }}" class="btn-primary px-10 py-3">Verify email</a>
                @endif
            @endguest
        </div>
    @endif
@endsection
