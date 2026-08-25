@php
    $sectionKey = $color !== null ? \Illuminate\Support\Str::slug($color) : 'all';
@endphp

<div id="product-image-gallery-{{ $sectionKey }}" data-color-gallery data-color="{{ $color }}">
    @if ($images->isNotEmpty())
        <div
            class="grid grid-cols-6 gap-3"
            data-sortable-images
            data-reorder-url="{{ route('admin.products.images.reorder', $product) }}"
            data-color="{{ $color }}"
        >
            @foreach ($images as $img)
                <div
                    class="group relative aspect-square overflow-hidden rounded-xl ring-1 ring-ink-200/70 cursor-move"
                    draggable="true"
                    data-image-id="{{ $img->id }}"
                >
                    @if ($img->isVideo())
                        <video src="{{ $img->url() }}" class="size-full object-cover" muted playsinline preload="metadata" onloadedmetadata="this.currentTime=0.1"></video>
                        <span class="pointer-events-none absolute inset-0 flex items-center justify-center bg-ink-950/35" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1" fill="#fff"/><rect x="14" y="5" width="4" height="14" rx="1" fill="#fff"/></svg>
                        </span>
                    @else
                        <img src="{{ $img->thumbUrl() }}" alt="" loading="lazy" decoding="async" class="size-full object-cover">
                    @endif

                    <span class="pointer-events-none absolute inset-0 bg-gradient-to-t from-ink-950/70 via-transparent to-transparent opacity-0 transition group-hover:opacity-100"></span>

                    @unless ($img->isVideo())
                        <label class="absolute left-1.5 top-1.5 inline-flex cursor-pointer items-center gap-1 rounded-full bg-white/90 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-ink-700 opacity-0 shadow-sm ring-1 ring-ink-200/70 transition group-hover:opacity-100 has-[:checked]:opacity-100 has-[:checked]:bg-accent-600 has-[:checked]:text-white has-[:checked]:ring-accent-600">
                            <input
                                type="radio"
                                name="thumbnail_image_id"
                                value="{{ $img->id }}"
                                class="sr-only"
                                @checked(old('thumbnail_image_id', $product->images->firstWhere('is_thumbnail', true)?->id) == $img->id)
                            >
                            Thumb
                        </label>
                    @endunless

                    <div class="absolute right-1.5 top-1.5">
                        <button
                            type="submit"
                            form="delete-image-form-{{ $img->id }}"
                            class="inline-flex size-6 cursor-pointer items-center justify-center rounded-full bg-ink-950/60 text-white opacity-0 shadow-sm transition hover:bg-red-600 group-hover:opacity-100"
                            title="Delete photo"
                            aria-label="Delete photo"
                        >
                            <x-icons.trash class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-xs text-ink-400">No photos yet.</p>
    @endif
</div>
