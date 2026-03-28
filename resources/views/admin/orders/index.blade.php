@extends('layouts.app')

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

    <div class="mt-10 overflow-hidden rounded-3xl border border-ink-200/60 bg-white/95 shadow-soft ring-1 ring-ink-950/[0.03]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ink-200 text-left text-sm">
                <thead class="bg-ink-50/90">
                    <tr>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Order</th>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Customer</th>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Date</th>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Status</th>
                        <th class="px-5 py-3.5 font-semibold text-ink-700">Total</th>
                        <th class="px-5 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-5 py-3.5 font-mono text-xs font-semibold text-ink-900">{{ $order->order_number }}</td>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-ink-950">{{ $order->user->name }}</span>
                                <span class="mt-0.5 block text-xs text-ink-500">{{ $order->user->email }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-ink-600">{{ $order->created_at->format('M j, Y H:i') }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex rounded-full bg-ink-100 px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wide text-ink-800">{{ $order->status->label() }}</span>
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-ink-900">{{ config('store.currency_symbol') }}{{ number_format((float) $order->total, 2) }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="link-brand text-sm">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-ink-600">No orders found.</td>
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
