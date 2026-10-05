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

        $text = $this->buildMessage($order);

        foreach ($chatIds as $chatId) {
            try {
                Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
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

    private function buildMessage(Order $order): string
    {
        $order->loadMissing(['items', 'user']);
        $currency = config('store.currency_symbol');
        $rule = str_repeat('━', 20);
        $email = $order->user?->email ?? $order->guest_email ?? '—';

        $lines = [
            $rule,
            '<b>NEW ORDER</b>',
            $rule,
            '',
            'Order #'.$this->esc($order->order_number),
            $order->created_at->copy()->timezone(config('store.display_timezone'))->format('d M Y').' • '.$order->created_at->copy()->timezone(config('store.display_timezone'))->format('H:i'),
            '',
            '<b>Customer</b>',
            $this->esc($order->shippingFullName()),
            $this->esc($order->shipping_phone),
            $this->esc($email),
            '',
            '<b>Shipping Address</b>',
            $this->esc($this->formatAddress($order)),
            '',
            '<b>Products</b>',
        ];

        foreach ($order->items as $item) {
            $variant = implode(', ', array_filter([$item->color, $item->size]));
            $label = $this->esc($item->product_name).($variant !== '' ? ' ('.$this->esc($variant).')' : '');
            $lines[] = '• '.$label.' ×'.$item->quantity.' .... '.$currency.number_format((float) $item->line_total, 2);
        }

        $lines[] = '';
        $lines[] = 'Subtotal: '.$currency.number_format((float) $order->subtotal, 2);
        $lines[] = 'Shipping: '.$currency.number_format((float) $order->shipping_amount, 2);
        $lines[] = '';
        $lines[] = '<b>Total Amount</b>';
        $lines[] = $currency.number_format((float) $order->total, 2);
        $lines[] = '';
        $lines[] = '<b>Payment Method</b>';
        $lines[] = $this->esc($order->payment_method->label());

        return implode("\n", $lines);
    }

    private function formatAddress(Order $order): string
    {
        $parts = array_filter([
            $order->shipping_street,
            $order->shipping_building,
        ]);
        $street = implode(', ', $parts);

        $cityParts = array_filter([
            $order->shipping_city,
            $order->shipping_region,
            $order->shipping_postal_code,
        ]);
        $cityLine = implode(', ', $cityParts);

        $country = config('store.shipping.countries.'.$order->shipping_country.'.label') ?? $order->shipping_country;

        return implode("\n", array_filter([$street, $cityLine, $country]));
    }

    /**
     * Telegram's HTML parse mode treats <, >, and & as markup — any of
     * those appearing in a customer-entered name, address, or product
     * title would otherwise break the message or get silently dropped.
     */
    private function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
