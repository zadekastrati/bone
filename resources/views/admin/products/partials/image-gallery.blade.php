@php
    $colorSections = collect([null])
        ->concat($product->variants->pluck('color')->unique()->values());
@endphp

<div class="space-y-8">
    @foreach ($colorSections as $color)
        <div class="space-y-3">
            <div>
                <h4 class="text-sm font-semibold text-zinc-900">{{ $color ?? 'All colors' }}</h4>
                @if ($color === null)
                    <p class="text-xs text-zinc-500">Shared photos, shown on the storefront for any color that has no photos of its own.</p>
                @endif
            </div>

            @include('admin.products.partials.image-gallery-grid', [
                'product' => $product,
                'color' => $color,
                'images' => $product->images->where('color', $color)->values(),
            ])

            <x-admin.product-media-upload
                :input-id="'product-media-upload-' . ($color !== null ? \Illuminate\Support\Str::slug($color) : 'all')"
                :input-name="$color !== null ? 'variant_images[' . $color . '][]' : 'images[]'"
                label="Add images or video"
            />

            <x-admin.product-media-library-picker
                :input-name="$color !== null ? 'attach_variant_images[' . $color . '][]' : 'attach_images[]'"
            />
        </div>
    @endforeach

    @error('thumbnail_image_id')
        <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror

    @error('attach_images')
        <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror

    <x-admin.media-library-modal
        :fetch-url="route('admin.media-library.index')"
        :thumbnail-url="route('admin.media-library.thumbnail')"
    />
</div>
