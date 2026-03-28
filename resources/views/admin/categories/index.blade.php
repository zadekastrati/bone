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
                            <td class="font-medium text-slate-900">{{ $category->name }}</td>
                            <td class="font-mono text-xs text-slate-600">{{ $category->slug }}</td>
                            <td class="text-slate-600">{{ $category->products_count }}</td>
                            <td class="text-slate-600">{{ $category->sort_order }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.categories.edit', $category->id) }}" class="link-brand text-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category->id) }}" class="ml-4 inline" onsubmit="return confirm('Delete this category?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="data-table-empty text-slate-500">No categories yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
