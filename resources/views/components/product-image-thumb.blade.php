@props([
    'path' => null,
    'size' => 'md',
])

@php
    use Illuminate\Support\Facades\Storage;

    $box = match ($size) {
        'sm' => 'size-10 rounded-md',
        'md' => 'size-14 rounded-lg',
        'grid' => 'aspect-[4/5] w-full overflow-hidden rounded-lg bg-zinc-50 ring-1 ring-zinc-200/50',
        /**
         * Cart row: readable square thumb (list layout — not full-width grid).
         */
        'cartRow' => 'block !h-[72px] !w-[72px] shrink-0 overflow-hidden rounded-lg bg-zinc-50 object-cover object-center ring-1 ring-zinc-200/60',
        'editorial' => 'mx-auto block !h-10 !w-10 shrink-0 overflow-hidden rounded-md bg-zinc-50 object-cover object-center ring-1 ring-zinc-200/60',
        default => 'size-14 rounded-lg',
    };
    $icon = match ($size) {
        'sm' => 'size-5',
        'md' => 'size-7',
        'grid' => 'size-10',
        'cartRow' => 'size-10',
        'editorial' => 'size-5',
        default => 'size-7',
    };
    $imgFit = match ($size) {
        'grid' => 'h-full w-full object-cover object-center',
        'cartRow' => '',
        'editorial' => '',
        default => 'h-full w-full object-cover',
    };

    [$imgW, $imgH] = match ($size) {
        'cartRow' => [72, 72],
        'editorial' => [40, 40],
        default => [160, 200],
    };
@endphp

@if ($path)
    <img
        src="{{ Storage::url($path) }}"
        alt=""
        width="{{ $imgW }}"
        height="{{ $imgH }}"
        {{ $attributes->merge(['class' => trim($box.' '.$imgFit)]) }}
        @if ($size === 'grid')
            sizes="(max-width: 640px) 42vw, (max-width: 1024px) 28vw, (max-width: 1280px) 20vw, 160px"
            decoding="async"
        @elseif ($size === 'editorial')
            sizes="40px"
            decoding="async"
            loading="lazy"
        @elseif ($size === 'cartRow')
            sizes="72px"
            decoding="async"
            loading="lazy"
        @endif
    >
@else
    <span {{ $attributes->merge(['class' => match ($size) {
        'editorial' => 'flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-md bg-zinc-50 ring-1 ring-zinc-200/60',
        'cartRow' => 'flex size-[72px] shrink-0 items-center justify-center overflow-hidden rounded-lg bg-zinc-50 ring-1 ring-zinc-200/60',
        default => 'flex '.$box.' shrink-0 items-center justify-center bg-zinc-50 ring-1 ring-zinc-200/40',
    }]) }} aria-hidden="true">
        <svg class="{{ $icon }} text-zinc-300" fill="none" viewBox="0 0 24 24" stroke-width="1.25" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3A1.5 1.5 0 0 0 1.5 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
        </svg>
    </span>
@endif
