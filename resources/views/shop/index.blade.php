@extends('layouts.app')

@section('title', $q !== '' ? 'Search' : 'Shop')

@section('content')
    @php
        $isSearch = $q !== '' && $searchResults !== null;
        $products = $products ?? null;
        $pageSubtitle = 'Search or browse by category — every product shows colors and sizes before checkout.';
        if ($isSearch) {
            $pageSubtitle = $searchResults->total().' '.($searchResults->total() === 1 ? 'result' : 'results').' for “'.e($q).'”.';
        } elseif ($products !== null) {
            $pageSubtitle = $products->total().' '.($products->total() === 1 ? 'product' : 'products').' available.';
        }
    @endphp

    <nav class="crumbs mb-6" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Home</a>
        <span class="mx-1.5 text-ink-300" aria-hidden="true">/</span>
        @if ($isSearch)
            <a href="{{ route('shop.index') }}">Shop</a>
            <span class="mx-1.5 text-ink-300" aria-hidden="true">/</span>
            <span class="text-ink-800" aria-current="page">Search</span>
        @else
            <span class="text-ink-800" aria-current="page">Shop</span>
        @endif
    </nav>

    <x-page-header :title="$isSearch ? 'Search results' : 'Shop'" :subtitle="$pageSubtitle">
        @auth
            <a href="{{ route('orders.index') }}" class="btn-secondary">My orders</a>
        @endauth
    </x-page-header>

    <div x-data="adminLiveSearch('shop-results')">
    {{-- Shop toolbar: search + context (full-width on small screens) --}}
    <div class="mb-12 flex flex-col gap-4 rounded-2xl border border-zinc-200/70 bg-white/90 p-4 shadow-soft ring-1 ring-zinc-900/[0.03] sm:flex-row sm:items-center sm:gap-6 sm:p-5">
        <div class="min-w-0 flex-1 sm:max-w-xl">
            <x-store-search-form variant="shop" x-ref="form" />
        </div>
        <div class="flex shrink-0 flex-wrap items-center gap-3 border-t border-zinc-100 pt-4 sm:border-t-0 sm:pt-0">
            @if (! $isSearch)
                <p class="text-sm text-ink-600">
                    <span class="font-semibold tabular-nums text-ink-900">{{ $categories->count() }}</span>
                    {{ $categories->count() === 1 ? 'category' : 'categories' }}
                    @if ($products !== null && $products->total() > 0)
                        <span class="text-ink-400"> · </span>
                        <span class="font-semibold tabular-nums text-ink-900">{{ $products->total() }}</span> products
                    @endif
                </p>
            @endif
            @if ($isSearch)
                <a href="{{ route('shop.index') }}" class="btn-secondary whitespace-nowrap">Clear search</a>
            @elseif ($products !== null && $products->total() > 0)
                <a href="#products" class="btn-ghost whitespace-nowrap px-4 py-2 text-[11px]">Browse products</a>
            @endif
            {{-- Categories dropdown --}}
            <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
                <button
                    @click="open = !open"
                    :aria-expanded="open"
                    aria-haspopup="true"
                    class="btn-ghost inline-flex items-center gap-1.5 whitespace-nowrap px-4 py-2 text-[11px]"
                >
                    Categories
                    <svg
                        class="h-3 w-3 transition-transform duration-200"
                        :class="{ 'rotate-180': open }"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                    >
                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                    class="absolute right-0 z-50 mt-2 w-64 origin-top-right rounded-2xl border border-zinc-200/70 bg-white shadow-lg ring-1 ring-zinc-900/[0.04] focus:outline-none"
                    role="menu"
                    aria-orientation="vertical"
                >
                    <div class="max-h-72 overflow-y-auto py-2">
                        @forelse ($categories as $cat)
                            <a
                                href="{{ route('shop.category', $cat) }}"
                                class="flex items-center justify-between px-4 py-2.5 text-sm text-ink-700 hover:bg-zinc-50 hover:text-accent-700 transition-colors"
                                role="menuitem"
                                @click="open = false"
                            >
                                <span class="font-medium">{{ $cat->name }}</span>
                                <span class="text-xs text-ink-400 tabular-nums">{{ $cat->active_products_count }}</span>
                            </a>
                        @empty
                            <p class="px-4 py-3 text-sm text-ink-500">No categories yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="shop-results" class="transition-opacity" :class="{ 'opacity-50': loading }">
        @include('shop.partials.results')
    </div>
    </div>
@endsection
