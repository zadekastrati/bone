@extends('layouts.app')

@section('title', $product->name)

@php
    $__thumb = $product->thumbnailImage();
    $__plainDescription = $product->description ? trim(preg_replace('/\s+/', ' ', strip_tags($product->description))) : null;
@endphp

@section('meta_description', $__plainDescription
    ? \Illuminate\Support\Str::limit($__plainDescription, 160)
    : __(':product, :category from :store.', ['product' => $product->name, 'category' => $category->name, 'store' => config('app.name')]))
@section('meta_image', $__thumb && ! $__thumb->isVideo() ? $__thumb->displayUrl() : asset('logo.png'))
@section('og_type', 'product')

@section('structured_data')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $__plainDescription ?? $product->name,
            'image' => $__thumb && ! $__thumb->isVideo() ? [$__thumb->displayUrl()] : [],
            'sku' => $product->style_code,
            'category' => $category->name,
            'offers' => [
                '@type' => 'Offer',
                'url' => route('shop.product', [$category, $product]),
                'priceCurrency' => config('store.currency', 'EUR'),
                'price' => number_format((float) $product->price, 2, '.', ''),
                'availability' => $product->isSoldOut()
                    ? 'https://schema.org/OutOfStock'
                    : 'https://schema.org/InStock',
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}
    </script>
@endsection

