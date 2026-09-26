@extends('layouts.admin')

@section('title', 'Discount codes')

@section('content')
    <x-page-header title="Discount codes" subtitle="Single-use codes, valid on a customer's first order only." />

    <div class="table-shell--admin mt-10">
        <div class="overflow-x-auto">
            <table class="data-table data-table--admin">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Label</th>
                        <th>Discount</th>
                        <th>Status</th>
                        <th>Used on order</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($codes as $code)
                        <tr>
                            <td class="font-mono font-medium text-ink-900">{{ $code->code }}</td>
                            <td class="text-ink-600">{{ $code->label ?? '—' }}</td>
                            <td class="text-ink-600">{{ $code->percent_off }}%</td>
                            <td>
                                @if ($code->isUsed())
                                    <x-admin.badge tone="neutral">Used</x-admin.badge>
                                @else
                                    <x-admin.badge tone="success">Unused</x-admin.badge>
                                @endif
                            </td>
                            <td class="text-ink-600">
                                @if ($code->usedByOrder)
                                    <a href="{{ route('admin.orders.show', $code->usedByOrder) }}" class="text-accent-700 hover:text-accent-800">{{ $code->usedByOrder->order_number }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-ink-600">{{ $code->created_at->copy()->timezone(config('store.display_timezone'))->format('M j, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="data-table-empty text-ink-500">No discount codes yet. Generate a batch with <code>php artisan discount-codes:generate</code>.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="pagination-wrap pagination-wrap--admin">
        {{ $codes->links() }}
    </div>
@endsection
