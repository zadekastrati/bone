@extends('layouts.app')

@section('title', $q !== '' ? 'Search' : 'Shop')

@section('content')
    @php
        $isSearch = $q !== '' && $searchResults !== null;
        $pageSubtitle = 'Search or browse by category — every product shows colors and sizes before checkout.';
        if ($isSearch) {
            $pageSubtitle = $searchResults->total().' '.($searchResults->total() === 1 ? 'result' : 'results').' for “'.e($q).'”.';
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

    {{-- Shop toolbar: search + context (full-width on small screens) --}}
    <div class="mb-12 flex flex-col gap-4 rounded-2xl border border-pink-200/70 bg-white/90 p-4 shadow-soft ring-1 ring-pink-900/[0.03] sm:flex-row sm:items-center sm:gap-6 sm:p-5">
        <div class="min-w-0 flex-1 sm:max-w-xl">
            <x-store-search-form variant="shop" />
        </div>
        <div class="flex shrink-0 flex-wrap items-center gap-3 border-t border-pink-100 pt-4 sm:border-t-0 sm:pt-0">
            @if (! $isSearch)
                <p class="text-sm text-ink-600">
                    <span class="font-semibold tabular-nums text-ink-900">{{ $categories->count() }}</span>
                    {{ $categories->count() === 1 ? 'category' : 'categories' }}
                    @if ($featured->isNotEmpty())
                        <span class="text-ink-400"> · </span>
                        <span class="font-semibold tabular-nums text-ink-900">{{ $featured->count() }}</span> new
                    @endif
                </p>
            @endif
            @if ($isSearch)
                <a href="{{ route('shop.index') }}" class="btn-secondary whitespace-nowrap">Clear search</a>
            @elseif ($featured->isNotEmpty())
                <a href="#new" class="btn-ghost whitespace-nowrap px-4 py-2 text-[11px]">New arrivals</a>
            @endif
            <a href="#categories" class="btn-ghost whitespace-nowrap px-4 py-2 text-[11px]">Categories</a>
        </div>
    </div>

    <div class="space-y-16 lg:space-y-20">
        @if ($isSearch)
            <section class="scroll-mt-28" aria-labelledby="search-results-heading">
                <h2 id="search-results-heading" class="sr-only">Matching products</h2>
                @if ($searchResults->isEmpty())
                    <div class="rounded-3xl border border-pink-200/70 bg-gradient-to-b from-pink-50/95 to-white px-6 py-14 text-center shadow-soft ring-1 ring-pink-900/[0.04]">
                        <p class="font-display text-lg font-bold uppercase tracking-wide text-ink-950">No matches</p>
                        <p class="mt-2 text-sm text-ink-600 text-pretty">Try a shorter term or browse categories below.</p>
                        <a href="{{ route('shop.index') }}" class="btn-primary mt-8 inline-flex">View all products</a>
                    </div>
                @else
                    <ul class="grid list-none gap-6 sm:grid-cols-2 sm:gap-8 lg:grid-cols-3">
                        @foreach ($searchResults as $product)
                            <li class="min-w-0">
                                @include('shop.partials.product-grid-card', ['product' => $product])
                            </li>
                        @endforeach
                    </ul>
                    <div class="pagination-wrap">
                        {{ $searchResults->links() }}
                    </div>
                @endif
            </section>
        @endif

        @if (! $isSearch && $featured->isNotEmpty())
            <section id="new" class="scroll-mt-28" aria-labelledby="new-arrivals-heading">
                <header class="flex flex-col gap-4 border-b border-pink-200/60 pb-8 sm:flex-row sm:items-end sm:justify-between">
                    <div class="min-w-0">
                        <p class="ui-eyebrow">Just dropped</p>
                        <h2 id="new-arrivals-heading" class="section-title mt-1">New arrivals</h2>
                        <p class="text-muted mt-3 max-w-xl">Latest additions — open a product to pick color and size.</p>
                    </div>
                </header>
                <ul class="mt-10 grid list-none gap-6 sm:grid-cols-2 sm:gap-8 lg:grid-cols-3">
                    @foreach ($featured as $product)
                        <li class="min-w-0">
                            @include('shop.partials.product-grid-card', ['product' => $product])
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section
            id="categories"
            class="@if ($isSearch || (! $isSearch && $featured->isNotEmpty())) scroll-mt-28 border-t border-pink-200/60 pt-14 lg:pt-16 @endif"
            aria-labelledby="categories-heading"
        >
            <header class="flex flex-col gap-4 border-b border-pink-200/60 pb-8 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0">
                    <p class="ui-eyebrow">Browse</p>
                    <h2 id="categories-heading" class="section-title mt-1">Shop by category</h2>
                    <p class="text-muted mt-3 max-w-xl">Choose a line — each category lists what&apos;s in stock.</p>
                </div>
            </header>

            @if ($categories->isEmpty())
                <p class="mt-10 text-ink-600">No categories yet. An administrator can add categories and products from the admin area.</p>
            @else
                <ul class="mt-10 grid list-none gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8">
                    @foreach ($categories as $cat)
                        <li class="min-w-0">
                            @include('shop.partials.category-card', ['category' => $cat])
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
