@extends('layouts.admin')

@section('title', 'Edit category')

@section('content')
    <x-page-header title="Edit category" :subtitle="$category->name">
        <a href="{{ route('admin.products.index') }}" class="btn-secondary">Back to products</a>
        <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Categories</a>
    </x-page-header>

    <div class="mx-auto mt-8 max-w-2xl">
        <form method="POST" action="{{ route('admin.categories.update', $category->id) }}">
            @csrf
            @method('PUT')

            <x-admin.form-section title="Category" description="Name and slug appear in URLs and navigation.">
                <div>
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" class="form-input" required>
                </div>
                <div>
                    <label for="slug" class="form-label">Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $category->slug) }}" class="form-input" required>
                </div>
                <div>
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="4" class="form-input">{{ old('description', $category->description) }}</textarea>
                </div>
                <div>
                    <label for="sort_order" class="form-label">Sort order</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="0" class="form-input max-w-xs">
                    <p class="mt-2 text-xs text-ink-500">Lower numbers appear first in lists.</p>
                </div>
            </x-admin.form-section>

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Save changes</button>
            </div>
        </form>
    </div>
@endsection
