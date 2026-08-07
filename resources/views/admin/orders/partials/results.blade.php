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
                        <td class="font-mono text-xs font-semibold text-ink-900">{{ $order->order_number }}</td>
                        <td>
                            <span class="font-medium text-ink-900">{{ $order->user->name }}</span>
                            <span class="mt-0.5 block text-xs text-ink-500">{{ $order->user->email }}</span>
                        </td>
                        <td class="text-ink-600">{{ $order->created_at->format('M j, Y H:i') }}</td>
                        <td>
                            <x-admin.badge :tone="$order->status->tone()">{{ $order->status->label() }}</x-admin.badge>
                        </td>
                        <td class="font-semibold text-ink-900">{{ config('store.currency_symbol') }}{{ number_format((float) $order->total, 2) }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="admin-action-link">Manage</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="data-table-empty text-ink-500">No orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination-wrap pagination-wrap--admin">
    {{ $orders->links() }}
</div>
