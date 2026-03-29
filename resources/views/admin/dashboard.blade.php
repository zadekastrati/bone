@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-10 border-b border-zinc-200/80 pb-8">
        <h1 class="font-display text-2xl font-semibold uppercase tracking-wide text-ink-900 sm:text-3xl">Overview</h1>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-ink-600">Sales, catalog, and quick access to recent orders.</p>
    </div>

    <div class="grid w-full grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="admin-panel min-h-0 p-5 sm:p-6">
            <p class="text-xs font-bold uppercase tracking-mega text-ink-500">Pending orders</p>
            <p class="mt-2 font-display text-3xl font-bold text-ink-900">{{ $stats['orders_pending'] }}</p>
            <a href="{{ route('admin.orders.index') }}" class="mt-3 inline-block text-xs font-semibold text-accent-700 hover:text-accent-600">View orders →</a>
        </div>
        <div class="admin-panel min-h-0 p-5 sm:p-6">
            <p class="text-xs font-bold uppercase tracking-mega text-ink-500">Orders today</p>
            <p class="mt-2 font-display text-3xl font-bold text-ink-900">{{ $stats['orders_today'] }}</p>
        </div>
        <div class="admin-panel min-h-0 p-5 sm:p-6">
            <p class="text-xs font-bold uppercase tracking-mega text-ink-500">Revenue today</p>
            <p class="mt-2 font-display text-3xl font-bold text-ink-900">{{ config('store.currency_symbol') }}{{ $stats['revenue_today'] }}</p>
        </div>
        <div class="admin-panel min-h-0 p-5 sm:p-6">
            <p class="text-xs font-bold uppercase tracking-mega text-ink-500">Catalog</p>
            <p class="mt-2 text-sm text-ink-700"><span class="font-semibold text-ink-900">{{ $stats['products'] }}</span> products · <span class="font-semibold text-ink-900">{{ $stats['categories'] }}</span> categories</p>
            <p class="mt-2 text-sm text-ink-600"><span class="font-semibold text-ink-900">{{ $stats['users'] }}</span> accounts</p>
        </div>
    </div>

    <div class="admin-panel mt-8 w-full min-w-0">
        <div class="border-b border-zinc-100/90 px-5 py-4 sm:px-6">
            <h2 class="font-display text-sm font-bold uppercase tracking-wide text-ink-900">Recent orders</h2>
            <p class="mt-1 text-xs text-ink-500">Latest activity across the store</p>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table data-table--admin">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentOrders as $order)
                        <tr>
                            <td class="font-mono text-xs text-ink-900">{{ $order->order_number }}</td>
                            <td>
                                <span class="font-medium text-ink-800">{{ $order->user->name ?? '—' }}</span>
                                <span class="mt-0.5 block text-xs text-ink-500">{{ $order->user->email }}</span>
                            </td>
                            <td>
                                <span class="inline-flex rounded-full border border-zinc-200/80 bg-zinc-50/80 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-ink-800">{{ $order->status->label() }}</span>
                            </td>
                            <td class="font-semibold text-ink-900">{{ config('store.currency_symbol') }}{{ number_format((float) $order->total, 2) }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="admin-action-link">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="data-table-empty text-ink-500">No orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8 flex w-full min-w-0 flex-wrap items-center gap-3">
        <a href="{{ route('admin.products.create') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-accent-600 px-5 py-2.5 text-xs font-bold uppercase tracking-mega text-white shadow-sm transition hover:bg-accent-700">New product</a>
        <a href="{{ route('admin.categories.index') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl border border-zinc-200/80 bg-white px-5 py-2.5 text-xs font-bold uppercase tracking-mega text-ink-800 shadow-sm transition hover:border-zinc-300">Manage categories</a>
    </div>
@endsection
