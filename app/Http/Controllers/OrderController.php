<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        $query = Order::query()
            ->withCount('items')
            ->with([
                'items.variant.product' => fn ($q) => $q->withTrashed()->with('images'),
                'items.product' => fn ($q) => $q->withTrashed()->with('images'),
            ])
            ->latest();

        if (! $request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->id);
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $orders */
        $orders = $query->paginate(20);
        $orders->withQueryString();

        return view('shop.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load([
            'items.variant.product' => fn ($q) => $q->withTrashed()->with('images'),
            'items.product' => fn ($q) => $q->withTrashed()->with('images'),
            'user',
        ]);

        return view('shop.orders.show', compact('order'));
    }
}
