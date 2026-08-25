@extends('layouts.admin')

@section('title', 'Messages')

@section('content')
    <x-page-header title="Messages" subtitle="Contact form submissions from the storefront.">
        @if ($messages->total() > 0)
            <form method="POST" action="{{ route('admin.messages.destroyAll') }}" data-confirm="Delete all {{ $messages->total() }} message(s){{ request()->filled('q') ? ' matching this search' : '' }}? This cannot be undone." data-confirm-label="Delete all">
                @csrf
                @method('DELETE')
                @if (request()->filled('q'))
                    <input type="hidden" name="q" value="{{ request('q') }}">
                @endif
                <button type="submit" class="btn-danger">Delete all</button>
            </form>
        @endif
    </x-page-header>

    <div x-data="adminLiveSearch('messages-results')">
        <form method="GET" action="{{ route('admin.messages.index') }}" class="search-bar mt-8 flex flex-wrap items-end gap-4" x-ref="form">
            <div class="min-w-0 flex-1">
                <label for="q" class="form-label">Search</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="Name, email, or message..." class="form-input">
            </div>
            <button type="submit" class="btn-dark inline-flex shrink-0 items-center gap-2">
                <x-icons.spinner x-show="loading" x-cloak class="h-4 w-4 animate-spin" />
                <span>Filter</span>
            </button>
            @if (request()->filled('q'))
                <a href="{{ route('admin.messages.index') }}" class="btn-secondary shrink-0">Clear</a>
            @endif
        </form>

        <div
            x-data="{
                hasSelection: false,
                selectedCount: 0,
                refresh() {
                    const boxes = Array.from(this.$el.querySelectorAll('.js-select-message'));
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
                    const boxes = Array.from(this.$el.querySelectorAll('.js-select-message'));
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
                        document.getElementById('messages-results'),
                        { childList: true, subtree: true }
                    );
                },
            }"
            @change="refresh()"
        >
            {{--
                This form does NOT wrap #messages-results — nesting a <form> inside
                another <form> is invalid HTML and browsers silently drop the inner
                one, which would mis-submit every per-row Delete button to this bulk
                route instead (see the `form` attribute on the checkboxes below,
                which associates them with this form despite living outside it).
            --}}
            <form
                id="messages-bulk-delete-form"
                method="POST"
                action="{{ route('admin.messages.bulkDestroy') }}"
                data-confirm-label="Delete"
                :data-confirm="`Delete ${selectedCount} selected message(s)? This cannot be undone.`"
            >
                @csrf
                @method('DELETE')
                <div class="mt-4" x-show="hasSelection" x-cloak>
                    <button type="submit" class="btn-danger">Delete Selected (<span x-text="selectedCount"></span>)</button>
                </div>
            </form>

            <div id="messages-results" class="transition-opacity" :class="{ 'opacity-50': loading }">
                @include('admin.messages.partials.results')
            </div>
        </div>
    </div>
@endsection
