<div class="table-shell--admin mt-10">
    <div class="overflow-x-auto">
        <table class="data-table data-table--admin">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Archived</th>
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
                            <span class="font-medium text-ink-900">{{ $order->user->name ?? trim($order->shipping_first_name.' '.$order->shipping_last_name) }}</span>
                            <span class="mt-0.5 block text-xs text-ink-500">{{ $order->user->email ?? $order->guest_email ?? 'Guest checkout' }}</span>
                        </td>
                        <td class="text-ink-600">{{ $order->deleted_at?->timezone(config('store.display_timezone'))->format('M j, Y') }}</td>
                        <td><x-admin.badge :tone="$order->status->tone()">{{ $order->status->label() }}</x-admin.badge></td>
                        <td class="font-semibold text-ink-900">{{ config('store.currency_symbol') }}{{ number_format((float) $order->total, 2) }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-3">
                                <form method="POST" action="{{ route('admin.orders.restore', $order) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="admin-action-link">Restore</button>
                                </form>
                                <form method="POST" action="{{ route('admin.orders.forceDelete', $order) }}" class="inline" data-confirm="Permanently delete order {{ $order->order_number }}? This cannot be undone." data-confirm-label="Delete permanently">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-action-link text-red-600 hover:text-red-800">Delete permanently</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="data-table-empty text-ink-500">No archived orders.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="pagination-wrap pagination-wrap--admin">
    {{ $orders->links() }}
</div>
