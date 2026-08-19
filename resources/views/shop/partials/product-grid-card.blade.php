@php
    $showCategory = $showCategory ?? true;
    $image = $product->thumbnailImage();
@endphp
<article class="store-card-interactive group/card overflow-hidden">
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
                <video src="{{ $image->url() }}" class="size-full object-cover transition duration-700 ease-out group-hover/card:scale-[1.03] motion-reduce:group-hover/card:scale-100" muted playsinline preload="metadata"></video>
            @else
                <img src="{{ $image->gridUrl() }}" alt="" loading="lazy" decoding="async" class="size-full object-cover transition duration-700 ease-out group-hover/card:scale-[1.03] motion-reduce:group-hover/card:scale-100">
            @endif
        @else
            <div class="flex size-full items-center justify-center bg-gradient-to-br from-zinc-100 to-accent-200 text-center text-xs font-bold uppercase tracking-mega text-accent-700">{{ __('Photo soon') }}</div>
        @endif
        <span class="pointer-events-none absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-accent-400/15 to-transparent opacity-0 transition duration-300 group-hover/card:opacity-100 motion-reduce:opacity-0"></span>
    </a>
    <div class="space-y-1.5 p-6">
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
