@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <x-page-header title="Categories" subtitle="Organize products. Each category has its own URL in the shop.">
        <a href="{{ route('admin.categories.create') }}" class="btn-primary">New category</a>
    </x-page-header>

    <div class="mt-10 overflow-hidden rounded-3xl border border-ink-200/60 bg-white/95 shadow-soft ring-1 ring-ink-950/[0.03]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ink-200 text-left text-sm">
                <thead class="bg-ink-50/90">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Name</th>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Slug</th>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Products</th>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Sort</th>
                        <th class="px-5 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse ($categories as $category)
                        <tr>
                            <td class="px-5 py-3.5 font-medium text-ink-950">{{ $category->name }}</td>
                            <td class="px-5 py-3.5 font-mono text-xs text-ink-600">{{ $category->slug }}</td>
                            <td class="px-5 py-3.5 text-ink-600">{{ $category->products_count }}</td>
                            <td class="px-5 py-3.5 text-ink-600">{{ $category->sort_order }}</td>
                            <td class="px-5 py-3.5 text-right">
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
                            <td colspan="5" class="px-5 py-12 text-center text-ink-600">No categories yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
