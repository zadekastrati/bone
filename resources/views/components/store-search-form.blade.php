@props([
    'variant' => 'header',
])

@php
    $value = (string) request('q', '');
    $inputId = 'store-search-q-'.$variant;
    $formClass = match ($variant) {
        'header' => 'group/storesearch hidden min-w-0 flex-1 items-center gap-2 rounded-full border border-white/15 bg-white/[0.06] px-3 py-2 transition focus-within:border-white/30 focus-within:bg-white/[0.09] sm:px-4 sm:py-2.5 lg:flex',
        'drawer' => 'mb-3 flex w-full items-center gap-2 rounded-xl border border-white/12 bg-white/[0.06] px-3 py-2.5',
        default => '',
    };
@endphp

<form method="GET" action="{{ route('shop.index') }}" role="search" {{ $attributes->class($formClass) }}>
    <label for="{{ $inputId }}" class="sr-only">Search products</label>
    <button type="submit" class="flex shrink-0 rounded-lg p-1 text-white/45 transition hover:bg-white/10 hover:text-white/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/40" aria-label="Search">
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
    </button>
    <input
        type="search"
        name="q"
        id="{{ $inputId }}"
        value="{{ $value }}"
        placeholder="What are you looking for today?"
        maxlength="200"
        autocomplete="off"
        class="min-w-0 flex-1 border-0 bg-transparent py-1 text-sm text-white placeholder:text-white/45 focus:ring-0"
    />
</form>
