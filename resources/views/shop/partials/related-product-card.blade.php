@php
    $image = $product->thumbnailImage();
@endphp
<article class="store-card-interactive group/card w-36 shrink-0 snap-start overflow-hidden sm:w-44">
    <a href="{{ route('shop.product', [$product->category, $product]) }}" class="relative block aspect-[4/5] overflow-hidden bg-gradient-to-b from-white to-ink-100/80">
        @if ($product->isSoldOut())
            <span class="pointer-events-none absolute left-1.5 top-1.5 z-20 inline-flex items-center rounded-full bg-ink-950/90 px-2 py-1 shadow-soft backdrop-blur-sm" aria-hidden="true">
                <span class="text-[8px] font-bold uppercase leading-none tracking-[0.12em] text-white">{{ __('Sold out') }}</span>
            </span>
        @endif
        @if ($image)
            @if ($image->isVideo())
                <video src="{{ $image->url() }}" class="size-full object-cover transition duration-700 ease-out group-hover/card:scale-[1.03] motion-reduce:group-hover/card:scale-100" muted playsinline preload="metadata" onloadedmetadata="this.currentTime=0.1"></video>
            @else
                <img src="{{ $image->gridUrl() }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="size-full object-cover transition duration-700 ease-out group-hover/card:scale-[1.03] motion-reduce:group-hover/card:scale-100">
            @endif
        @else
            <div class="flex size-full items-center justify-center bg-gradient-to-br from-zinc-100 to-accent-200 text-center text-[9px] font-bold uppercase tracking-mega text-accent-700">{{ __('Photo soon') }}</div>
        @endif
    </a>
    <div class="space-y-1 p-3">
        <p class="ui-eyebrow text-[8px]">{{ $product->category->name }}</p>
        <h3 class="font-display line-clamp-1 text-[11px] font-bold uppercase leading-snug tracking-wide text-ink-950">
            <a href="{{ route('shop.product', [$product->category, $product]) }}" class="transition hover:text-accent-600">{{ $product->name }}</a>
        </h3>
        <p class="pt-0.5 font-display text-sm font-semibold tabular-nums tracking-tight text-ink-950">
            <x-price :amount="$product->price" />
        </p>
    </div>
</article>
