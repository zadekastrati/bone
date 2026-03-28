@extends('layouts.app')

@section('title', 'New category')

@section('content')
    <x-page-header title="New category" subtitle="Slug is used in URLs. Leave blank to generate from the name.">
        <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Back</a>
    </x-page-header>

    <form method="POST" action="{{ route('admin.categories.store') }}" class="mt-10 max-w-xl space-y-6">
        @csrf
        <div>
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-input" required>
        </div>
        <div>
            <label for="slug" class="form-label">Slug <span class="font-normal text-ink-400">(optional)</span></label>
            <input type="text" name="slug" id="slug" value="{{ old('slug') }}" class="form-input" placeholder="auto from name">
        </div>
        <div>
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" rows="4" class="form-input">{{ old('description') }}</textarea>
        </div>
        <div>
            <label for="sort_order" class="form-label">Sort order</label>
            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="form-input">
        </div>
        <button type="submit" class="btn-primary">Create</button>
    </form>
@endsection
