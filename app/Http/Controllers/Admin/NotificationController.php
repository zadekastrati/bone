<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
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

        $orders = $this->confirmedOrders()->latest()->take(self::LIMIT)->get()->map(fn (Order $order) => [
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

        $unreadOrders = $this->confirmedOrders()->when($seenAt, fn ($q) => $q->where('created_at', '>', $seenAt))->count();
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

    /**
     * A card order isn't confirmed yet at placement — the customer can still
     * abandon it at Quipu's hosted page. Same reasoning as the deferred
     * Telegram alert and confirmation email: don't surface it here until
     * payment actually succeeds, so the bell doesn't announce orders that
     * never go through.
     */
    private function confirmedOrders(): Builder
    {
        return Order::query()->where(function (Builder $query): void {
            $query->where('payment_method', '!=', PaymentMethod::Card)
                ->orWhere('payment_status', PaymentStatus::Paid);
        });
    }
}
