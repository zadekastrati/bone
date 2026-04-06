<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $pendingOrders = Order::query()->where('status', OrderStatus::Pending)->count();
        $ordersToday = Order::query()->whereDate('created_at', today())->count();
        $revenueToday = Order::query()
            ->whereDate('created_at', today())
            ->sum('total');

        $recentOrders = Order::query()
            ->with(['user:id,name,email'])
            ->latest()
            ->limit(8)
            ->get();

        $recentContactMessages = ContactMessage::query()
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', [
            'stats' => [
                'orders_pending' => $pendingOrders,
                'orders_today' => $ordersToday,
                'revenue_today' => number_format((float) $revenueToday, 2, '.', ''),
                'products' => Product::query()->count(),
                'categories' => Category::query()->count(),
                'users' => User::query()->count(),
                'contacts' => ContactMessage::query()->count(),
            ],
            'recentOrders' => $recentOrders,
            'recentContactMessages' => $recentContactMessages,
        ]);
    }
}
