{{-- Archived Product Detail Slide-Over --}}
<div
    class="fixed inset-0 z-50 pointer-events-none"
    x-cloak
    :class="{ 'pointer-events-auto': detailModal }"
    @keydown.escape.window="detailModal = false"
>
    {{-- Backdrop --}}
    <div
        x-show="detailModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-ink-950/50 backdrop-blur-sm"
        @click="detailModal = false"
    ></div>

    {{-- Panel --}}
    <div
        x-show="detailModal"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="absolute right-0 top-0 bottom-0 flex w-full max-w-lg flex-col border-l border-zinc-200/90 bg-white shadow-elevated"
        @click.stop
    >
        {{-- Header --}}
        <div class="shrink-0 border-b border-zinc-200/80 bg-gradient-to-b from-zinc-50 to-white">
            <div class="flex items-center justify-between px-6 pt-5">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-accent-600" x-text="selectedProduct?.category"></p>
                <button
                    @click="detailModal = false"
                    class="inline-flex size-9 shrink-0 items-center justify-center rounded-xl text-ink-500 transition hover:bg-zinc-100 hover:text-ink-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-accent-500/30"
                    aria-label="Close"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Media slider --}}
            <div class="px-6 pt-2">
                <div class="group relative h-28 w-full overflow-hidden rounded-xl border border-zinc-200/80 bg-white opacity-80 shadow-soft">
                    <div
                        class="flex h-full transition-transform duration-300 ease-out-expo"
                        :style="`transform: translateX(-${activeMedia * 100}%)`"
                    >
                        <template x-for="media in (selectedProduct?.images ?? [])" :key="media.id">
                            <div class="h-full w-full shrink-0">
                                <video x-show="media.is_video" :src="media.url" class="size-full object-contain" autoplay loop muted playsinline preload="metadata"></video>
                                <img x-show="!media.is_video" :src="media.url" :alt="selectedProduct?.name" class="size-full object-contain">
                            </div>
                        </template>
                    </div>

                    <template x-if="!selectedProduct?.images?.length">
                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-1 text-ink-300">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 9.75h18M3 4.5h18M3.75 19.5h16.5a.75.75 0 00.75-.75V5.25a.75.75 0 00-.75-.75H3.75a.75.75 0 00-.75.75v13.5a.75.75 0 00.75.75z" />
                            </svg>
                            <span class="text-[10px] font-semibold uppercase tracking-wider">No media</span>
                        </div>
                    </template>

                    {{-- Prev / next arrows --}}
                    <button
                        x-show="selectedProduct?.images?.length > 1"
                        @click="activeMedia = (activeMedia - 1 + selectedProduct.images.length) % selectedProduct.images.length"
                        class="absolute left-1.5 top-1/2 inline-flex size-6 -translate-y-1/2 items-center justify-center rounded-full bg-ink-950/50 text-white opacity-0 transition group-hover:opacity-100 hover:bg-ink-950/70"
                        aria-label="Previous media"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button
                        x-show="selectedProduct?.images?.length > 1"
                        @click="activeMedia = (activeMedia + 1) % selectedProduct.images.length"
                        class="absolute right-1.5 top-1/2 inline-flex size-6 -translate-y-1/2 items-center justify-center rounded-full bg-ink-950/50 text-white opacity-0 transition group-hover:opacity-100 hover:bg-ink-950/70"
                        aria-label="Next media"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    {{-- Dot indicators --}}
                    <div
                        x-show="selectedProduct?.images?.length > 1"
                        class="absolute bottom-1.5 left-1/2 flex -translate-x-1/2 gap-1"
                    >
                        <template x-for="(media, i) in (selectedProduct?.images ?? [])" :key="'dot-'+media.id">
                            <button
                                @click="activeMedia = i"
                                class="h-1.5 rounded-full transition-all"
                                :class="activeMedia === i ? 'w-4 bg-white' : 'w-1.5 bg-white/50 hover:bg-white/80'"
                                :aria-label="`Go to media ${i + 1}`"
                            ></button>
                        </template>
                    </div>
                </div>
            </div>

            <div class="px-6 pb-5 pt-3">
                <h2 class="truncate font-display text-xl font-bold leading-tight text-ink-950" x-text="selectedProduct?.name"></h2>
                <p class="mt-1 font-mono text-xs text-ink-400" x-text="selectedProduct?.slug"></p>
            </div>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
            {{-- Price + status --}}
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-ink-500">Price</p>
                    <p class="mt-0.5 font-display text-3xl font-bold text-ink-950" x-text="`{{ config('store.currency_symbol') }}${parseFloat(selectedProduct?.price ?? 0).toFixed(2)}`"></p>
                </div>
                <div class="text-right">
                    <x-admin.badge tone="neutral">Archived</x-admin.badge>
                    <p class="mt-1.5 text-xs text-ink-400" x-text="selectedProduct?.archived_at"></p>
                </div>
            </div>

            {{-- Quick stats --}}
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl border border-zinc-200/80 bg-zinc-50/70 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-ink-500">Total Stock</p>
                    <p class="mt-1 text-2xl font-bold text-ink-950" x-text="selectedProduct?.total_stock ?? 0"></p>
                </div>
                <div class="rounded-xl border border-zinc-200/80 bg-zinc-50/70 p-4">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-ink-500">Variants</p>
                    <p class="mt-1 text-2xl font-bold text-ink-950" x-text="selectedProduct?.variants?.length ?? 0"></p>
                </div>
            </div>

            {{-- Description --}}
            <template x-if="selectedProduct?.description">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-ink-500 mb-2">Description</p>
                    <p class="text-sm leading-relaxed text-ink-700" x-text="selectedProduct.description"></p>
                </div>
            </template>

            {{-- Style code --}}
            <template x-if="selectedProduct?.style_code">
                <div class="flex items-center justify-between rounded-xl border border-zinc-200/80 bg-zinc-50/70 px-4 py-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-ink-500">Style Code</span>
                    <code class="font-mono text-sm font-semibold text-ink-900" x-text="selectedProduct.style_code"></code>
                </div>
            </template>

            {{-- Variants --}}
            <template x-if="selectedProduct?.variants?.length">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-ink-500 mb-3">Variants</p>
                    <div class="overflow-hidden rounded-xl border border-zinc-200/80">
                        <template x-for="(variant, idx) in selectedProduct.variants" :key="variant.id">
                            <div
                                class="flex items-center gap-3 px-4 py-3"
                                :class="idx !== 0 ? 'border-t border-zinc-200/70' : ''"
                            >
                                <span
                                    class="size-4 shrink-0 rounded-full border border-zinc-300 shadow-sm"
                                    :style="variant.color_hex ? `background-color: ${variant.color_hex}` : 'background: repeating-linear-gradient(45deg,#e4e4e7,#e4e4e7 3px,#fafafa 3px,#fafafa 6px)'"
                                ></span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-ink-900">
                                        <span x-text="variant.color"></span>
                                        <span class="text-ink-400">&middot;</span>
                                        <span x-text="variant.size"></span>
                                    </p>
                                    <p class="font-mono text-xs text-ink-400" x-text="variant.sku || '—'"></p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <span
                                        class="badge"
                                        :class="variant.stock_quantity > 0 ? 'badge-success' : 'badge-danger'"
                                        x-text="variant.stock_quantity > 0 ? `${variant.stock_quantity} in stock` : 'Out of stock'"
                                    ></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="!selectedProduct?.variants?.length">
                <p class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50/60 px-4 py-6 text-center text-sm text-ink-500">
                    No variants configured for this product.
                </p>
            </template>
        </div>

        {{-- Footer --}}
        <div class="flex shrink-0 gap-3 border-t border-zinc-200/80 bg-zinc-50/60 px-6 py-4">
            <form
                method="POST"
                class="flex-1"
                :action="selectedProduct ? `{{ url('admin/products') }}/${selectedProduct.slug}/restore` : '#'"
            >
                @csrf
                <button type="submit" class="btn-primary w-full">
                    <x-icons.arrow-uturn-left class="h-4 w-4" />
                    Restore
                </button>
            </form>
            <form
                method="POST"
                :action="selectedProduct ? `{{ url('admin/products') }}/${selectedProduct.slug}/force-delete` : '#'"
                data-confirm="Permanently delete this product? This cannot be undone — its images and videos will be removed too."
                data-confirm-label="Delete permanently"
            >
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">
                    <x-icons.trash class="h-4 w-4" />
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>
