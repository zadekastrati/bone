@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        <h1 class="heading-page">{{ $title }}</h1>
        @if ($subtitle)
            <p class="text-muted mt-2 max-w-2xl">{{ $subtitle }}</p>
        @endif
    </div>
    @if (trim($slot) !== '')
        <div class="flex shrink-0 flex-wrap items-center gap-3">
            {{ $slot }}
        </div>
    @endif
</div>
