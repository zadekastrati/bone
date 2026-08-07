@extends('layouts.admin')

@section('title', 'Edit product')

@section('content')
    <x-page-header title="Edit product" :subtitle="$product->name">
        <a href="{{ route('admin.products.index') }}" class="btn-secondary">Back</a>
    </x-page-header>

    @php
        $variantRows = old('variants', $product->variants->map(fn ($v) => [
            'id' => $v->id,
            'color' => $v->color,
            'size' => $v->size,
            'sku' => $v->sku ?? '',
            'stock_quantity' => (string) $v->stock_quantity,
            'color_hex' => $v->color_hex ?? '',
        ])->values()->all());
    @endphp

    <form
        method="POST"
        action="{{ route('admin.products.update', $product) }}"
        enctype="multipart/form-data"
        class="admin-pro-form mx-auto mt-8 max-w-4xl space-y-6"
        x-data="{ rows: @js($variantRows), addRow() { this.rows.push({ id: null, color: '', size: '', sku: '', stock_quantity: '0', color_hex: '' }); }, removeRow(i) { this.rows.splice(i, 1); } }"
    >
        @csrf
        @method('PUT')

        <x-admin.form-section title="Product information" variant="minimal">
            <div class="grid gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="category_id" class="form-label">Category</label>
                    <select name="category_id" id="category_id" class="form-select @error('category_id') form-input-error @enderror" required>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" class="form-input @error('name') form-input-error @enderror" required autocomplete="off">
                    @error('name')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="slug" class="form-label">URL slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $product->slug) }}" class="form-input font-mono text-sm @error('slug') form-input-error @enderror" required autocomplete="off">
                    @error('slug')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="style_code" class="form-label">Style code</label>
                    <input type="text" name="style_code" id="style_code" value="{{ old('style_code', $product->style_code) }}" class="form-input font-mono text-sm @error('style_code') form-input-error @enderror" autocomplete="off">
                    <p class="mt-2 text-xs text-zinc-500">Product style code is shared across all sizes and colors. If blank, variant SKUs will only be used when entered.</p>
                    @error('style_code')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="6" class="form-input @error('description') form-input-error @enderror">{{ old('description', $product->description) }}</textarea>
                    @error('description')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="price" class="form-label">Price</label>
                    <div class="relative mt-1.5">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm text-zinc-500">$</span>
                        <input type="number" name="price" id="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" class="form-input pl-8 @error('price') form-input-error @enderror" required>
                    </div>
                    @error('price')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <fieldset class="space-y-4 rounded-3xl border border-ink-200/60 bg-white/95 p-6 shadow-soft">
                <legend class="font-display text-sm font-bold uppercase tracking-wide text-ink-950">Media</legend>

                @if ($product->images->isNotEmpty())
                    <div class="space-y-3">
                        <p class="text-xs font-bold uppercase tracking-mega text-ink-500">Existing files</p>
                        <ul class="space-y-3">
                            @foreach ($product->images as $img)
                                <li class="flex flex-wrap items-center gap-3 rounded-xl border border-ink-200/60 bg-white/80 px-3 py-2">
                                    @if ($img->isVideo())
                                        <span class="relative inline-block size-14 shrink-0 overflow-hidden rounded-lg ring-1 ring-ink-200/60">
                                            <video src="{{ $img->url() }}" class="size-full object-cover" muted playsinline></video>
                                            <span class="pointer-events-none absolute inset-0 flex items-center justify-center bg-ink-950/45" aria-hidden="true">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1" fill="#fff"/><rect x="14" y="5" width="4" height="14" rx="1" fill="#fff"/></svg>
                                            </span>
                                        </span>
                                    @else
                                        <img src="{{ $img->url() }}" alt="" class="size-14 rounded-lg object-cover ring-1 ring-ink-200/60">
                                    @endif

                                    <div class="min-w-0 flex-1 space-y-2">
                                        <p class="truncate font-mono text-xs text-ink-500">{{ \Illuminate\Support\Str::afterLast($img->path, '/') }}</p>
                                        <div class="flex flex-wrap items-center gap-4 text-sm">
                                            @if (! $img->isVideo())
                                                <label class="inline-flex items-center gap-2 text-ink-700">
                                                    <input
                                                        type="radio"
                                                        name="thumbnail_image_id"
                                                        value="{{ $img->id }}"
                                                        @checked(old('thumbnail_image_id', $product->images->firstWhere('is_thumbnail', true)?->id) == $img->id)
                                                    >
                                                    Catalog thumbnail
                                                </label>
                                            @endif
                                            <label class="inline-flex items-center gap-2 text-red-700">
                                                <input type="checkbox" name="delete_image_ids[]" value="{{ $img->id }}">
                                                Delete
                                            </label>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        @error('thumbnail_image_id')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <x-admin.product-media-upload input-id="product-media-upload-edit" label="Add images or video" />
            </fieldset>
        </x-admin.form-section>

        <x-admin.form-section title="Variants" description="Each row is a sellable SKU. Stock is per variant." variant="minimal" flush>
            <x-slot name="actions">
                <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-800 shadow-sm transition hover:bg-zinc-50" @click="addRow()">
                    Add variant
                </button>
            </x-slot>
            <div class="overflow-x-auto">
                <table class="data-table data-table--admin data-table--compact data-table--variants min-w-full border-0">
                    <thead>
                        <tr>
                            <th scope="col">Color</th>
                            <th scope="col">Hex</th>
                            <th scope="col">Size</th>
                            <th scope="col">SKU</th>
                            <th scope="col">Stock</th>
                            <th scope="col" class="w-12"><span class="sr-only">Remove</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, i) in rows" :key="row.id ?? ('n-' + i)">
                            <tr class="align-top">
                                <td>
                                    <template x-if="row.id">
                                        <input type="hidden" :name="'variants[' + i + '][id]'" :value="row.id">
                                    </template>
                                    <input type="text" :name="'variants[' + i + '][color]'" x-model="row.color" class="form-input py-2 text-sm" required>
                                </td>
                                <td>
                                    <input type="text" :name="'variants[' + i + '][color_hex]'" x-model="row.color_hex" class="form-input py-2 font-mono text-sm" placeholder="#000000">
                                </td>
                                <td>
                                    <input type="text" :name="'variants[' + i + '][size]'" x-model="row.size" class="form-input py-2 text-sm" required>
                                </td>
                                <td>
                                    <input type="text" :name="'variants[' + i + '][sku]'" x-model="row.sku" class="form-input py-2 font-mono text-sm">
                                </td>
                                <td>
                                    <input type="number" :name="'variants[' + i + '][stock_quantity]'" x-model="row.stock_quantity" min="0" class="form-input py-2 text-sm" required>
                                </td>
                                <td class="text-right">
                                    <button type="button" class="inline-flex size-9 items-center justify-center rounded-md text-zinc-500 transition hover:bg-red-50 hover:text-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-zinc-400/40" @click="removeRow(i)" x-show="rows.length > 1" title="Remove variant" aria-label="Remove variant row">
                                        <x-icons.trash class="h-4 w-4" />
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <p class="border-t border-zinc-200 bg-zinc-50/80 px-6 py-3 text-sm leading-relaxed text-zinc-600">
                Deleting a row removes that variant. Completed orders still show the variant details from when they were placed.
            </p>
        </x-admin.form-section>

        <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 pt-6 sm:flex-row sm:justify-end">
            <button type="submit" class="btn-primary px-8 py-2.5 font-medium">Save changes</button>
        </div>
    </form>
@endsection
