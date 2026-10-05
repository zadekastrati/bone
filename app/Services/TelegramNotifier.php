<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotifier
{
    /**
     * Mirrors NewOrderAdminMail's trigger point and timing exactly — fires
     * for every order the moment it's placed, including a still-Pending
     * card order, so whoever's watching their phone sees activity as it
     * happens rather than only once a card payment later confirms.
     */
    public function sendNewOrderAlert(Order $order): void
    {
        $token = config('services.telegram.bot_token');
        $chatIds = config('services.telegram.chat_ids');

        if (! $token || $chatIds === []) {
            return;
        }

        $text = __(":order_number\n:customer\n:total :currency · :method", [
            'order_number' => $order->order_number,
            'customer' => $order->shippingFullName(),
            'total' => number_format((float) $order->total, 2),
            'currency' => config('store.currency_symbol'),
            'method' => $order->payment_method->label(),
        ]);

        foreach ($chatIds as $chatId) {
            try {
                Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $text,
                ]);
            } catch (\Throwable $e) {
                Log::error('Telegram new order notification failed', [
                    'order_id' => $order->id,
                    'chat_id' => $chatId,
                    'exception' => $e->getMessage(),
                ]);
            }
        }
    }
}
