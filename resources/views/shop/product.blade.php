@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <nav class="crumbs" aria-label="Breadcrumb">
        <a href="{{ route('shop.index') }}">Shop</a>
        <span class="mx-1.5 text-ink-300">/</span>
        <a href="{{ route('shop.category', $category) }}">{{ $category->name }}</a>
        <span class="mx-1.5 text-ink-300">/</span>
        <span class="text-ink-800">{{ $product->name }}</span>
    </nav>

    <div class="mt-10 grid gap-10 lg:grid-cols-2 lg:gap-14">
        <div class="space-y-4">
            @if ($product->images->isEmpty())
                <div class="aspect-[4/5] overflow-hidden rounded-3xl bg-gradient-to-br from-zinc-100 to-accent-200 shadow-float">
                    <div class="flex size-full items-center justify-center text-xs font-bold uppercase tracking-mega text-white/40">Photo soon</div>
                </div>
            @else
                <div class="store-card overflow-hidden bg-ink-100 shadow-elevated">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($product->images->first()->path) }}" alt="" class="aspect-[4/5] w-full object-cover">
                </div>
                @if ($product->images->count() > 1)
                    <div class="grid grid-cols-4 gap-3">
                        @foreach ($product->images->skip(1) as $img)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($img->path) }}" alt="" class="aspect-square rounded-xl object-cover ring-1 ring-ink-200/60">
                        @endforeach
                    </div>
                @endif
            @endif
        </div>

        <div>
            <p class="ui-eyebrow">{{ $category->name }}</p>
            <h1 class="font-display mt-3 text-3xl font-bold uppercase tracking-tight text-ink-950 sm:text-4xl sm:tracking-wide">{{ $product->name }}</h1>
            <p class="mt-4 text-2xl font-semibold text-ink-900">
                {{ config('store.currency_symbol') }}{{ number_format((float) $product->price, 2) }}
                <span class="text-sm font-medium text-ink-500">{{ config('store.currency') }}</span>
            </p>

            @if ($product->description)
                <div class="prose prose-ink mt-6 max-w-none text-sm leading-relaxed text-ink-600">
                    {!! nl2br(e($product->description)) !!}
                </div>
            @endif

            <form id="add-to-cart-form" method="POST" action="{{ route('cart.store') }}" class="mt-10 space-y-8">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-mega text-ink-500">Color <span class="text-red-600">*</span></p>
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
                    <p class="text-[10px] font-bold uppercase tracking-mega text-ink-500">Size <span class="text-red-600">*</span></p>
                    <p id="pick-color-hint" class="mt-2 text-xs text-ink-500">Select a color first, then choose a size.</p>

                    @forelse ($variantsByColor as $colorName => $group)
                        @php
                            $sizeList = $group->pluck('size')->unique()->sort()->values();
                        @endphp
                        <fieldset
                            class="product-size-panel mt-3 border-0 p-0"
                            data-size-for="{{ e($colorName) }}"
                            disabled
                            hidden
                        >
                            <legend class="sr-only">Sizes for {{ $colorName }}</legend>
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
                        <p class="mt-2 text-sm text-amber-800">This product has no size variants yet.</p>
                    @endforelse

                    <p id="product-stock-line" class="mt-2 hidden text-xs text-ink-600">
                        <span class="font-semibold">In stock:</span>
                        <span data-stock-val>0</span>
                    </p>
                    <p id="product-oos-line" class="mt-2 hidden text-xs text-amber-800">This combination is out of stock.</p>
                    @error('size')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="quantity" class="text-[10px] font-bold uppercase tracking-mega text-ink-500">Quantity</label>
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
                        Add to cart
                    </button>
                    <a href="{{ route('shop.category', $category) }}" class="btn-secondary px-6 py-3">Back</a>
                </div>
                <p id="add-to-cart-hint" class="text-xs text-ink-500">Choose a color and size to add this item to your cart.</p>
            </form>
        </div>
    </div>

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
@endsection
