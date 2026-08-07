@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <x-page-header title="Overview" subtitle="Sales, catalog, and quick access to recent orders.">
        <a href="{{ route('admin.products.create') }}" class="btn-primary px-5 py-2.5 text-xs">New product</a>
        <a href="{{ route('admin.categories.index') }}" class="btn-secondary px-5 py-2.5 text-xs">Manage categories</a>
    </x-page-header>

    <div class="grid w-full grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card label="Pending orders" icon="clock" :href="route('admin.orders.index')" link-text="View orders →">
            {{ $stats['orders_pending'] }}
        </x-admin.stat-card>
        <x-admin.stat-card label="Orders today" icon="chart-bar">
            {{ $stats['orders_today'] }}
        </x-admin.stat-card>
        <x-admin.stat-card label="Revenue today" icon="banknotes">
            {{ config('store.currency_symbol') }}{{ $stats['revenue_today'] }}
        </x-admin.stat-card>
        <x-admin.stat-card label="Messages" icon="chat-bubble" :href="route('admin.messages.index')" link-text="View messages →">
            {{ $stats['contacts'] }}
        </x-admin.stat-card>
    </div>

    <div class="admin-panel mt-8 w-full min-w-0 p-5 sm:p-6">
        <p class="text-xs font-bold uppercase tracking-mega text-ink-500">Catalog snapshot</p>
        <div class="mt-3 flex flex-wrap gap-x-8 gap-y-2 text-sm text-ink-700">
            <p><span class="font-semibold text-ink-900">{{ $stats['products'] }}</span> products</p>
            <p><span class="font-semibold text-ink-900">{{ $stats['categories'] }}</span> categories</p>
            <p><span class="font-semibold text-ink-900">{{ $stats['users'] }}</span> accounts</p>
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
                                <x-admin.badge :tone="$order->status->tone()">{{ $order->status->label() }}</x-admin.badge>
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

    <div class="admin-panel mt-8 w-full min-w-0">
        <div class="border-b border-zinc-100/90 px-5 py-4 sm:px-6">
            <h2 class="font-display text-sm font-bold uppercase tracking-wide text-ink-900">Recent contact messages</h2>
            <p class="mt-1 text-xs text-ink-500">Latest messages sent from the contact form</p>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table data-table--admin">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Received</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentContactMessages as $message)
                        <tr>
                            <td class="font-medium text-ink-800">{{ $message->name }}</td>
                            <td><a href="mailto:{{ $message->email }}" class="text-ink-700 hover:text-accent-700">{{ $message->email }}</a></td>
                            <td class="max-w-[30rem] text-sm text-ink-600">{{ \Illuminate\Support\Str::limit($message->message, 140) }}</td>
                            <td class="text-xs text-ink-500">{{ $message->created_at->format('M d, Y H:i') }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.messages.show', $message->id) }}" class="admin-action-link">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="data-table-empty text-ink-500">No contact messages yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 pb-4 pt-3 sm:px-6">
            <a href="{{ route('admin.messages.index') }}" class="admin-action-link">View all messages</a>
        </div>
    </div>
@endsection
