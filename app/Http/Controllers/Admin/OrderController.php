<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderStatusService $orderStatus) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        $query = Order::query()->with(['user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->trim().'%';
            $query->where(function ($q) use ($term): void {
                $q->where('order_number', 'like', $term)
                    ->orWhereHas('user', function ($uq) use ($term): void {
                        $uq->where('first_name', 'like', $term)
                            ->orWhere('last_name', 'like', $term)
                            ->orWhere('email', 'like', $term);
                    });
            });
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $orders */
        $orders = $query->paginate(20);
        $orders->withQueryString();

        if ($request->ajax()) {
            return view('admin.orders.partials.results', compact('orders'));
        }

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['items', 'user']);

        return view('admin.orders.show', compact('order'));
    }

    public function details(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['items.variant.product.images', 'user']);

        return view('admin.orders.partials.detail', compact('order'));
    }

    public function quickStatus(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ]);

        $status = OrderStatus::from($validated['status']);

        $this->orderStatus->updateStatus($order, $status);

        return response()->json([
            'status' => $status->value,
            'label' => $status->label(),
            'badge' => Blade::render(
                '<x-admin.badge :tone="$tone">{{ $label }}</x-admin.badge>',
                ['tone' => $status->tone(), 'label' => $status->label()]
            ),
        ]);
    }

    public function invoice(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['items', 'user']);

        return view('admin.orders.invoice', compact('order'));
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        /** @var array<string, mixed> $data */
        $data = $request->validated();

        if (empty($data['shipped_at'])) {
            $data['shipped_at'] = null;
        }

        $status = OrderStatus::from($data['status']);
        unset($data['status']);

        $this->orderStatus->updateStatus($order, $status, $data);

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order updated.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $this->authorize('delete', $order);

        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Order archived.');
    }

    public function archived(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        $query = Order::onlyTrashed()->with(['user'])->latest('deleted_at');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->trim().'%';
            $query->where(function ($q) use ($term): void {
                $q->where('order_number', 'like', $term)
                    ->orWhereHas('user', function ($uq) use ($term): void {
                        $uq->where('first_name', 'like', $term)
                            ->orWhere('last_name', 'like', $term)
                            ->orWhere('email', 'like', $term);
                    });
            });
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $orders */
        $orders = $query->paginate(20);
        $orders->withQueryString();

        if ($request->ajax()) {
            return view('admin.orders.partials.archived-results', compact('orders'));
        }

        return view('admin.orders.archived', compact('orders'));
    }

    public function restore(Order $order): RedirectResponse
    {
        $this->authorize('restore', $order);

        $order->restore();

        return redirect()->route('admin.orders.archived')->with('success', 'Order restored.');
    }

    public function forceDelete(Order $order): RedirectResponse
    {
        $this->authorize('forceDelete', $order);

        $order->forceDelete();

        return redirect()->route('admin.orders.archived')->with('success', 'Order permanently deleted.');
    }
}
