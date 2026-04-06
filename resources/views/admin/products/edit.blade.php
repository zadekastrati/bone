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
        action="{{ route('admin.products.update', $product->id) }}"
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
                    <select name="category_id" id="category_id" class="form-select" required>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" class="form-input" required autocomplete="off">
                </div>
                <div class="sm:col-span-2">
                    <label for="slug" class="form-label">URL slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $product->slug) }}" class="form-input font-mono text-sm" required autocomplete="off">
                </div>
                <div class="sm:col-span-2">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="6" class="form-input">{{ old('description', $product->description) }}</textarea>
                </div>
                <div>
                    <label for="price" class="form-label">Price</label>
                    <div class="relative mt-1.5">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm text-zinc-500">$</span>
                        <input type="number" name="price" id="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" class="form-input pl-8" required>
                    </div>
                </div>
                <div class="flex items-end">
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-800">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active)) class="size-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-400/30">
                        <span class="font-medium">Published</span>
                        <span class="text-zinc-500">— visible in the storefront</span>
                    </label>
                </div>
            </div>
        </x-admin.form-section>

        <x-admin.form-section title="Media" description="Remove existing images with the control beside each file. New uploads append on save." variant="minimal">
            @if ($product->images->isNotEmpty())
                <div class="overflow-hidden rounded-lg border border-zinc-200">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-medium text-zinc-600">Preview</th>
                                <th class="px-4 py-2.5 text-left text-xs font-medium text-zinc-600">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 bg-white">
                            @foreach ($product->images as $img)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-4">
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($img->path) }}" alt="" class="size-12 shrink-0 rounded-md border border-zinc-200 object-cover">
                                            <span class="truncate font-mono text-xs text-zinc-500">{{ \Illuminate\Support\Str::afterLast($img->path, '/') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <label class="inline-flex cursor-pointer items-center gap-2 text-red-700">
                                            <input type="checkbox" name="delete_image_ids[]" value="{{ $img->id }}" class="size-4 rounded border-zinc-300 text-red-600 focus:ring-red-500/30">
                                            <x-icons.trash class="h-4 w-4 shrink-0" />
                                            <span>Remove</span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            <div>
                <label class="form-label">Upload new images</label>
                <input type="file" name="images[]" accept="image/*" multiple class="form-input file:rounded-md file:bg-zinc-100 file:text-zinc-800">
                <p class="mt-2 text-xs text-zinc-500">JPEG, PNG, or WebP.</p>
            </div>
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
