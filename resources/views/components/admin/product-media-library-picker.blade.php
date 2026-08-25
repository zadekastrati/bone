@props([
    'inputName' => 'attach_images[]',
])

<div x-data="{ attached: [] }" {{ $attributes->merge(['class' => 'space-y-3']) }}>
    <button
        type="button"
        class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-700 shadow-sm transition hover:border-accent-300 hover:bg-accent-50"
        @click="$dispatch('open-media-picker', { onSelect: (items) => {
            attached = attached.concat(items.filter((item) => ! attached.some((a) => a.path === item.path)));
        } })"
    >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3 8.25V6a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 6v12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-2.25" />
        </svg>
        Choose from library
    </button>

    <ul class="grid grid-cols-6 gap-3" x-show="attached.length" x-cloak>
        <template x-for="(item, index) in attached" :key="item.path">
            <li class="group relative aspect-square overflow-hidden rounded-xl ring-1 ring-ink-200/70">
                <input type="hidden" name="{{ $inputName }}" :value="item.path">
                <video :src="item.url" x-show="item.is_video" class="size-full object-cover" muted playsinline preload="metadata" onloadedmetadata="this.currentTime=0.1"></video>
                <img :src="item.url" x-show="!item.is_video" alt="" class="size-full object-cover">
                <span class="pointer-events-none absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-ink-950/70 to-transparent"></span>
                <button
                    type="button"
                    class="absolute right-1 top-1 inline-flex size-6 items-center justify-center rounded-full bg-ink-950/60 text-white transition hover:bg-red-600"
                    @click="$store.mediaLibrary.unmark(item.path); attached.splice(index, 1)"
                    title="Remove from selection"
                    aria-label="Remove from selection"
                >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </li>
        </template>
    </ul>
</div>
