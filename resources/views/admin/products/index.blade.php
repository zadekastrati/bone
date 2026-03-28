@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <x-page-header title="Products" subtitle="Manage catalog, variants, and stock. Customers always pick color and size at checkout.">
        <a href="{{ route('admin.products.create') }}" class="btn-primary">New product</a>
    </x-page-header>

    <form method="GET" action="{{ route('admin.products.index') }}" class="search-bar mt-8 flex flex-wrap items-end gap-4">
        <div class="min-w-0 flex-1">
            <label for="q" class="form-label">Search</label>
            <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="Name or slug…" class="form-input">
        </div>
        <div class="w-full sm:w-48">
            <label for="category_id" class="form-label">Category</label>
            <select name="category_id" id="category_id" class="form-input">
                <option value="">All</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2 pb-2">
            <input type="checkbox" name="inactive" id="inactive" value="1" @checked(request()->boolean('inactive'))>
            <label for="inactive" class="text-sm text-ink-700">Inactive only</label>
        </div>
        <button type="submit" class="btn-dark shrink-0">Filter</button>
        @if (request()->hasAny(['q', 'category_id', 'inactive']))
            <a href="{{ route('admin.products.index') }}" class="btn-secondary shrink-0">Clear</a>
        @endif
    </form>

    <div class="mt-10 overflow-hidden rounded-3xl border border-ink-200/60 bg-white/95 shadow-soft ring-1 ring-ink-950/[0.03]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ink-200 text-left text-sm">
                <thead class="bg-ink-50/90">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Product</th>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Category</th>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Price</th>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Variants</th>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Active</th>
                        <th class="px-5 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse ($products as $product)
                        <tr>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-ink-950">{{ $product->name }}</span>
                                <span class="mt-0.5 block font-mono text-xs text-ink-500">{{ $product->slug }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-ink-600">{{ $product->category->name }}</td>
                            <td class="px-5 py-3.5 font-semibold text-ink-900">{{ config('store.currency_symbol') }}{{ number_format((float) $product->price, 2) }}</td>
                            <td class="px-5 py-3.5 text-ink-600">{{ $product->variants_count }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $product->is_active ? 'bg-emerald-100 text-emerald-900' : 'bg-ink-100 text-ink-700' }}">
                                    {{ $product->is_active ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="link-brand text-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product->id) }}" class="ml-4 inline" onsubmit="return confirm('Archive this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800">Archive</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-ink-600">No products match.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-10 flex justify-center">
        {{ $products->links() }}
    </div>
@endsection
