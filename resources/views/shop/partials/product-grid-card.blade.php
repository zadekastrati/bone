@php
    $showCategory = $showCategory ?? true;
    $image = $product->images->first();
@endphp
<article class="store-card-interactive group/card overflow-hidden">
    <a href="{{ route('shop.product', [$product->category, $product]) }}" class="relative block aspect-[4/5] overflow-hidden bg-gradient-to-b from-white to-ink-100/80">
        @if ($image)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($image->path) }}" alt="" class="size-full object-cover transition duration-700 ease-out group-hover/card:scale-[1.03] motion-reduce:group-hover/card:scale-100">
        @else
            <div class="flex size-full items-center justify-center bg-gradient-to-br from-ink-800 to-ink-950 text-center text-xs font-bold uppercase tracking-mega text-white/40">Photo soon</div>
        @endif
        <span class="pointer-events-none absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-ink-950/20 to-transparent opacity-0 transition duration-300 group-hover/card:opacity-100 motion-reduce:opacity-0"></span>
    </a>
    <div class="space-y-1.5 p-6">
        @if ($showCategory)
            <p class="ui-eyebrow text-ink-400">{{ $product->category->name }}</p>
        @endif
        <h3 class="font-display text-base font-bold uppercase leading-snug tracking-wide text-ink-950">
            <a href="{{ route('shop.product', [$product->category, $product]) }}" class="transition hover:text-accent-600">{{ $product->name }}</a>
        </h3>
        <p class="pt-2 font-display text-lg font-semibold tabular-nums tracking-tight text-ink-950">
            {{ config('store.currency_symbol') }}{{ number_format((float) $product->price, 2) }}
        </p>
    </div>
</article>
