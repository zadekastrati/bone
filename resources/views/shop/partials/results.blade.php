@php
    $isSearch = $q !== '' && $searchResults !== null;
@endphp

<div class="space-y-16 lg:space-y-20">
    @if ($isSearch)
        <section class="scroll-mt-28" aria-labelledby="search-results-heading">
            <h2 id="search-results-heading" class="sr-only">Matching products</h2>
            @if ($searchResults->isEmpty())
                <div class="rounded-3xl border border-zinc-200/70 bg-gradient-to-b from-zinc-50/95 to-white px-6 py-14 text-center shadow-soft ring-1 ring-zinc-900/[0.04]">
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

    @if (! $isSearch && $products !== null && $products->count() > 0)
        <section id="products" class="scroll-mt-28" aria-labelledby="products-heading">
            <header class="flex flex-col gap-4 border-b border-zinc-200/60 pb-8 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0">
                    <p class="ui-eyebrow">Store catalog</p>
                    <h2 id="products-heading" class="section-title mt-1">All products</h2>
                    <p class="text-muted mt-3 max-w-xl">Browse every active product in the store.</p>
                </div>
            </header>
            <ul class="mt-10 grid list-none gap-6 sm:grid-cols-2 sm:gap-8 lg:grid-cols-3">
                @foreach ($products as $product)
                    <li class="min-w-0">
                        @include('shop.partials.product-grid-card', ['product' => $product])
                    </li>
                @endforeach
            </ul>
            <div class="pagination-wrap">
                {{ $products->links() }}
            </div>
        </section>
    @endif

    @if (! $isSearch && $products !== null && $products->count() === 0)
        <section class="scroll-mt-28" aria-labelledby="products-empty-heading">
            <h2 id="products-empty-heading" class="sr-only">All products</h2>
            <div class="rounded-3xl border border-zinc-200/70 bg-gradient-to-b from-zinc-50/95 to-white px-6 py-14 text-center shadow-soft ring-1 ring-zinc-900/[0.04]">
                <p class="font-display text-lg font-bold uppercase tracking-wide text-ink-950">No products yet</p>
                <p class="mt-2 text-sm text-ink-600 text-pretty">Products will appear here once they are added and active.</p>
            </div>
        </section>
    @endif
</div>
