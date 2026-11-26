@props(['product'])

@if ($product->isOnSale())
    <span class="inline-flex flex-wrap items-baseline gap-2">
        <span class="text-ink-400 line-through decoration-ink-400/70">
            <x-price :amount="$product->original_price" />
        </span>
        <span class="font-bold text-accent-700">
            <x-price :amount="$product->price" />
        </span>
    </span>
@else
    <x-price :amount="$product->price" />
@endif
