@props([
    'fetchUrl',
    'thumbnailUrl',
])

<div
    x-data="mediaLibraryModal(@js($fetchUrl), @js($thumbnailUrl))"
    @open-media-picker.window="openFor($event.detail)"
    @keydown.escape.window="close()"
    class="fixed inset-0 z-50 pointer-events-none"
    x-cloak
    :class="{ 'pointer-events-auto': open }"
>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-ink-950/50 backdrop-blur-sm"
        @click="close()"
    ></div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200 transform"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150 transform"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute inset-4 mx-auto flex max-w-4xl flex-col overflow-hidden rounded-2xl border border-zinc-200/90 bg-white shadow-elevated sm:inset-x-8 sm:inset-y-8 lg:inset-x-auto lg:inset-y-10 lg:w-full"
        @click.stop
    >
        <div class="flex shrink-0 items-center justify-between gap-4 border-b border-zinc-200/80 bg-gradient-to-b from-zinc-50 to-white px-6 py-4">
            <div class="min-w-0">
                <h3 class="text-sm font-semibold text-zinc-900">Choose from library</h3>
                <p class="mt-0.5 truncate text-xs text-zinc-500" x-show="view === 'folders'">Pick a folder to browse — files already uploaded to R2 that aren't attached to a product yet.</p>
                <p class="mt-0.5 truncate text-xs text-zinc-500" x-show="view === 'files'" x-text="activeFolder"></p>
            </div>
            <button
                @click="close()"
                class="inline-flex size-9 shrink-0 items-center justify-center rounded-xl text-ink-500 transition hover:bg-zinc-100 hover:text-ink-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-accent-500/30"
                aria-label="Close"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="flex shrink-0 items-center gap-3 border-b border-zinc-200/80 px-6 py-3" x-show="view === 'files'">
            <button
                type="button"
                class="inline-flex shrink-0 items-center gap-1 text-sm font-medium text-zinc-600 transition hover:text-accent-700"
                @click="backToFolders()"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Folders
            </button>
            <input
                type="text"
                x-model="query"
                placeholder="Filter within this folder…"
                class="form-input"
            >
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-4" x-ref="scrollContainer">
            <template x-if="$store.mediaLibrary.loading">
                <p class="py-12 text-center text-sm text-zinc-500">Loading library…</p>
            </template>

            <template x-if="$store.mediaLibrary.error">
                <p class="py-12 text-center text-sm text-red-600" x-text="$store.mediaLibrary.error"></p>
            </template>

            <template x-if="!$store.mediaLibrary.loading && !$store.mediaLibrary.error && view === 'folders' && folders.length === 0">
                <p class="py-12 text-center text-sm text-zinc-500">No unattached files left — everything's been assigned.</p>
            </template>

            <ul
                class="grid grid-cols-2 gap-3 sm:grid-cols-3"
                x-show="!$store.mediaLibrary.loading && !$store.mediaLibrary.error && view === 'folders'"
            >
                <template x-for="folder in folders" :key="folder.name">
                    <li>
                        <button
                            type="button"
                            class="flex w-full flex-col items-start gap-2 rounded-xl border border-zinc-200/90 bg-zinc-50/60 p-4 text-left transition hover:border-accent-300 hover:bg-accent-50/40"
                            @click="openFolder(folder.name)"
                        >
                            <svg class="h-6 w-6 text-accent-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-19.5 0v6a2.25 2.25 0 0 0 2.25 2.25h15a2.25 2.25 0 0 0 2.25-2.25v-6m-19.5 0v-.15c0-1.036.84-1.875 1.875-1.875h4.5c.5 0 .98.2 1.334.553l1.686 1.686a1.875 1.875 0 0 0 1.334.554h6.27c1.035 0 1.875.84 1.875 1.875v.107" />
                            </svg>
                            <span class="text-sm font-semibold text-zinc-900" x-text="folder.name"></span>
                            <span class="text-xs text-zinc-500" x-text="folder.total + ' files (' + folder.images + ' photos, ' + folder.videos + ' videos)'"></span>
                        </button>
                    </li>
                </template>
            </ul>

            <template x-if="!$store.mediaLibrary.loading && !$store.mediaLibrary.error && view === 'files' && filtered.length === 0">
                <p class="py-12 text-center text-sm text-zinc-500">No unattached files match.</p>
            </template>

            <ul
                class="grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-6"
                x-show="!$store.mediaLibrary.loading && view === 'files'"
            >
                <template x-for="item in filtered" :key="item.path">
                    <li
                        class="group relative aspect-square cursor-pointer overflow-hidden rounded-xl bg-zinc-100 ring-2 transition [content-visibility:auto] [contain-intrinsic-size:160px_160px]"
                        :class="item.selected ? 'ring-accent-600' : 'ring-ink-200/70 hover:ring-accent-300'"
                        :data-path="item.path"
                        x-init="observeTile($el, item)"
                        @click="toggle(item)"
                    >
                        <template x-if="item.visible && item.is_video">
                            <video :src="item.url" class="size-full object-cover" muted playsinline preload="metadata" @loadedmetadata="tileSettled(item)" x-on:error="tileSettled(item)"></video>
                        </template>
                        <template x-if="item.visible && !item.is_video">
                            <img :src="thumbSrc(item)" alt="" loading="lazy" decoding="async" class="size-full object-cover" @load="tileSettled(item)" x-on:error="tileSettled(item)">
                        </template>
                        <div x-show="!item.visible" class="absolute inset-0 animate-pulse bg-zinc-200"></div>

                        <span
                            class="pointer-events-none absolute inset-0 bg-accent-600/25"
                            x-show="item.selected"
                        ></span>
                        <span
                            class="pointer-events-none absolute right-1.5 top-1.5 inline-flex size-5 items-center justify-center rounded-full bg-white/95 text-accent-700 shadow-sm"
                            x-show="item.selected"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </span>

                        <span class="pointer-events-none absolute inset-x-0 bottom-0 truncate bg-gradient-to-t from-ink-950/75 to-transparent px-1.5 pb-1 pt-4 text-[9px] font-medium text-white" x-text="item.path"></span>
                    </li>
                </template>
            </ul>
        </div>

        <div class="flex shrink-0 items-center justify-between gap-4 border-t border-zinc-200/80 bg-zinc-50/80 px-6 py-3.5">
            <p class="text-xs text-zinc-500">
                <span x-text="selectedCount"></span> selected
            </p>
            <div class="flex gap-2">
                <button type="button" class="btn-secondary px-4 py-2 text-sm" @click="close()">Cancel</button>
                <button type="button" class="btn-primary px-4 py-2 text-sm" :disabled="selectedCount === 0" @click="confirm()">
                    Add selected
                </button>
            </div>
        </div>
    </div>
</div>
