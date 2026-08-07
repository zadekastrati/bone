@extends('layouts.admin')

@section('title', 'Store orders')

@section('content')
    <x-page-header title="Orders" subtitle="Update fulfillment, tracking, and payment status after bank transfers clear." />

    <div x-data="adminLiveSearch('orders-results')">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="search-bar mt-8 flex flex-wrap items-end gap-4" x-ref="form">
            <div class="min-w-0 flex-1">
                <label for="q" class="form-label">Search</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}" placeholder="Order # or customer…" class="form-input">
            </div>
            <div class="w-full sm:w-48">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-input">
                    <option value="">All</option>
                    @foreach (\App\Enums\OrderStatus::cases() as $st)
                        <option value="{{ $st->value }}" @selected(request('status') === $st->value)>{{ $st->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-dark inline-flex shrink-0 items-center gap-2">
                <x-icons.spinner x-show="loading" x-cloak class="h-4 w-4 animate-spin" />
                <span>Filter</span>
            </button>
            @if (request()->hasAny(['q', 'status']))
                <a href="{{ route('admin.orders.index') }}" class="btn-secondary shrink-0">Clear</a>
            @endif
        </form>

        <div id="orders-results" class="transition-opacity" :class="{ 'opacity-50': loading }">
            @include('admin.orders.partials.results')
        </div>
    </div>
@endsection
