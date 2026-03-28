@extends('layouts.app')

@section('title', 'Shop')

@section('content')
    <x-page-header title="Shop" subtitle="Browse by category. Every product lists colors and sizes before checkout.">
        @auth
            <a href="{{ route('orders.index') }}" class="btn-secondary">My orders</a>
        @endauth
    </x-page-header>

    <section class="mt-12">
        <h2 class="font-display text-xl font-bold uppercase tracking-wide text-ink-950">Categories</h2>
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($categories as $cat)
                <a
                    href="{{ route('shop.category', $cat) }}"
                    class="group flex flex-col overflow-hidden rounded-3xl border border-ink-200/60 bg-white/95 p-6 shadow-soft ring-1 ring-ink-950/[0.03] transition hover:border-accent-300/60 hover:shadow-float"
                >
                    <span class="text-xs font-bold uppercase tracking-mega text-accent-600">{{ $cat->active_products_count }} products</span>
                    <span class="font-display mt-3 text-lg font-bold uppercase tracking-wide text-ink-950 group-hover:text-accent-700">{{ $cat->name }}</span>
                    @if ($cat->description)
                        <p class="mt-2 text-sm leading-relaxed text-ink-600 text-pretty">{{ \Illuminate\Support\Str::limit($cat->description, 120) }}</p>
                    @endif
                </a>
            @empty
                <p class="text-ink-600">No categories yet. An administrator can add categories and products from the admin area.</p>
            @endforelse
        </div>
    </section>

    @if ($featured->isNotEmpty())
        <section class="mt-20">
            <h2 class="font-display text-xl font-bold uppercase tracking-wide text-ink-950">New arrivals</h2>
            <div class="mt-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $product)
                    @php
                        $image = $product->images->first();
                    @endphp
                    <article class="overflow-hidden rounded-3xl border border-ink-200/60 bg-white/95 shadow-soft ring-1 ring-ink-950/[0.03]">
                        <a href="{{ route('shop.product', [$product->category, $product]) }}" class="block aspect-[4/5] overflow-hidden bg-ink-100">
                            @if ($image)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($image->path) }}" alt="" class="size-full object-cover transition duration-500 hover:scale-[1.03]">
                            @else
                                <div class="flex size-full items-center justify-center bg-gradient-to-br from-ink-800 to-ink-950 text-center text-xs font-bold uppercase tracking-mega text-white/40">Photo soon</div>
                            @endif
                        </a>
                        <div class="p-5">
                            <p class="text-[10px] font-bold uppercase tracking-mega text-ink-400">{{ $product->category->name }}</p>
                            <h3 class="font-display mt-1 text-base font-bold uppercase tracking-wide text-ink-950">
                                <a href="{{ route('shop.product', [$product->category, $product]) }}" class="hover:text-accent-700">{{ $product->name }}</a>
                            </h3>
                            <p class="mt-3 text-sm font-semibold text-ink-800">
                                {{ config('store.currency_symbol') }}{{ number_format((float) $product->price, 2) }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
