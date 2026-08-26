@extends('layouts.admin')

@section('title', 'New category')

@section('content')
    <x-page-header title="New category" subtitle="Slug is used in URLs. Leave blank to generate from the name.">
        <a href="{{ route('admin.products.index') }}" class="btn-secondary">Back to products</a>
        <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Categories</a>
    </x-page-header>

    <div class="mx-auto mt-8 max-w-2xl">
        <form method="POST" action="{{ route('admin.categories.store') }}" class="admin-pro-form" enctype="multipart/form-data">
            @csrf

            <x-admin.form-section title="Category" description="Name and slug appear in URLs and navigation.">
                <div>
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-input @error('name') form-input-error @enderror" required>
                    @error('name')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="slug" class="form-label">Slug <span class="font-normal text-ink-400">(optional)</span></label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}" placeholder="auto from name" class="form-input @error('slug') form-input-error @enderror">
                    @error('slug')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="4" class="form-input @error('description') form-input-error @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="image" class="form-label">Category image</label>
                    <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp" class="form-input @error('image') form-input-error @enderror">
                    <p class="mt-2 text-xs text-ink-500">Shown as the background photo for this category on the homepage. Upload a file, or pick one already on R2.</p>
                    @error('image')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <div class="mt-3">
                        <x-admin.category-image-picker />
                    </div>
                    @error('attach_image')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="sort_order" class="form-label">Sort order</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="form-input max-w-xs @error('sort_order') form-input-error @enderror">
                    <p class="mt-2 text-xs text-ink-500">Lower numbers appear first in lists.</p>
                    @error('sort_order')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </x-admin.form-section>

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Create</button>
            </div>
        </form>
    </div>

    <x-admin.media-library-modal
        :fetch-url="route('admin.media-library.index')"
        :thumbnail-url="route('admin.media-library.thumbnail')"
    />
@endsection
