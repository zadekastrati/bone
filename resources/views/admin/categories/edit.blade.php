@extends('layouts.app')

@section('title', 'Edit category')

@section('content')
    <x-page-header title="Edit category" :subtitle="$category->name">
        <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Back</a>
    </x-page-header>

    <form method="POST" action="{{ route('admin.categories.update', $category->id) }}" class="mt-10 max-w-xl space-y-6">
        @csrf
        @method('PUT')
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
            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="0" class="form-input">
        </div>
        <button type="submit" class="btn-primary">Save</button>
    </form>
@endsection
