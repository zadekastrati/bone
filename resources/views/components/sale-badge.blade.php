@props(['product', 'size' => 'default'])

@if ($product->isOnSale())
    @if ($size === 'sm')
        <span class="pointer-events-none absolute right-1.5 top-1.5 z-20 inline-flex items-center rounded-full bg-red-600 px-2 py-1 shadow-soft" aria-hidden="true">
            <span class="text-[8px] font-bold uppercase leading-none tracking-[0.12em] text-white">
                {{ __('-:percent%', ['percent' => $product->discount_percent]) }}
            </span>
        </span>
    @else
        <span class="pointer-events-none absolute right-2 top-2 z-20 inline-flex items-center rounded-full bg-red-600 px-3 py-1.5 shadow-soft sm:right-3 sm:top-3" aria-hidden="true">
            <span class="text-[10px] font-bold uppercase leading-none tracking-[0.15em] text-white sm:text-[11px]">
                {{ __('-:percent%', ['percent' => $product->discount_percent]) }}
            </span>
        </span>
    @endif
@endif
