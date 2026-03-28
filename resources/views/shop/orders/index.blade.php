@extends('layouts.app')

@section('title', 'My orders')

@section('content')
    <x-page-header
        title="{{ auth()->user()->isAdmin() ? 'All orders' : 'My orders' }}"
        subtitle="{{ auth()->user()->isAdmin() ? 'Store-wide list. Customers only see their own orders.' : 'Track purchases and payment status. Bank transfers stay pending until an administrator confirms receipt.' }}"
    />

    <div class="mt-10 overflow-hidden rounded-3xl border border-ink-200/60 bg-white/95 shadow-soft ring-1 ring-ink-950/[0.03]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ink-200 text-left text-sm">
                <thead class="bg-ink-50/90">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Order</th>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Date</th>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Payment</th>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Total</th>
                        <th class="px-5 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-5 py-3.5 font-mono text-xs font-semibold text-ink-900">{{ $order->order_number }}</td>
                            <td class="px-5 py-3.5 text-ink-600">{{ $order->created_at->format('M j, Y') }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex rounded-full bg-ink-100 px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wide text-ink-800">{{ $order->status->label() }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-ink-600">
                                {{ $order->payment_method->label() }}
                                <span class="block text-xs text-ink-400">{{ $order->payment_status->label() }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-ink-900">{{ config('store.currency_symbol') }}{{ number_format((float) $order->total, 2) }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('orders.show', $order) }}" class="link-brand text-sm">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-ink-600">No orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-10 flex justify-center">
        {{ $orders->links() }}
    </div>
@endsection
