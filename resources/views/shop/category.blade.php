@extends('layouts.app')

@section('title', $category->name)

@section('content')
    <nav class="text-xs font-medium text-ink-500">
        <a href="{{ route('shop.index') }}" class="hover:text-accent-600">Shop</a>
        <span class="mx-1.5 text-ink-300">/</span>
        <span class="text-ink-800">{{ $category->name }}</span>
    </nav>

    <x-page-header :title="$category->name" :subtitle="$category->description" />

    <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($products as $product)
            @php
                $image = $product->images->first();
            @endphp
            <article class="overflow-hidden rounded-3xl border border-ink-200/60 bg-white/95 shadow-soft ring-1 ring-ink-950/[0.03]">
                <a href="{{ route('shop.product', [$category, $product]) }}" class="block aspect-[4/5] overflow-hidden bg-ink-100">
                    @if ($image)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($image->path) }}" alt="" class="size-full object-cover transition duration-500 hover:scale-[1.03]">
                    @else
                        <div class="flex size-full items-center justify-center bg-gradient-to-br from-ink-800 to-ink-950 text-center text-xs font-bold uppercase tracking-mega text-white/40">Photo soon</div>
                    @endif
                </a>
                <div class="p-5">
                    <h2 class="font-display text-base font-bold uppercase tracking-wide text-ink-950">
                        <a href="{{ route('shop.product', [$category, $product]) }}" class="hover:text-accent-700">{{ $product->name }}</a>
                    </h2>
                    <p class="mt-3 text-sm font-semibold text-ink-800">
                        {{ config('store.currency_symbol') }}{{ number_format((float) $product->price, 2) }}
                    </p>
                </div>
            </article>
        @empty
            <p class="text-ink-600 sm:col-span-2 lg:col-span-3">No products in this category yet.</p>
        @endforelse
    </div>

    <div class="mt-12 flex justify-center">
        {{ $products->links() }}
    </div>
@endsection
