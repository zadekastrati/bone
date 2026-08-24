<div class="table-shell--admin mt-10">
    <div class="overflow-x-auto">
        <table class="data-table data-table--admin">
            <thead>
                <tr>
                    <th></th>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            @forelse ($orders as $order)
                <tbody
                    x-data="orderRow('{{ route('admin.orders.details', $order) }}')"
                    @order-status-updated="$refs.badgeSlot.innerHTML = $event.detail.badge"
                >
                    <tr>
                        <td class="w-10">
                            <button
                                type="button"
                                @click="toggle()"
                                :aria-expanded="open"
                                aria-label="Toggle order details"
                                class="flex size-7 items-center justify-center rounded-full text-ink-500 transition-colors hover:bg-zinc-200/60 hover:text-ink-900"
                            >
                                <x-icons.chevron-down class="size-4 transition-transform" x-bind:class="{ 'rotate-180': open }" />
                            </button>
                        </td>
                        <td class="font-mono text-xs font-semibold text-ink-900">{{ $order->order_number }}</td>
                        <td>
                            <span class="font-medium text-ink-900">{{ $order->user->name }}</span>
                            <span class="mt-0.5 block text-xs text-ink-500">{{ $order->user->email }}</span>
                        </td>
                        <td class="text-ink-600">{{ $order->created_at->format('M j, Y H:i') }}</td>
                        <td x-ref="badgeSlot">
                            <x-admin.badge :tone="$order->status->tone()">{{ $order->status->label() }}</x-admin.badge>
                        </td>
                        <td class="font-semibold text-ink-900">{{ config('store.currency_symbol') }}{{ number_format((float) $order->total, 2) }}</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-3">
                                <button type="button" class="admin-action-link" @click="toggle()">
                                    <span x-text="open ? 'Hide' : 'Details'"></span>
                                </button>
                                <a href="{{ route('admin.orders.show', $order) }}" class="admin-action-link">Manage</a>
                                <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" class="inline" data-confirm="Archive order {{ $order->order_number }}?" data-confirm-label="Archive">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-action-link">Archive</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <tr x-show="open" x-cloak>
                        <td colspan="7" class="!p-0 border-t border-zinc-200/70 bg-zinc-50/80">
                            <div x-show="loading" class="flex items-center gap-2 px-6 py-8 text-sm text-ink-500">
                                <x-icons.spinner class="size-4 animate-spin" /> Loading order details…
                            </div>
                            <div x-ref="detail" x-show="loaded && ! loading"></div>
                        </td>
                    </tr>
                </tbody>
            @empty
                <tbody>
                    <tr>
                        <td colspan="7" class="data-table-empty text-ink-500">No orders found.</td>
                    </tr>
                </tbody>
            @endforelse
        </table>
    </div>
</div>

<div class="pagination-wrap pagination-wrap--admin">
    {{ $orders->links() }}
</div>
