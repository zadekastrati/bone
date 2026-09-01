@php
    use App\Enums\OrderStatus;
@endphp

<div class="grid gap-6 p-6 lg:grid-cols-3" x-data="{ status: '{{ $order->status->value }}', saving: false, saved: false, error: false }">
    <div class="space-y-6 lg:col-span-2">
        <div>
            <h3 class="text-xs font-bold uppercase tracking-mega text-ink-500">Items</h3>
            <ul class="mt-3 divide-y divide-ink-100 text-sm">
                @foreach ($order->items as $item)
                    {{-- product_id is the stable link (see OrderItem::product()) — product_variant_id can go null if that exact variant is later deleted, e.g. when the product's variants are edited. Shows the photo for the color the customer actually ordered (item->color), not just the product's generic thumbnail, which could be a different color entirely. --}}
                    @php
                        $__product = $item->product ?? $item->variant?->product;
                        $thumb = $__product?->imagesForColor($item->color)->first() ?? $__product?->thumbnailImage();
                    @endphp
                    <li class="flex flex-wrap items-center justify-between gap-4 py-3">
                        <div class="flex items-center gap-3">
                            <x-product-image-thumb :path="$thumb?->path" :thumb-src="$thumb?->thumbUrl()" size="sm" />
                            <div>
                                <p class="font-semibold text-ink-950">{{ $item->product_name }}</p>
                                <p class="text-xs text-ink-500">{{ $item->color }} · {{ $item->size }} @if ($item->sku) · {{ $item->sku }} @endif</p>
                            </div>
                        </div>
                        <div class="text-right text-sm">
                            <p class="text-ink-600">{{ config('store.currency_symbol') }}{{ number_format((float) $item->unit_price, 2) }} × {{ $item->quantity }}</p>
                            <p class="font-semibold text-ink-900">{{ config('store.currency_symbol') }}{{ number_format((float) $item->line_total, 2) }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-mega text-ink-500">Customer</h3>
                <p class="mt-2 text-sm leading-relaxed text-ink-700">
                    @if ($order->user)
                        {{ $order->user->name }}<br>
                        <a href="mailto:{{ $order->user->email }}" class="link-brand">{{ $order->user->email }}</a><br>
                    @else
                        {{ trim($order->shipping_first_name.' '.$order->shipping_last_name) }} <span class="text-ink-400">(Guest checkout)</span><br>
                    @endif
                    <span class="text-ink-500">Phone: {{ $order->shipping_phone }}</span>
                </p>
            </div>
            <div>
                <h3 class="text-xs font-bold uppercase tracking-mega text-ink-500">Ship to</h3>
                <p class="mt-2 text-sm leading-relaxed text-ink-700">
                    {{ $order->shipping_first_name }} {{ $order->shipping_last_name }}<br>
                    {{ $order->shipping_street }}<br>
                    @if ($order->shipping_building)
                        {{ $order->shipping_building }}<br>
                    @endif
                    {{ $order->shipping_city }}@if ($order->shipping_region), {{ $order->shipping_region }}@endif
                    @if ($order->shipping_postal_code) {{ $order->shipping_postal_code }}@endif<br>
                    {{ config('store.shipping.countries.'.$order->shipping_country.'.label') ?? $order->shipping_country }} ({{ $order->shipping_country }})
                </p>
            </div>
        </div>

        @if ($order->shipping_delivery_notes || $order->customer_notes)
            <div class="grid gap-4 sm:grid-cols-2">
                @if ($order->shipping_delivery_notes)
                    <p class="rounded-2xl bg-amber-50/90 p-4 text-sm text-ink-800 ring-1 ring-amber-200/60">
                        <span class="text-xs font-bold uppercase tracking-mega text-amber-800/80">Delivery instructions</span><br>
                        {{ $order->shipping_delivery_notes }}
                    </p>
                @endif
                @if ($order->customer_notes)
                    <p class="rounded-2xl bg-ink-50/90 p-4 text-sm text-ink-700">
                        <span class="text-xs font-bold uppercase tracking-mega text-ink-400">Order notes</span><br>
                        {{ $order->customer_notes }}
                    </p>
                @endif
            </div>
        @endif
    </div>

    <div class="space-y-4">
        <div>
            <h3 class="text-xs font-bold uppercase tracking-mega text-ink-500">Status</h3>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <x-admin.badge :tone="$order->status->tone()">{{ $order->status->label() }}</x-admin.badge>
                <x-admin.badge :tone="$order->payment_status->tone()">{{ $order->payment_status->label() }}</x-admin.badge>
            </div>
        </div>

        <div class="rounded-2xl border border-zinc-200/80 bg-white/70 p-4">
            <label for="quick-status-{{ $order->order_number }}" class="form-label">Update fulfillment status</label>
            <select id="quick-status-{{ $order->order_number }}" x-model="status" class="form-input mt-1.5">
                @foreach (OrderStatus::cases() as $st)
                    <option value="{{ $st->value }}">{{ $st->label() }}</option>
                @endforeach
            </select>
            <button
                type="button"
                class="btn-secondary mt-3 w-full justify-center"
                :disabled="saving || status === '{{ $order->status->value }}'"
                @click="
                    saving = true; saved = false; error = false;
                    axios.patch('{{ route('admin.orders.quickStatus', $order) }}', { status })
                        .then(({ data }) => {
                            saved = true;
                            $dispatch('order-status-updated', { badge: data.badge });
                        })
                        .catch(() => { error = true; })
                        .finally(() => { saving = false; });
                "
            >
                <span x-show="!saving">Update status</span>
                <span x-show="saving">Saving…</span>
            </button>
            <p x-show="saved" x-cloak class="mt-2 text-xs font-semibold text-emerald-600">Saved.</p>
            <p x-show="error" x-cloak class="mt-2 text-xs font-semibold text-red-600">Could not update. Try again.</p>
        </div>

        <div class="flex flex-col gap-2">
            <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" rel="noopener" class="btn-secondary justify-center">
                Print invoice
            </a>
            @if ($order->user)
                <a href="mailto:{{ $order->user->email }}?subject={{ rawurlencode('Regarding your order '.$order->order_number) }}" class="btn-secondary justify-center">
                    Contact customer
                </a>
            @else
                <a href="tel:{{ $order->shipping_phone }}" class="btn-secondary justify-center">
                    Call customer
                </a>
            @endif
            <a href="{{ route('admin.orders.show', $order) }}" class="admin-action-link text-center">
                Open full order page →
            </a>
        </div>
    </div>
</div>
