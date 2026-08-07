<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    private const LIMIT = 8;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $seenAt = $user->notifications_seen_at;

        $orders = Order::query()->latest()->take(self::LIMIT)->get()->map(fn (Order $order) => [
            'id' => 'order-'.$order->id,
            'type' => 'order',
            'title' => 'New order '.$order->order_number,
            'subtitle' => $order->shippingFullName().' — '.config('store.currency_symbol').number_format((float) $order->total, 2),
            'url' => route('admin.orders.show', $order),
            'created_at' => $order->created_at->toIso8601String(),
            'timestamp' => $order->created_at->diffForHumans(),
            'unread' => $seenAt === null || $order->created_at->gt($seenAt),
        ]);

        $messages = ContactMessage::query()->latest()->take(self::LIMIT)->get()->map(fn (ContactMessage $message) => [
            'id' => 'message-'.$message->id,
            'type' => 'message',
            'title' => 'New message from '.$message->name,
            'subtitle' => Str::limit($message->message, 60),
            'url' => route('admin.messages.show', $message->id),
            'created_at' => $message->created_at->toIso8601String(),
            'timestamp' => $message->created_at->diffForHumans(),
            'unread' => $seenAt === null || $message->created_at->gt($seenAt),
        ]);

        $items = $orders->concat($messages)
            ->sortByDesc('created_at')
            ->take(self::LIMIT)
            ->values();

        $unreadOrders = Order::query()->when($seenAt, fn ($q) => $q->where('created_at', '>', $seenAt))->count();
        $unreadMessages = ContactMessage::query()->when($seenAt, fn ($q) => $q->where('created_at', '>', $seenAt))->count();

        return response()->json([
            'items' => $items,
            'unread_count' => $unreadOrders + $unreadMessages,
        ]);
    }

    public function markSeen(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->notifications_seen_at = now();
        $user->save();

        return response()->json(['success' => true]);
    }
}
