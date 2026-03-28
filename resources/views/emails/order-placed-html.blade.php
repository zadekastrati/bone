<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order confirmed</title>
</head>
<body style="margin:0;font-family:system-ui,-apple-system,sans-serif;font-size:16px;line-height:1.6;color:#1a1a1a;background:#f4f4f5;padding:24px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;margin:0 auto;background:#ffffff;border-radius:12px;padding:32px;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
        <tr>
            <td>
                <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#71717a;">Thank you</p>
                <p style="margin:0 0 16px;font-size:20px;font-weight:700;color:#18181b;">Your order is confirmed</p>
                <p style="margin:0 0 24px;">Hi {{ $order->shipping_first_name }},</p>
                <p style="margin:0 0 24px;">We’ve received your order <strong style="font-family:ui-monospace,monospace;">{{ $order->order_number }}</strong>. You’ll get updates when it ships.</p>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;border-top:1px solid #e4e4e7;border-bottom:1px solid #e4e4e7;">
                    @foreach ($order->items as $item)
                        <tr>
                            <td style="padding:12px 0;vertical-align:top;">
                                <strong>{{ $item->product_name }}</strong><br>
                                <span style="font-size:14px;color:#52525b;">{{ $item->color }} · {{ $item->size }} · × {{ $item->quantity }}</span>
                            </td>
                            <td style="padding:12px 0;text-align:right;white-space:nowrap;font-weight:600;">{{ config('store.currency_symbol') }}{{ number_format((float) $item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </table>

                <p style="margin:0 0 8px;font-size:14px;color:#52525b;">Subtotal · {{ config('store.currency_symbol') }}{{ number_format((float) $order->subtotal, 2) }}</p>
                <p style="margin:0 0 8px;font-size:14px;color:#52525b;">Shipping · {{ config('store.currency_symbol') }}{{ number_format((float) $order->shipping_amount, 2) }}</p>
                <p style="margin:0 0 24px;font-size:18px;font-weight:700;">Total · {{ config('store.currency_symbol') }}{{ number_format((float) $order->total, 2) }}</p>

                <p style="margin:0 0 8px;font-size:14px;color:#52525b;">Payment</p>
                <p style="margin:0 0 24px;">{{ $order->payment_method->label() }} — {{ $order->payment_status->label() }}</p>

                @if ($order->payment_method === \App\Enums\PaymentMethod::BankTransfer)
                    <p style="margin:0 0 8px;font-size:14px;color:#52525b;">Bank transfer reference</p>
                    <p style="margin:0 0 24px;font-family:ui-monospace,monospace;font-size:15px;font-weight:600;">{{ config('store.bank.reference_prefix') }}-{{ $order->order_number }}</p>
                @endif

                <p style="margin:0;font-size:14px;color:#71717a;">— {{ config('app.name') }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
