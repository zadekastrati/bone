@extends('layouts.admin')

@section('title', 'Store orders')

@section('content')
    <x-page-header title="Orders" subtitle="Update fulfillment, tracking, and payment status after bank transfers clear.">

    </x-page-header>

    <form method="GET" action="{{ route('admin.orders.index') }}" class="search-bar mt-8 flex flex-wrap items-end gap-4">
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
        <button type="submit" class="btn-dark shrink-0">Filter</button>
        @if (request()->hasAny(['q', 'status']))
            <a href="{{ route('admin.orders.index') }}" class="btn-secondary shrink-0">Clear</a>
        @endif
    </form>

    <div class="table-shell--admin mt-10">
        <div class="overflow-x-auto">
            <table class="data-table data-table--admin">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td class="font-mono text-xs font-semibold text-slate-900">{{ $order->order_number }}</td>
                            <td>
                                <span class="font-medium text-slate-900">{{ $order->user->name }}</span>
                                <span class="mt-0.5 block text-xs text-slate-500">{{ $order->user->email }}</span>
                            </td>
                            <td class="text-slate-600">{{ $order->created_at->format('M j, Y H:i') }}</td>
                            <td>
                                <span class="inline-flex rounded-full border border-slate-200/90 bg-slate-50 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-slate-800">{{ $order->status->label() }}</span>
                            </td>
                            <td class="font-semibold text-slate-900">{{ config('store.currency_symbol') }}{{ number_format((float) $order->total, 2) }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="admin-action-link">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="data-table-empty text-slate-500">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination-wrap pagination-wrap--admin">
        {{ $orders->links() }}
    </div>
@endsection
