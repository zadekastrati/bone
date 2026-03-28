@extends('layouts.app')

@section('title', $q !== '' ? 'Search' : 'Shop')

@section('content')
    @php
        $pageSubtitle = 'Browse by category. Every product lists colors and sizes before checkout.';
        if ($q !== '' && $searchResults !== null) {
            $pageSubtitle = $searchResults->total().' '.($searchResults->total() === 1 ? 'result' : 'results').' for "'.$q.'"';
        }
    @endphp
    <x-page-header :title="$q !== '' ? 'Search results' : 'Shop'" :subtitle="$pageSubtitle">
        @auth
            <a href="{{ route('orders.index') }}" class="btn-secondary">My orders</a>
        @endauth
    </x-page-header>

    @if ($q !== '' && $searchResults !== null)
        <section class="mb-16" aria-labelledby="search-results-heading">
            <h2 id="search-results-heading" class="sr-only">Matching products</h2>
            @if ($searchResults->isEmpty())
                <div class="rounded-3xl border border-ink-200/60 bg-gradient-to-b from-ink-50/90 to-white px-6 py-14 text-center shadow-soft ring-1 ring-ink-950/[0.03]">
                    <p class="font-display text-lg font-bold uppercase tracking-wide text-ink-950">No matches</p>
                    <p class="mt-2 text-sm text-ink-600 text-pretty">Try a shorter term or browse categories below.</p>
                    <a href="{{ route('shop.index') }}" class="btn-primary mt-8 inline-flex">Clear search</a>
                </div>
            @else
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($searchResults as $product)
                        @include('shop.partials.product-grid-card', ['product' => $product])
                    @endforeach
                </div>
                <div class="pagination-wrap">
                    {{ $searchResults->links() }}
                </div>
            @endif
        </section>
    @endif

    <section @if($q !== '') class="border-t border-ink-200/50 pt-16" @endif>
        <p class="ui-eyebrow">Browse</p>
        <h2 class="section-title mt-2">Categories</h2>
        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($categories as $cat)
                <a
                    href="{{ route('shop.category', $cat) }}"
                    class="store-card-interactive group flex flex-col p-6 sm:p-7"
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
        <section id="new" class="mt-20 scroll-mt-24 border-t border-ink-200/40 pt-16">
            <p class="ui-eyebrow">Just dropped</p>
            <h2 class="section-title mt-2">New arrivals</h2>
            <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $product)
                    @include('shop.partials.product-grid-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif
@endsection
