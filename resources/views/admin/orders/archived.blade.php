@extends('layouts.admin')

@section('title', 'Archived orders')

@section('content')
    <x-page-header title="Archived orders" subtitle="Orders you've archived. Restore them or delete them permanently.">
        <a href="{{ route('admin.orders.index') }}" class="btn-secondary">Back to orders</a>
    </x-page-header>

    <div x-data="adminLiveSearch('orders-archived-results')">
        <form method="GET" action="{{ route('admin.orders.archived') }}" class="search-bar mt-8 flex flex-wrap items-end gap-4" x-ref="form">
            <div class="min-w-0 flex-1">
                <label for="q" class="form-label">Search</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="Order number, name, or email…" class="form-input">
            </div>
            <button type="submit" class="btn-dark inline-flex shrink-0 items-center gap-2">
                <x-icons.spinner x-show="loading" x-cloak class="h-4 w-4 animate-spin" />
                <span>Filter</span>
            </button>
            @if (request()->filled('q'))
                <a href="{{ route('admin.orders.archived') }}" class="btn-secondary shrink-0">Clear</a>
            @endif
        </form>

        <div id="orders-archived-results" class="transition-opacity" :class="{ 'opacity-50': loading }">
            @include('admin.orders.partials.archived-results')
        </div>
    </div>
@endsection
