@extends('layouts.admin')

@section('title', 'Products')

@section('content')
    <x-page-header title="Products" subtitle="Manage catalog, variants, and stock. Customers always pick color and size at checkout.">
        <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Manage categories</a>
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

    <div class="table-shell--admin mt-10">
        <div class="overflow-x-auto">
            <table class="data-table data-table--admin">
                <thead>
                    <tr>
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
                        <tr>
                            <td>
                                <span class="font-medium text-ink-900">{{ $product->name }}</span>
                                <span class="mt-0.5 block font-mono text-xs text-ink-500">{{ $product->slug }}</span>
                            </td>
                            <td class="text-ink-600">{{ $product->category->name }}</td>
                            <td class="font-semibold text-ink-900">{{ config('store.currency_symbol') }}{{ number_format((float) $product->price, 2) }}</td>
                            <td class="text-ink-600">{{ $product->variants_count }}</td>
                            <td>
                                <span class="inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide {{ $product->is_active ? 'border-emerald-200/80 bg-emerald-50 text-emerald-900' : 'border-zinc-200/80 bg-zinc-100/80 text-ink-700' }}">
                                    {{ $product->is_active ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="inline-flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="inline-flex size-9 items-center justify-center rounded-lg text-accent-700 transition hover:bg-accent-50 hover:text-accent-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-accent-500/30" title="Edit" aria-label="Edit product">
                                        <x-icons.pencil-square class="h-5 w-5" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product->id) }}" class="inline" onsubmit="return confirm('Archive this product?');">
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
                            <td colspan="6" class="data-table-empty text-ink-500">No products match.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination-wrap pagination-wrap--admin">
        {{ $products->links() }}
    </div>
@endsection
