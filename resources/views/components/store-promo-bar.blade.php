{{-- Gymshark-style rotating promo strip (demo copy — not affiliated with any third-party brand) --}}
@php
    $items = [
        'Free standard shipping on orders over ' . config('store.currency_symbol') . '75',
        'New drop every month · limited colourways',
        'Students save 10% with verified ID',
        'Sign up for first-order perks & restock news',
    ];
@endphp
<div class="relative z-[60] border-b border-white/[0.07] bg-zinc-950 text-[10px] font-semibold uppercase tracking-[0.16em] text-white/85 sm:text-[11px] sm:tracking-mega">
    <div class="overflow-hidden py-2.5">
        <div class="flex w-max animate-marquee motion-reduce:animate-none">
            <ul class="flex items-center gap-x-10 px-5" aria-hidden="true">
                @foreach ($items as $line)
                    <li class="flex shrink-0 items-center gap-2">
                        <span class="size-1.5 shrink-0 rounded-full bg-accent-500" aria-hidden="true"></span>
                        <span>{{ $line }}</span>
                    </li>
                @endforeach
            </ul>
            <ul class="flex items-center gap-x-10 px-5" aria-hidden="true">
                @foreach ($items as $line)
                    <li class="flex shrink-0 items-center gap-2">
                        <span class="size-1.5 shrink-0 rounded-full bg-accent-500" aria-hidden="true"></span>
                        <span>{{ $line }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
