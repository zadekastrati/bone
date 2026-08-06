@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
    <x-page-header title="Categories" subtitle="Organize products. Each category has its own URL in the shop.">
        <a href="{{ route('admin.products.index') }}" class="btn-secondary">Back to products</a>
        <a href="{{ route('admin.categories.create') }}" class="btn-primary">New category</a>
    </x-page-header>

    <div class="table-shell--admin mt-10">
        <div class="overflow-x-auto">
            <table class="data-table data-table--admin">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Products</th>
                        <th>Sort</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="font-medium text-ink-900">{{ $category->name }}</td>
                            <td class="font-mono text-xs text-ink-600">{{ $category->slug }}</td>
                            <td class="text-ink-600">{{ $category->products_count }}</td>
                            <td class="text-ink-600">{{ $category->sort_order }}</td>
                            <td class="text-right">
                                <div class="inline-flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="inline-flex size-9 items-center justify-center rounded-lg text-accent-700 transition hover:bg-accent-50 hover:text-accent-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-accent-500/30" title="Edit" aria-label="Edit category">
                                        <x-icons.pencil-square class="h-5 w-5" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category->id) }}" class="inline" onsubmit="return confirm('Delete this category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex size-9 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-50 hover:text-red-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500/30" title="Delete" aria-label="Delete category">
                                            <x-icons.trash class="h-5 w-5" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="data-table-empty text-ink-500">No categories yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
