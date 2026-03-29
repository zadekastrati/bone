@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'mb-10 flex flex-col gap-6 border-b border-zinc-200/60 pb-8 sm:mb-12 sm:flex-row sm:items-end sm:justify-between sm:gap-8 sm:pb-10']) }}>
    <div class="min-w-0">
        <h1 class="heading-page">{{ $title }}</h1>
        @if ($subtitle)
            <p class="text-muted mt-3 max-w-2xl">{{ $subtitle }}</p>
        @endif
    </div>
    @if (trim($slot) !== '')
        <div class="flex shrink-0 flex-wrap items-center gap-3">
            {{ $slot }}
        </div>
    @endif
</div>
