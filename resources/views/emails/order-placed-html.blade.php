<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Order confirmed') }}</title>
</head>
<body style="margin:0;font-family:system-ui,-apple-system,sans-serif;font-size:16px;line-height:1.6;color:#3f3731;background:#f4f0eb;padding:24px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;margin:0 auto;background:#ffffff;border-radius:12px;padding:32px;box-shadow:0 1px 3px rgba(62,52,44,0.08);">
        <tr>
            <td>
                <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#8f7d6d;">{{ __('Thank you') }}</p>
                <p style="margin:0 0 16px;font-size:20px;font-weight:700;color:#2e2824;">{{ __('Your order is confirmed') }}</p>
                <p style="margin:0 0 24px;">{{ __('Hi :name,', ['name' => $order->shipping_first_name]) }}</p>
                <p style="margin:0 0 24px;">{!! __('We\'ve received your order :number. You\'ll get updates when it ships.', ['number' => '<strong style="font-family:ui-monospace,monospace;">'.e($order->order_number).'</strong>']) !!}</p>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;border-top:1px solid #e5dbd2;border-bottom:1px solid #e5dbd2;">
                    @foreach ($order->items as $item)
                        <tr>
                            <td style="padding:12px 0;vertical-align:top;">
                                <strong>{{ $item->product_name }}</strong><br>
                                <span style="font-size:14px;color:#73655a;">{{ $item->color }} · {{ $item->size }} · × {{ $item->quantity }}</span>
                            </td>
                            <td style="padding:12px 0;text-align:right;white-space:nowrap;font-weight:600;">{{ config('store.currency_symbol') }}{{ number_format((float) $item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </table>

                <p style="margin:0 0 8px;font-size:14px;color:#52525b;">{{ __('Subtotal') }} · {{ config('store.currency_symbol') }}{{ number_format((float) $order->subtotal, 2) }}</p>
                <p style="margin:0 0 8px;font-size:14px;color:#52525b;">{{ __('Shipping') }} · {{ config('store.currency_symbol') }}{{ number_format((float) $order->shipping_amount, 2) }}</p>
                <p style="margin:0 0 24px;font-size:18px;font-weight:700;">{{ __('Total') }} · {{ config('store.currency_symbol') }}{{ number_format((float) $order->total, 2) }}</p>

                <p style="margin:0 0 8px;font-size:14px;color:#73655a;">{{ __('Payment') }}</p>
                <p style="margin:0 0 24px;">{{ $order->payment_method->label() }} — {{ $order->payment_status->label() }}</p>

                @if ($order->payment_method === \App\Enums\PaymentMethod::BankTransfer)
                    <p style="margin:0 0 8px;font-size:14px;color:#52525b;">{{ __('Bank transfer reference') }}</p>
                    <p style="margin:0 0 24px;font-family:ui-monospace,monospace;font-size:15px;font-weight:600;">{{ config('store.bank.reference_prefix') }}-{{ $order->order_number }}</p>
                @endif

                <p style="margin:0;font-size:14px;color:#8f7d6d;">— {{ config('app.name') }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
