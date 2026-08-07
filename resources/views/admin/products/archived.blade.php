@extends('layouts.admin')

@section('title', 'Archived products')

@section('content')
    <x-page-header title="Archived products" subtitle="Products you've archived. Restore them or delete them permanently.">
        <a href="{{ route('admin.products.index') }}" class="btn-secondary">Back to products</a>
    </x-page-header>

    <div x-data="adminLiveSearch('products-archived-results')">
        <form method="GET" action="{{ route('admin.products.archived') }}" class="search-bar mt-8 flex flex-wrap items-end gap-4" x-ref="form">
            <div class="min-w-0 flex-1">
                <label for="q" class="form-label">Search</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="Name or slug…" class="form-input">
            </div>
            <button type="submit" class="btn-dark inline-flex shrink-0 items-center gap-2">
                <x-icons.spinner x-show="loading" x-cloak class="h-4 w-4 animate-spin" />
                <span>Filter</span>
            </button>
            @if (request()->filled('q'))
                <a href="{{ route('admin.products.archived') }}" class="btn-secondary shrink-0">Clear</a>
            @endif
        </form>

        <form
            method="POST"
            action="{{ route('admin.products.bulkForceDelete') }}"
            data-confirm-label="Delete permanently"
            x-data="{
                hasSelection: false,
                selectedCount: 0,
                refresh() {
                    const boxes = this.$el.querySelectorAll('.js-select-product:checked');
                    this.selectedCount = boxes.length;
                    this.hasSelection = boxes.length > 0;
                },
                init() {
                    new MutationObserver(() => this.refresh()).observe(
                        document.getElementById('products-archived-results'),
                        { childList: true, subtree: true }
                    );
                },
            }"
            @change="refresh()"
            :data-confirm="`Permanently delete ${selectedCount} selected product(s)? This cannot be undone — their images and videos will be removed too.`"
        >
            @csrf
            @method('DELETE')
            <div class="mt-4" x-show="hasSelection" x-cloak>
                <button type="submit" class="btn-danger">Delete Selected Permanently (<span x-text="selectedCount"></span>)</button>
            </div>

            <div id="products-archived-results" class="transition-opacity" :class="{ 'opacity-50': loading }">
                @include('admin.products.partials.archived-results')
            </div>
        </form>
    </div>
@endsection
