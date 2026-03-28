<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
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
                        $uq->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term);
                    });
            });
        }

        $orders = $query->paginate(25)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['items', 'user']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        /** @var array<string, mixed> $data */
        $data = $request->validated();

        if (empty($data['shipped_at'])) {
            $data['shipped_at'] = null;
        }

        if (($data['status'] ?? '') === OrderStatus::Shipped->value
            && empty($data['shipped_at'])
            && $order->shipped_at === null) {
            $data['shipped_at'] = now()->toDateTimeString();
        }

        $order->update($data);

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order updated.');
    }
}
