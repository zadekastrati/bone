@props([
    'variant' => 'header',
])

@php
    $value = (string) request('q', '');
    $inputId = 'store-search-q-' . $variant;

    $formClass = match ($variant) {
        'header' => 'group/storesearch hidden min-w-0 flex-1 items-center gap-2 rounded-full border border-zinc-200/90 bg-white/90 px-3 py-2 shadow-sm transition sm:px-4 sm:py-2.5 lg:flex',

        'drawer' => 'mb-3 flex w-full items-center gap-2 rounded-xl border border-zinc-200/90 bg-white/95 px-3 py-2.5 shadow-sm',

        'shop' => 'group/storesearch flex w-full min-w-0 items-center gap-3 rounded-2xl border border-zinc-200/90 bg-white px-4 py-3 shadow-soft transition sm:px-5 sm:py-3.5',

        default => '',
    };
@endphp

<form
    method="GET"
    action="{{ route('shop.index') }}"
    role="search"
    {{ $attributes->class($formClass) }}
>
    <label for="{{ $inputId }}" class="sr-only">
        Search products
    </label>

    <button
        type="submit"
        aria-label="Search"
        class="flex shrink-0 rounded-lg p-1 text-ink-400 transition hover:bg-zinc-100/80 hover:text-accent-700 focus:outline-none focus:ring-0 focus-visible:ring-0"
    >
        <svg
            class="size-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"
            />
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
        class="min-w-0 flex-1 border-0 bg-transparent py-1 text-sm text-ink-800 placeholder:text-ink-400 focus:outline-none focus:ring-0 focus:border-0 focus:shadow-none"
    />
</form>
