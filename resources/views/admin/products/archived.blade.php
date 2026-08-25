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
                    this.syncRestoreIds();
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
                    // independent of the server-rendered HTML — refresh() (which also
                    // calls syncRestoreIds) recomputes everything against the real row
                    // states instead of trusting a possibly-stale restored one.
                    this.refresh();
                    new MutationObserver(() => this.refresh()).observe(
                        document.getElementById('products-archived-results'),
                        { childList: true, subtree: true }
                    );
                },
                /*
                 * The restore form (unlike the delete form) can't use the
                 * checkboxes' native form attribute — an input can only be
                 * associated with one form, and that's already claimed by
                 * the delete form below. So the checked ids are mirrored into
                 * this form as hidden inputs on every checkbox change, kept
                 * continuously in sync rather than collected right at submit
                 * time — the previous submit-time-only version raced against
                 * the data-confirm modal's own submit interception and could
                 * end up submitting before the ids were copied over.
                 */
                syncRestoreIds() {
                    const form = document.getElementById('products-archived-bulk-restore-form');
                    if (! form) {
                        return;
                    }
                    form.querySelectorAll('.js-collected-id').forEach((el) => el.remove());
                    this.$el.querySelectorAll('.js-select-product:checked').forEach((checkbox) => {
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'ids[]';
                        hidden.className = 'js-collected-id';
                        hidden.value = checkbox.value;
                        form.appendChild(hidden);
                    });
                },
            }"
            @change="refresh()"
        >
            {{--
                These forms do NOT wrap #products-archived-results — nesting a
                <form> inside another <form> is invalid HTML and browsers silently
                drop the inner one, which was mis-submitting every per-row Restore/
                Delete-permanently button to this bulk route instead (see the
                `form` attribute on the checkboxes below, which associates them
                with the delete form despite living outside it).
            --}}
            <div class="mt-4 flex flex-wrap gap-3" x-show="hasSelection" x-cloak>
                <form
                    id="products-archived-bulk-restore-form"
                    method="POST"
                    action="{{ route('admin.products.bulkRestore') }}"
                    data-confirm-label="Restore"
                    :data-confirm="`Restore ${selectedCount} selected product(s)?`"
                >
                    @csrf
                    <button type="submit" class="btn-dark">Restore Selected (<span x-text="selectedCount"></span>)</button>
                </form>

                <form
                    id="products-archived-bulk-delete-form"
                    method="POST"
                    action="{{ route('admin.products.bulkForceDelete') }}"
                    data-confirm-label="Delete permanently"
                    :data-confirm="`Permanently delete ${selectedCount} selected product(s)? This cannot be undone — their images and videos will be removed too.`"
                >
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Delete Selected Permanently (<span x-text="selectedCount"></span>)</button>
                </form>
            </div>

            <div id="products-archived-results" class="transition-opacity" :class="{ 'opacity-50': loading }">
                @include('admin.products.partials.archived-results')
            </div>
        </div>
    </div>
@endsection
