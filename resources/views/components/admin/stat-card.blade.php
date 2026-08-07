@props([
    'label',
    'icon',
    'href' => null,
    'linkText' => null,
])

<div {{ $attributes->merge(['class' => 'stat-card']) }}>
    <span class="stat-card__icon">
        <x-dynamic-component :component="'icons.'.$icon" class="h-5 w-5" />
    </span>
    <div class="min-w-0 flex-1">
        <p class="text-xs font-bold uppercase tracking-mega text-ink-500">{{ $label }}</p>
        <div class="mt-2 font-display text-3xl font-bold text-ink-900">{{ $slot }}</div>
        @if ($href)
            <a href="{{ $href }}" class="mt-3 inline-block text-xs font-semibold text-accent-700 hover:text-accent-600">{{ $linkText ?? 'View →' }}</a>
        @endif
    </div>
</div>
