@extends('layouts.app')

@section('title', 'My orders')

@section('content')
    <x-page-header
        title="{{ auth()->user()->isAdmin() ? 'All orders' : 'My orders' }}"
        subtitle="{{ auth()->user()->isAdmin() ? 'Store-wide list. Customers only see their own orders.' : 'Track purchases and payment status. Bank transfers stay pending until an administrator confirms receipt.' }}"
    />

    <div class="table-shell mt-10">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td class="font-mono text-xs font-semibold text-ink-900">{{ $order->order_number }}</td>
                            <td class="text-ink-600">{{ $order->created_at->format('M j, Y') }}</td>
                            <td>
                                <span class="inline-flex rounded-full border border-ink-200/80 bg-ink-50 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-ink-800">{{ $order->status->label() }}</span>
                            </td>
                            <td class="text-ink-600">
                                {{ $order->payment_method->label() }}
                                <span class="mt-0.5 block text-xs text-ink-400">{{ $order->payment_status->label() }}</span>
                            </td>
                            <td class="font-semibold text-ink-900">{{ config('store.currency_symbol') }}{{ number_format((float) $order->total, 2) }}</td>
                            <td class="text-right">
                                <a href="{{ route('orders.show', $order) }}" class="link-brand text-sm">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="data-table-empty">No orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination-wrap">
        {{ $orders->links() }}
    </div>
@endsection