@section('content')
    <nav class="crumbs" aria-label="Breadcrumb">
        <a href="{{ route('shop.index') }}">{{ __('Shop') }}</a>
        <span class="mx-1.5 text-ink-300">/</span>
        <a href="{{ route('shop.category', $category) }}">{{ $category->name }}</a>
        <span class="mx-1.5 text-ink-300">/</span>
        <span class="text-ink-800">{{ $product->name }}</span>
    </nav>

    <div class="mt-10 grid gap-10 lg:grid-cols-2 lg:gap-14">
        <div class="space-y-4">
            <x-shop.product-media-gallery :product="$product" :images-by-color="$imagesByColor" :default-color="$defaultColor" />
        </div>

        <div>
            <p class="ui-eyebrow">{{ $category->name }}</p>
            <h1 class="font-display mt-3 text-3xl font-bold uppercase tracking-tight text-ink-950 sm:text-4xl sm:tracking-wide">{{ $product->name }}</h1>
            <p class="mt-4 text-2xl font-semibold text-ink-900">
                <x-price :amount="$product->price" />
                <span class="text-sm font-medium text-ink-500">{{ app(\App\Services\CurrencyService::class)->currentCurrency() }}</span>
            </p>
            <x-store.currency-disclaimer :amount="$product->price" class="mt-1.5" />

            @if ($product->description)
                <div class="prose prose-ink mt-6 max-w-none text-sm leading-relaxed text-ink-600">
                    {!! nl2br(e($product->description)) !!}
                </div>
            @endif

            @if ($product->isSoldOut())
                <div class="mt-10 rounded-2xl border border-zinc-200/80 bg-zinc-100/80 px-5 py-6 text-center">
                    <p class="font-display text-sm font-bold uppercase tracking-[0.2em] text-ink-800">{{ __('Sold out') }}</p>
                    <p class="mt-2 text-sm text-ink-600">{{ __('This product is not available right now. Try another size or colour on other items, or check back later.') }}</p>
                    <a href="{{ route('shop.category', $category) }}" class="btn-secondary mt-6 inline-flex px-6 py-3">{{ __('Back to :category', ['category' => $category->name]) }}</a>
                </div>
            @else
            <form id="add-to-cart-form" method="POST" action="{{ route('cart.store') }}" class="mt-10 space-y-8" data-cart-form>
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-mega text-ink-500">{{ __('Color') }} <span class="text-red-600">*</span></p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($product->availableColors() as $c)
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-2xl border border-ink-200/80 bg-white px-4 py-2 text-sm font-medium shadow-sm transition has-[:checked]:border-accent-500 has-[:checked]:ring-2 has-[:checked]:ring-accent-500/30">
                                <input
                                    type="radio"
                                    name="color"
                                    value="{{ $c['name'] }}"
                                    class="product-color-input sr-only"
                                    {{ old('color') === $c['name'] ? 'checked' : '' }}
                                    required
                                >
                                @if (! empty($c['hex']))
                                    <span class="size-5 rounded-full border border-ink-200/60 shadow-inner" style="background-color: {{ $c['hex'] }}"></span>
                                @endif
                                <span>{{ $c['name'] }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('color')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-mega text-ink-500">{{ __('Size') }} <span class="text-red-600">*</span></p>
                    <p id="pick-color-hint" class="mt-2 text-xs text-ink-500">{{ __('Select a color first, then choose a size.') }}</p>

                    @forelse ($variantsByColor as $colorName => $group)
                        @php
                            $sizeList = $group->pluck('size')->unique()
                                ->sortBy(fn ($sz) => \App\Models\ProductVariant::sizeSortKey($sz))
                                ->values();
                        @endphp
                        <fieldset
                            class="product-size-panel mt-3 border-0 p-0"
                            data-size-for="{{ e($colorName) }}"
                            disabled
                            hidden
                        >
                            <legend class="sr-only">{{ __('Sizes for :color', ['color' => $colorName]) }}</legend>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($sizeList as $sz)
                                    @php
                                        $lineStock = (int) $stockByKey->get($colorName.'|'.$sz, 0);
                                    @endphp
                                    <label class="inline-flex min-w-[3rem] cursor-pointer items-center justify-center rounded-2xl border border-ink-200/80 bg-white px-4 py-2 text-sm font-semibold shadow-sm transition has-[:checked]:border-accent-500 has-[:checked]:ring-2 has-[:checked]:ring-accent-500/30">
                                        <input
                                            type="radio"
                                            name="size"
                                            value="{{ $sz }}"
                                            class="product-size-input sr-only"
                                            data-stock="{{ $lineStock }}"
                                            {{ old('color') === $colorName && old('size') === (string) $sz ? 'checked' : '' }}
                                        >
                                        <span>{{ $sz }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @empty
                        <p class="mt-2 text-sm text-amber-800">{{ __('This product has no size variants yet.') }}</p>
                    @endforelse

                    <p id="product-oos-line" class="mt-2 hidden text-xs text-amber-800">{{ __('This combination is out of stock.') }}</p>
                    @error('size')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="quantity" class="text-[10px] font-bold uppercase tracking-mega text-ink-500">{{ __('Quantity') }}</label>
                    <input
                        type="number"
                        name="quantity"
                        id="quantity"
                        min="1"
                        max="99"
                        value="{{ old('quantity', 1) }}"
                        class="form-input mt-2 max-w-[8rem]"
                        required
                    >
                    @error('quantity')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" id="add-to-cart-submit" class="btn-primary px-8 py-3 opacity-40" disabled>
                        {{ __('Add to cart') }}
                    </button>
                    <a href="{{ route('shop.category', $category) }}" class="btn-secondary px-6 py-3">{{ __('Back') }}</a>
                </div>
                <p id="add-to-cart-hint" class="text-xs text-ink-500">{{ __('Choose a color and size to add this item to your cart.') }}</p>
                <p
                    x-data="{ text: '', ok: true, show: false, timer: null }"
                    x-show="show"
                    x-cloak
                    x-text="text"
                    :class="ok ? 'text-emerald-700' : 'text-red-600'"
                    class="text-xs font-semibold"
                    @cart-updated.window="text = $event.detail.message ?? '{{ __('Added to cart.') }}'; ok = true; show = true; clearTimeout(timer); timer = setTimeout(() => { const ref = document.referrer; window.location.href = (ref && ref.startsWith(window.location.origin)) ? ref : '{{ route('shop.index') }}' }, 900)"
                    @cart-error.window="text = $event.detail.message ?? '{{ __('Something went wrong.') }}'; ok = false; show = true; clearTimeout(timer); timer = setTimeout(() => show = false, 4000)"
                ></p>
            </form>
            @endif
        </div>
    </div>

    @if ($relatedProducts->isNotEmpty())
        <section class="mt-16 border-t border-zinc-200/60 pt-12 sm:mt-20 sm:pt-16 lg:mt-24 lg:pt-20" aria-labelledby="related-products-heading">
            <p class="ui-eyebrow">{{ __('Keep exploring') }}</p>
            <h2 id="related-products-heading" class="section-title mt-1">{{ __('More to Explore') }}</h2>
            <ul class="mt-10 grid list-none gap-6 sm:grid-cols-2 sm:gap-8 lg:grid-cols-3">
                @foreach ($relatedProducts as $relatedProduct)
                    <li class="min-w-0">
                        @include('shop.partials.product-grid-card', ['product' => $relatedProduct])
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if (! $product->isSoldOut())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('add-to-cart-form');
            if (! form) {
                return;
            }

            const panels = form.querySelectorAll('.product-size-panel');
            const colorInputs = form.querySelectorAll('.product-color-input');
            const sizeInputs = form.querySelectorAll('.product-size-input');
            const pickHint = document.getElementById('pick-color-hint');
            const stockLine = document.getElementById('product-stock-line');
            const stockVal = stockLine ? stockLine.querySelector('[data-stock-val]') : null;
            const oosLine = document.getElementById('product-oos-line');
            const submitBtn = document.getElementById('add-to-cart-submit');
            const hint = document.getElementById('add-to-cart-hint');

            function selectedColor() {
                const el = form.querySelector('.product-color-input:checked');

                return el ? el.value : null;
            }

            function syncPanels() {
                const color = selectedColor();

                if (pickHint) {
                    pickHint.hidden = !! color;
                }

                panels.forEach(function (panel) {
                    const forColor = panel.getAttribute('data-size-for');
                    const match = color !== null && forColor === color;

                    panel.hidden = ! match;
                    panel.disabled = ! match;

                    if (! match) {
                        panel.querySelectorAll('.product-size-input').forEach(function (r) {
                            r.checked = false;
                        });
                    }
                });

                updateBar();

                window.dispatchEvent(new CustomEvent('product-color-selected', { detail: { color: color } }));
            }

            function updateBar() {
                const sizeEl = form.querySelector('.product-size-input:checked');
                const stock = sizeEl ? parseInt(sizeEl.getAttribute('data-stock') || '0', 10) : null;

                if (stockLine) {
                    stockLine.classList.toggle('hidden', stock === null);
                }
                if (stockVal && stock !== null) {
                    stockVal.textContent = String(stock);
                }
                if (oosLine) {
                    oosLine.classList.toggle('hidden', stock !== 0);
                }

                const ok = selectedColor() && sizeEl !== null && stock !== null && stock >= 1;

                if (submitBtn) {
                    submitBtn.disabled = ! ok;
                    submitBtn.classList.toggle('opacity-40', ! ok);
                }
                if (hint) {
                    hint.hidden = ok;
                }
            }

            colorInputs.forEach(function (el) {
                el.addEventListener('change', syncPanels);
            });
            sizeInputs.forEach(function (el) {
                el.addEventListener('change', updateBar);
            });

            syncPanels();
        });
    </script>
    @endif
@endsection
