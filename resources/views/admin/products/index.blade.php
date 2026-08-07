@extends('layouts.admin')

@section('title', 'Products')

@section('content')
    <x-page-header title="Products" subtitle="Manage catalog, variants, and stock. Customers always pick color and size at checkout.">
        <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Manage categories</a>
        <a href="{{ route('admin.products.create') }}" class="btn-primary">New product</a>
    </x-page-header>

    <div x-data="adminLiveSearch('products-results')">
        <form method="GET" action="{{ route('admin.products.index') }}" class="search-bar mt-8 flex flex-wrap items-end gap-4" x-ref="form">
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
            <button type="submit" class="btn-dark inline-flex shrink-0 items-center gap-2">
                <x-icons.spinner x-show="loading" x-cloak class="h-4 w-4 animate-spin" />
                <span>Filter</span>
            </button>
            @if (request()->hasAny(['q', 'category_id', 'inactive']))
                <a href="{{ route('admin.products.index') }}" class="btn-secondary shrink-0">Clear</a>
            @endif
        </form>

        <div id="products-results" class="transition-opacity" :class="{ 'opacity-50': loading }">
            @include('admin.products.partials.results')
        </div>
    </div>
@endsection
