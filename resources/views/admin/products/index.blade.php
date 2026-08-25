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

        <div
            x-data="{
                hasSelection: false,
                selectedCount: 0,
                refresh() {
                    const boxes = Array.from(this.$el.querySelectorAll('.js-select-product'));
                    const checked = boxes.filter((box) => box.checked);
                    this.selectedCount = checked.length;
                    this.hasSelection = checked.length > 0;
                    const selectAll = this.$el.querySelector('.js-select-all');
                    if (selectAll) {
                        selectAll.checked = boxes.length > 0 && checked.length === boxes.length;
                        selectAll.indeterminate = checked.length > 0 && checked.length < boxes.length;
                    }
                },
                toggleAll() {
                    // Decides the new state itself (any unchecked → check all, else
                    // uncheck all) instead of trusting the checkbox's own native click
                    // toggle — some browsers don't reliably flip `checked` on the first
                    // click when the box was indeterminate, which read as a dead click.
                    const boxes = Array.from(this.$el.querySelectorAll('.js-select-product'));
                    const shouldCheck = boxes.some((box) => ! box.checked);
                    boxes.forEach((box) => { box.checked = shouldCheck; });
                    this.refresh();
                },
                init() {
                    // Browsers restore checkbox checked-state on reload/back-navigation
                    // independent of the server-rendered HTML — without this, a stale
                    // select-all checked state could survive a reload while the rows
                    // underneath reset to unchecked. Recomputing on load corrects it
                    // against the real row states instead of trusting the restored one.
                    this.refresh();
                    new MutationObserver(() => this.refresh()).observe(
                        document.getElementById('products-results'),
                        { childList: true, subtree: true }
                    );
                },
            }"
            @change="refresh()"
        >
            {{--
                This form does NOT wrap #products-results — nesting a <form> inside
                another <form> is invalid HTML and browsers silently drop the inner
                one, which was mis-submitting every per-row Archive button to this
                bulk route instead (see the `form` attribute on the checkboxes below,
                which associates them with this form despite living outside it).
            --}}
            <form
                id="products-bulk-delete-form"
                method="POST"
                action="{{ route('admin.products.bulkDestroy') }}"
                data-confirm-label="Archive"
                :data-confirm="`Archive ${selectedCount} selected product(s)? You can restore them later from Archived.`"
            >
                @csrf
                @method('DELETE')
                <div class="mt-4" x-show="hasSelection" x-cloak>
                    <button type="submit" class="btn-danger">Archive Selected (<span x-text="selectedCount"></span>)</button>
                </div>
            </form>

            <div id="products-results" class="transition-opacity" :class="{ 'opacity-50': loading }">
                @include('admin.products.partials.results')
            </div>
        </div>
    </div>
@endsection
