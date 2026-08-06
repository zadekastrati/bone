@props([
    'title',
    'description' => null,
    'flush' => false,
    'variant' => 'default',
])

@php
    $isMinimal = $variant === 'minimal';
@endphp

<section {{ $attributes->merge(['class' => 'admin-panel overflow-hidden']) }}>
    <header @class([
        'flex flex-wrap items-start justify-between gap-3 sm:items-center',
        'border-b border-zinc-200 bg-white px-6 py-3.5' => $isMinimal,
        'border-b border-zinc-100 bg-gradient-to-r from-zinc-50 to-white px-5 py-4 sm:px-6' => ! $isMinimal,
    ])>
        <div class="min-w-0 flex-1">
            <h2 @class([
                'text-sm font-semibold leading-snug text-zinc-900' => $isMinimal,
                'font-display text-xs font-bold uppercase tracking-[0.2em] text-ink-900' => ! $isMinimal,
            ])>{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-sm leading-relaxed text-zinc-500">{{ $description }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                {{ $actions }}
            </div>
        @endisset
    </header>
    @if ($flush)
        {{ $slot }}
    @else
        <div @class([
            'space-y-5 p-6 sm:space-y-6' => $isMinimal,
            'space-y-5 p-5 sm:space-y-6 sm:p-6' => ! $isMinimal,
        ])>
            {{ $slot }}
        </div>
    @endif
</section>
