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
        class="mt-10 space-y-10"
        x-data="{ rows: @js($variantRows), addRow() { this.rows.push({ id: null, color: '', size: '', sku: '', stock_quantity: '0', color_hex: '' }); }, removeRow(i) { this.rows.splice(i, 1); } }"
    >
        @csrf
        @method('PUT')

        <div class="grid gap-10 lg:grid-cols-2">
            <fieldset class="space-y-4 rounded-3xl border border-ink-200/60 bg-white/95 p-6 shadow-soft">
                <legend class="font-display text-sm font-bold uppercase tracking-wide text-ink-950">Details</legend>
                <div>
                    <label for="category_id" class="form-label">Category</label>
                    <select name="category_id" id="category_id" class="form-input" required>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" class="form-input" required>
                </div>
                <div>
                    <label for="slug" class="form-label">Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $product->slug) }}" class="form-input" required>
                </div>
                <div>
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="5" class="form-input">{{ old('description', $product->description) }}</textarea>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="price" class="form-label">Price</label>
                        <input type="number" name="price" id="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" class="form-input" required>
                    </div>
                    <div class="flex items-end pb-2">
                        <label class="flex items-center gap-2 text-sm font-medium text-ink-800">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active)) class="rounded border-ink-300">
                            Active in storefront
                        </label>
                    </div>
                </div>
            </fieldset>

            <fieldset class="space-y-4 rounded-3xl border border-ink-200/60 bg-white/95 p-6 shadow-soft">
                <legend class="font-display text-sm font-bold uppercase tracking-wide text-ink-950">Images</legend>
                @if ($product->images->isNotEmpty())
                    <p class="text-xs font-bold uppercase tracking-mega text-ink-500">Remove existing</p>
                    <ul class="space-y-2">
                        @foreach ($product->images as $img)
                            <li class="flex items-center gap-3">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($img->path) }}" alt="" class="size-14 rounded-lg object-cover ring-1 ring-ink-200/60">
                                <label class="flex items-center gap-2 text-sm text-ink-700">
                                    <input type="checkbox" name="delete_image_ids[]" value="{{ $img->id }}">
                                    Delete
                                </label>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <div>
                    <label class="form-label">Add images</label>
                    <input type="file" name="images[]" accept="image/*" multiple class="form-input">
                </div>
            </fieldset>
        </div>

        <fieldset class="rounded-3xl border border-ink-200/60 bg-white/95 p-6 shadow-soft">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <legend class="font-display text-sm font-bold uppercase tracking-wide text-ink-950">Variants</legend>
                <button type="button" class="btn-secondary text-sm" @click="addRow()">Add row</button>
            </div>
            <div class="mt-6 overflow-x-auto rounded-lg border border-zinc-200/80">
                <table class="data-table data-table--admin data-table--compact min-w-full">
                    <thead>
                        <tr>
                            <th>Color</th>
                            <th>Hex</th>
                            <th>Size</th>
                            <th>SKU</th>
                            <th>Stock</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, i) in rows" :key="row.id ?? ('n-' + i)">
                            <tr class="align-top">
                                <td>
                                    <template x-if="row.id">
                                        <input type="hidden" :name="'variants[' + i + '][id]'" :value="row.id">
                                    </template>
                                    <input type="text" :name="'variants[' + i + '][color]'" x-model="row.color" class="form-input py-1.5 text-sm" required>
                                </td>
                                <td>
                                    <input type="text" :name="'variants[' + i + '][color_hex]'" x-model="row.color_hex" class="form-input py-1.5 text-sm" placeholder="#000000">
                                </td>
                                <td>
                                    <input type="text" :name="'variants[' + i + '][size]'" x-model="row.size" class="form-input py-1.5 text-sm" required>
                                </td>
                                <td>
                                    <input type="text" :name="'variants[' + i + '][sku]'" x-model="row.sku" class="form-input py-1.5 text-sm">
                                </td>
                                <td>
                                    <input type="number" :name="'variants[' + i + '][stock_quantity]'" x-model="row.stock_quantity" min="0" class="form-input py-1.5 text-sm" required>
                                </td>
                                <td class="text-right">
                                    <button type="button" class="text-xs font-semibold text-red-600 hover:text-red-800" @click="removeRow(i)" x-show="rows.length > 1">Remove</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <p class="mt-4 text-xs text-ink-500">Removing a row deletes that variant. Past orders keep a snapshot of names and prices.</p>
        </fieldset>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="btn-primary">Save changes</button>
        </div>
    </form>
@endsection
