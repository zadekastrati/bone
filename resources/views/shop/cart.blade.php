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
        {{-- One column width for lines + checkout (aligned); wider than max-w-3xl --}}
        <div class="mx-auto mt-10 w-full max-w-5xl">
            <div class="divide-y divide-zinc-200/90 border-t border-zinc-200/90">
            @foreach ($lines as $line)
                @php
                    $v = $line['variant'];
                    $p = $v->product;
                    $thumb = $p->images->first();
                @endphp
                <article class="flex gap-5 py-8 sm:gap-8 sm:py-10">
                    <a
                        href="{{ route('shop.product', [$p->category, $p]) }}"
                        class="group shrink-0 self-start"
                    >
                        <x-product-image-thumb :path="$thumb?->path" size="cartRow" class="transition duration-200 group-hover:opacity-90" />
                    </a>

                    <div class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-8">
                        <div class="min-w-0 space-y-2">
                            <a
                                href="{{ route('shop.product', [$p->category, $p]) }}"
                                class="font-display text-sm font-bold uppercase leading-snug tracking-wide text-ink-950 transition hover:text-accent-700 sm:text-base"
                            >
                                {{ $p->name }}
                            </a>
                            <p class="text-xs uppercase tracking-wide text-ink-600">
                                {{ $v->size }} <span class="text-ink-300">|</span> {{ $v->color }}
                            </p>
                            <p class="font-display text-sm font-semibold tabular-nums text-ink-950">
                                {{ config('store.currency_symbol') }}{{ number_format((float) $p->price, 2) }}
                                <span class="text-xs font-normal text-ink-500">each</span>
                            </p>
                            @if ($v->stock_quantity < 1)
                                <p class="text-[11px] font-bold uppercase tracking-wide text-amber-800">Sold out — no stock left</p>
                            @elseif (! $v->isInStock((int) $line['quantity']))
                                <p class="text-[11px] font-semibold text-amber-800">Only {{ $v->stock_quantity }} left — reduce quantity</p>
                            @endif
                        </div>

                        <div class="flex flex-col gap-4 sm:items-end sm:text-right">
                            <form method="POST" action="{{ route('cart.update', $v->id) }}" class="flex flex-wrap items-center gap-2 sm:justify-end">
                                @csrf
                                @method('PATCH')
                                <label class="text-xs font-semibold uppercase tracking-wider text-ink-500" for="qty-{{ $v->id }}">Qty</label>
                                <input
                                    id="qty-{{ $v->id }}"
                                    type="number"
                                    name="quantity"
                                    value="{{ $line['quantity'] }}"
                                    min="0"
                                    max="99"
                                    class="w-16 rounded-lg border border-zinc-200/90 bg-white px-2 py-1.5 text-center text-sm tabular-nums text-ink-900 shadow-sm focus:border-accent-400 focus:outline-none focus:ring-2 focus:ring-accent-400/20"
                                >
                                <button type="submit" class="text-xs font-semibold uppercase tracking-wider text-accent-700 hover:underline">Update</button>
                            </form>

                            <p class="font-display text-base font-semibold tabular-nums text-ink-950">
                                {{ config('store.currency_symbol') }}{{ number_format((float) $line['line_total'], 2) }}
                                @if ($line['quantity'] > 1)
                                    <span class="block text-xs font-normal text-ink-500">line total</span>
                                @endif
                            </p>

                            <form method="POST" action="{{ route('cart.destroy', $v->id) }}" onsubmit="return confirm('Remove this item?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-bold uppercase tracking-[0.2em] text-ink-600 transition hover:text-red-700">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
            </div>

            <div class="surface-muted mt-12 flex w-full flex-col gap-4">
                <div class="flex flex-col items-end gap-2 sm:flex-row sm:items-baseline sm:justify-end sm:gap-4">
                    <span class="text-sm font-medium text-ink-600">Subtotal</span>
                    <span class="font-display text-2xl font-semibold tabular-nums text-ink-950">{{ config('store.currency_symbol') }}{{ number_format((float) $subtotal, 2) }}</span>
                </div>
                @guest
                    <p class="text-right text-sm text-ink-600">Log in or register to enter shipping details and place your order.</p>
                    <div class="flex flex-wrap justify-end gap-3">
                        <a href="{{ route('login') }}" class="btn-primary">Log in</a>
                        <a href="{{ route('register') }}" class="btn-secondary">Register</a>
                    </div>
                @else
                    @if (auth()->user()->hasVerifiedEmail())
                        <div class="flex justify-end">
                            <a href="{{ route('checkout.create') }}" class="btn-primary min-w-[14rem] px-10 py-3 text-center">Proceed to checkout</a>
                        </div>
                    @else
                        <p class="text-right text-sm text-ink-600">Confirm your email address before you can place an order.</p>
                        <div class="flex justify-end">
                            <a href="{{ route('verification.notice') }}" class="btn-primary px-10 py-3">Verify email</a>
                        </div>
                    @endif
                @endguest
            </div>
        </div>
    @endif
@endsection
