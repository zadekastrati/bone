@props(['product'])

@if ($product->isOnSale())
    <span class="inline-flex flex-wrap items-baseline gap-2">
        <span class="text-ink-500 line-through decoration-2 decoration-ink-500">
            <x-price :amount="$product->original_price" />
        </span>
        <span class="font-bold text-red-600">
            <x-price :amount="$product->price" />
        </span>
    </span>
@else
    <x-price :amount="$product->price" />
@endif
