@props([
    'count' => 0,
])

{{-- Fixed above promos/modals; outside main wrapper so nothing clips it --}}
<a
    href="{{ route('cart.index') }}"
    class="{{ request()->routeIs('cart.*') ? 'border-zinc-500 bg-zinc-100' : 'border-zinc-200/90 bg-white' }} pointer-events-auto fixed bottom-6 right-6 z-[100] flex size-14 items-center justify-center rounded-full border-2 text-ink-900 shadow-[0_10px_40px_-8px_rgba(0,0,0,0.25)] ring-2 ring-white/90 transition motion-safe:hover:scale-[1.05] motion-safe:hover:shadow-[0_14px_44px_-8px_rgba(0,0,0,0.3)] focus:outline-none focus-visible:ring-2 focus-visible:ring-accent-500/50 sm:bottom-8 sm:right-8"
    aria-label="{{ $count > 0 ? 'Shopping bag, '.$count.' items' : 'Shopping bag, empty' }}"
>
    <x-icons.shopping-bag class="size-[1.625rem] shrink-0" />
    @if ($count > 0)
        <span class="absolute -right-1 -top-1 flex min-h-[1.35rem] min-w-[1.35rem] items-center justify-center rounded-full bg-ink-900 px-1 text-[11px] font-bold leading-none text-white ring-2 ring-white">
            {{ $count > 99 ? '99+' : $count }}
        </span>
    @endif
</a>
