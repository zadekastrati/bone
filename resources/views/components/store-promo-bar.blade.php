{{-- Gymshark-style rotating promo strip (demo copy — not affiliated with any third-party brand) --}}
@php
    $blackFridayActive = now()->between(
        \Illuminate\Support\Carbon::parse('2026-11-27 00:00:00', 'Europe/Belgrade'),
        \Illuminate\Support\Carbon::parse('2026-11-29 00:00:00', 'Europe/Belgrade'),
    );

    $items = [
        ['text' => __('Free shipping on orders over :amount', ['amount' => config('store.currency_symbol') . '100'])],
        ['text' => __('Cash on delivery available in Kosovo, North Macedonia & Albania')],
        ['text' => __('Sign up for first-order perks & restock news')],
    ];

    if ($blackFridayActive) {
        array_unshift($items, ['text' => __('Black Friday: up to 50% off selected items'), 'highlight' => true]);
    }
@endphp
<div class="relative z-[60] border-b border-zinc-200/80 bg-gradient-to-r from-zinc-100 via-zinc-100 to-zinc-100 text-[10px] font-semibold uppercase tracking-[0.16em] text-ink-700 sm:text-[11px] sm:tracking-mega">
    <div class="overflow-hidden py-2.5">
        <div class="flex w-max animate-marquee motion-reduce:animate-none">
            <ul class="flex items-center gap-x-10 px-5" aria-hidden="true">
                @foreach ($items as $item)
                    <li class="flex shrink-0 items-center gap-2 {{ ($item['highlight'] ?? false) ? 'text-red-600' : '' }}">
                        <span class="size-1.5 shrink-0 rounded-full {{ ($item['highlight'] ?? false) ? 'bg-red-500' : 'bg-accent-400' }}" aria-hidden="true"></span>
                        <span>{{ $item['text'] }}</span>
                    </li>
                @endforeach
            </ul>
            <ul class="flex items-center gap-x-10 px-5" aria-hidden="true">
                @foreach ($items as $item)
                    <li class="flex shrink-0 items-center gap-2 {{ ($item['highlight'] ?? false) ? 'text-red-600' : '' }}">
                        <span class="size-1.5 shrink-0 rounded-full {{ ($item['highlight'] ?? false) ? 'bg-red-500' : 'bg-accent-400' }}" aria-hidden="true"></span>
                        <span>{{ $item['text'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
