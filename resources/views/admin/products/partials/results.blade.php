<div
    x-data="{
        detailModal: false,
        selectedProduct: null,
        activeMedia: 0,
        products: @js($products->map(function ($p) {
            $thumb = $p->thumbnailImage();

            $sizeRank = ['XS' => 0, 'S' => 1, 'M' => 2, 'L' => 3, 'XL' => 4, 'XXL' => 5];
            $colorOrder = $p->variants->pluck('color')->unique()->values()->flip();
            $sortedVariants = $p->variants->sortBy(fn ($v) => sprintf(
                '%03d-%02d',
                $colorOrder[$v->color] ?? 0,
                $sizeRank[strtoupper(trim($v->size))] ?? 99,
            ))->values();

            return [
                'id' => $p->id,
                'slug' => $p->slug,
                'name' => $p->name,
                'category' => $p->category->name,
                'price' => $p->price,
                'is_active' => $p->is_active,
                'description' => $p->description,
                'style_code' => $p->style_code,
                'images' => $p->images->map(fn($img) => [
                    'id' => $img->id,
                    'url' => Storage::url($img->path),
                    'is_video' => $img->isVideo(),
                    'is_thumbnail' => $img->is_thumbnail,
                ])->toArray(),
                'total_stock' => $p->variants->sum('stock_quantity'),
                'variants' => $sortedVariants->map(fn($v) => [
                    'id' => $v->id,
                    'color' => $v->color,
                    'color_hex' => $v->color_hex,
                    'size' => $v->size,
                    'sku' => $v->sku,
                    'stock_quantity' => $v->stock_quantity,
                ])->toArray(),
            ];
        })->values()->toArray()),
        openDetail(product) {
            this.selectedProduct = product;
            const thumbIdx = product.images.findIndex((m) => m.is_thumbnail);
            this.activeMedia = thumbIdx !== -1 ? thumbIdx : 0;
            this.detailModal = true;
        }
    }"
    class="relative"
>
    {{-- Table --}}
    <div class="table-shell--admin mt-10">
        <div class="overflow-x-auto">
            <table class="data-table data-table--admin">
                <thead>
                    <tr>
                        <th class="w-10"><span class="sr-only">Select</span></th>
                        <th></th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Variants</th>
                        <th>Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr
                            class="cursor-pointer transition hover:bg-slate-50"
                            @click="openDetail(products[{{ $loop->index }}])"
                        >
                            <td class="w-10" @click.stop>
                                <input type="checkbox" name="ids[]" value="{{ $product->id }}" form="products-bulk-delete-form" class="js-select-product" aria-label="Select {{ $product->name }}">
                            </td>
                            <td class="w-24">
                                @php $thumb = $product->thumbnailImage(); @endphp
                                @if ($thumb && ! $thumb->isVideo())
                                    <img src="{{ Storage::url($thumb->path) }}" alt="{{ $product->name }} thumbnail" class="h-14 w-14 rounded-xl object-cover" loading="lazy">
                                @elseif ($thumb && $thumb->isVideo())
                                    <span class="relative inline-block h-14 w-14 overflow-hidden rounded-xl ring-1 ring-zinc-200">
                                        <video src="{{ $thumb->url() }}" class="size-full object-cover" muted playsinline preload="metadata"></video>
                                        <span class="pointer-events-none absolute inset-0 flex items-center justify-center bg-ink-950/35" aria-hidden="true">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1" fill="#fff"/><rect x="14" y="5" width="4" height="14" rx="1" fill="#fff"/></svg>
                                        </span>
                                    </span>
                                @else
                                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-zinc-100 text-ink-400 text-xs uppercase tracking-[0.15em]">No image</div>
                                @endif
                            </td>
                            <td>
                                <span class="font-medium text-ink-900">{{ $product->name }}</span>
                                <span class="mt-0.5 block font-mono text-xs text-ink-500">{{ $product->slug }}</span>
                            </td>
                            <td class="text-ink-600">{{ $product->category->name }}</td>
                            <td class="font-semibold text-ink-900">{{ config('store.currency_symbol') }}{{ number_format((float) $product->price, 2) }}</td>
                            <td class="text-ink-600">{{ $product->variants_count }}</td>
                            <td>
                                <x-admin.badge :tone="$product->is_active ? 'success' : 'neutral'">{{ $product->is_active ? 'Yes' : 'No' }}</x-admin.badge>
                            </td>
                            <td class="text-right" @click.stop>
                                <div class="inline-flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex size-9 items-center justify-center rounded-lg text-accent-700 transition hover:bg-accent-50 hover:text-accent-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-accent-500/30" title="Edit" aria-label="Edit product">
                                        <x-icons.pencil-square class="h-5 w-5" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline" data-confirm="Archive this product?" data-confirm-label="Archive">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex size-9 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-50 hover:text-red-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/30" title="Archive" aria-label="Archive product">
                                            <x-icons.archive-box class="h-5 w-5" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="data-table-empty text-ink-500">No products match.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Detail Modal --}}
    @include('admin.products.partials.detail-modal')
</div>

<div class="pagination-wrap pagination-wrap--admin">
    {{ $products->links() }}
</div>
