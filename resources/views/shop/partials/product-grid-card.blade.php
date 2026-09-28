@php
    $showCategory = $showCategory ?? true;
    $image = $product->thumbnailImage();

    // Sourced from the already-eager-loaded variants/images collections
    // (not availableColors()/imagesForColor(), which always re-query) — this
    // card renders once per product in a listing of up to a dozen, so an
    // extra query per product per call would add up fast.
    $variants = $product->relationLoaded('variants') ? $product->variants : $product->variants()->get();
    $images = $product->relationLoaded('images') ? $product->images : $product->images()->get();

    $colors = $variants->sortBy('color')->unique('color')->values()
        ->map(fn ($v) => ['name' => $v->color, 'hex' => $v->color_hex]);

    $colorImages = $colors->mapWithKeys(function (array $c) use ($images) {
        $forColor = $images->where('color', $c['name'])->first(fn ($i) => ! $i->isVideo());
        $shared = $images->whereNull('color')->first(fn ($i) => ! $i->isVideo());

        return [$c['name'] => ($forColor ?? $shared)?->gridUrl()];
    })->filter();

    $showSwatches = $colorImages->count() > 1;
@endphp
<article
    class="store-card-interactive group/card overflow-hidden"
    @if ($showSwatches)
        x-data="{ activeColor: null, colorImages: @js($colorImages), defaultImage: @js($image?->gridUrl()) }"
        x-init="Object.values(colorImages).forEach((src) => { if (src) (new Image()).src = src })"
    @endif
>
    <a href="{{ route('shop.product', [$product->category, $product]) }}" class="relative block aspect-[4/5] overflow-hidden bg-gradient-to-b from-white to-ink-100/80">
        @if ($product->isSoldOut())
            {{-- Top-left on the image: visible while browsing shop (no click needed) --}}
            <span class="pointer-events-none absolute left-2 top-2 z-20 inline-flex items-center gap-1.5 rounded-full bg-ink-950/90 px-3 py-1.5 shadow-soft backdrop-blur-sm sm:left-3 sm:top-3" aria-hidden="true">
                <svg class="size-3 shrink-0 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M5 5l14 14" />
                </svg>
                <span class="text-[10px] font-bold uppercase leading-none tracking-[0.15em] text-white sm:text-[11px]">
                    {{ __('Sold out') }}
                </span>
            </span>
        @endif
        @if ($image)
            @if ($image->isVideo())
                <video src="{{ $image->url() }}" class="size-full object-cover transition duration-700 ease-out group-hover/card:scale-[1.03] motion-reduce:group-hover/card:scale-100" muted playsinline preload="metadata" onloadedmetadata="this.currentTime=0.1"></video>
            @elseif ($showSwatches)
                <img :src="activeColor ? (colorImages[activeColor] ?? defaultImage) : defaultImage" alt="{{ $product->name }}" loading="lazy" decoding="async" class="size-full object-cover transition duration-700 ease-out group-hover/card:scale-[1.03] motion-reduce:group-hover/card:scale-100">
            @else
                <img src="{{ $image->gridUrl() }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="size-full object-cover transition duration-700 ease-out group-hover/card:scale-[1.03] motion-reduce:group-hover/card:scale-100">
            @endif
        @else
            <div class="flex size-full items-center justify-center bg-gradient-to-br from-zinc-100 to-accent-200 text-center text-xs font-bold uppercase tracking-mega text-accent-700">{{ __('Photo soon') }}</div>
        @endif
        <span class="pointer-events-none absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-accent-400/15 to-transparent opacity-0 transition duration-300 group-hover/card:opacity-100 motion-reduce:opacity-0"></span>
    </a>
    <div class="space-y-1.5 p-6">
        @if ($showSwatches)
            <div class="flex flex-wrap items-center gap-1.5 pb-0.5">
                @foreach ($colors as $c)
                    @continue(! $colorImages->has($c['name']))
                    <button
                        type="button"
                        @click="activeColor = @js($c['name'])"
                        :class="activeColor === @js($c['name']) ? 'ring-2 ring-accent-500 ring-offset-1' : 'ring-1 ring-ink-200/80 hover:ring-ink-300'"
                        class="size-4 shrink-0 rounded-full border border-white/70 shadow-sm transition"
                        style="background-color: {{ $c['hex'] ?? '#e4e4e7' }}"
                        title="{{ $c['name'] }}"
                        aria-label="{{ __('Show :color', ['color' => $c['name']]) }}"
                    ></button>
                @endforeach
            </div>
        @endif
        @if ($showCategory)
            <p class="ui-eyebrow text-ink-400">{{ $product->category->name }}</p>
        @endif
        <h3 class="font-display text-base font-bold uppercase leading-snug tracking-wide text-ink-950">
            <a href="{{ route('shop.product', [$product->category, $product]) }}" class="transition hover:text-accent-600">{{ $product->name }}</a>
        </h3>
        <p class="pt-2 font-display text-lg font-semibold tabular-nums tracking-tight text-ink-950">
            <x-price :amount="$product->price" />
        </p>
    </div>
</article>
