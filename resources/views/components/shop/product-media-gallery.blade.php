@props([
    'product',
    'imagesByColor' => null,
    'defaultColor' => null,
])

@php
    $toItems = fn ($images) => $images->map(fn ($image) => [
        'id' => $image->id,
        'url' => $image->url(),
        'thumbUrl' => $image->thumbUrl(),
        'displayUrl' => $image->displayUrl(),
        'isVideo' => $image->isVideo(),
        'alt' => $product->name,
    ])->values()->all();

    $colorItems = collect($imagesByColor)->map($toItems)->all();

    // Show only the default color's photos to start (falling back to the
    // shared pool already baked into $colorItems by Product::imagesForColor,
    // or to every photo if the product has no colors at all). Clicking a
    // color swatch still swaps via colorItems below, unchanged.
    $items = ($defaultColor !== null && ! empty($colorItems[$defaultColor]))
        ? $colorItems[$defaultColor]
        : $toItems($product->images);
@endphp

@if ($product->images->isEmpty())
    <div class="aspect-[4/5] overflow-hidden rounded-3xl bg-gradient-to-br from-zinc-100 to-accent-200 shadow-float">
        <div class="flex size-full items-center justify-center text-xs font-bold uppercase tracking-mega text-white/40">Photo soon</div>
    </div>
@else
    <div
        class="space-y-4"
        x-data="{
            active: 0,
            items: @js($items),
            colorItems: @js($colorItems),
            preloadReady: false,
            setSlide(index) {
                this.active = index;
                this.syncVideos();
            },
            onColorChange(color) {
                const list = this.colorItems[color];
                if (list && list.length) {
                    this.items = list;
                }
                this.active = 0;
                this.preloadReady = false;
                this.syncVideos();
                this.schedulePreload();
            },
            next() {
                this.setSlide((this.active + 1) % this.items.length);
            },
            prev() {
                this.setSlide((this.active - 1 + this.items.length) % this.items.length);
            },
            syncVideos() {
                this.$nextTick(() => {
                    this.$refs.stage.querySelectorAll('[data-gallery-video]').forEach((video, index) => {
                        if (index === this.active) {
                            video.play().catch(() => {});
                        } else {
                            video.pause();
                            video.currentTime = 0;
                        }
                    });
                });
            },
            // Only the active slide's full-size image is requested right
            // away; the rest of this color's photos are held back briefly
            // so they don't compete with it for the connection on the very
            // first load. They still finish well before a shopper reacts
            // and clicks next/prev, which is what keeps that interaction
            // feeling instant.
            schedulePreload() {
                setTimeout(() => { this.preloadReady = true; }, 400);
            },
            init() {
                this.syncVideos();
                this.schedulePreload();
            },
        }"
        x-on:product-color-selected.window="onColorChange($event.detail.color)"
    >
        <div class="relative">
            <div x-ref="stage" class="store-card overflow-hidden bg-ink-100 shadow-elevated">
                <template x-for="(item, index) in items" :key="item.id">
                    <div x-show="active === index" x-cloak>
                        {{--
                            scale-[1.02]: Safari/iOS has a known object-fit:
                            cover rendering bug that leaves a thin black
                            sub-pixel line at the left/right edges when the
                            media's dimensions don't divide evenly into the
                            container's — invisible on other browsers, real on
                            iPhone. Slightly overscaling the media so it
                            overflows its box (clipped by the stage's own
                            overflow-hidden) hides that gap without any
                            visible zoom or layout change.
                        --}}
                        <video
                            x-show="item.isVideo"
                            data-gallery-video
                            :src="item.url"
                            class="aspect-[4/5] w-full scale-[1.02] object-cover"
                            autoplay
                            loop
                            muted
                            playsinline
                            preload="metadata"
                        ></video>
                        <img
                            x-show="!item.isVideo"
                            :src="(index === active || preloadReady) ? item.displayUrl : null"
                            :alt="item.alt"
                            :fetchpriority="index === active ? 'high' : 'low'"
                            decoding="async"
                            class="aspect-[4/5] w-full scale-[1.02] object-cover"
                        >
                    </div>
                </template>
            </div>

            <template x-if="items.length > 1">
                <div>
                    <button
                        type="button"
                        class="absolute left-3 top-1/2 inline-flex size-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-ink-900 shadow-md ring-1 ring-ink-200/70 transition hover:bg-white"
                        @click="prev()"
                        aria-label="Previous media"
                    >
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="absolute right-3 top-1/2 inline-flex size-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-ink-900 shadow-md ring-1 ring-ink-200/70 transition hover:bg-white"
                        @click="next()"
                        aria-label="Next media"
                    >
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        <template x-if="items.length > 1">
            <div class="grid grid-cols-4 gap-3 sm:grid-cols-5">
                <template x-for="(item, index) in items" :key="'thumb-' + item.id">
                    <button
                        type="button"
                        class="relative overflow-hidden rounded-xl ring-1 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-accent-500/40"
                        :class="active === index ? 'ring-accent-500 ring-2' : 'ring-ink-200/60 hover:ring-ink-300'"
                        @click="setSlide(index)"
                        :aria-label="'Show media ' + (index + 1)"
                    >
                        {{--
                            onloadedmetadata seeks to a hair past the start once
                            metadata is available, forcing a frame to actually
                            decode and paint. Without it, iOS Safari leaves a
                            non-autoplaying, not-yet-played <video> blank —
                            preload="metadata" alone doesn't get it to render a
                            visible frame there the way it does on desktop
                            browsers. Same technique already used for this exact
                            reason in the admin product gallery grid.
                        --}}
                        <video
                            x-show="item.isVideo"
                            :src="item.url"
                            class="aspect-square size-full object-cover"
                            muted
                            playsinline
                            preload="metadata"
                            onloadedmetadata="this.currentTime=0.1"
                        ></video>
                        <img
                            x-show="!item.isVideo"
                            :src="item.thumbUrl"
                            alt=""
                            loading="lazy"
                            decoding="async"
                            class="aspect-square size-full object-cover"
                        >
                    </button>
                </template>
            </div>
        </template>
    </div>
@endif
