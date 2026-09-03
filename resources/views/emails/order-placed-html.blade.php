<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no, url=no">
    <title>{{ __('Order confirmed') }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <style>
        table, td { border-collapse: collapse; }
        * { font-family: Helvetica, Arial, sans-serif !important; }
    </style>
    <![endif]-->
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; height: 100% !important; }
        @media only screen and (max-width: 600px) {
            .bn-container { width: 100% !important; }
            .bn-card { padding: 24px 20px !important; }
            .bn-stack { display: block !important; width: 100% !important; }
            .bn-stack-cell { display: block !important; width: 100% !important; padding-right: 0 !important; }
            .bn-stack-cell + .bn-stack-cell { padding-top: 16px !important; }
            .bn-hide-mobile { display: none !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f8f5f1;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    {{-- Preheader: shown in inbox preview, hidden in the body --}}
    <div style="display:none;max-height:0;overflow:hidden;mso-hide:all;opacity:0;">
        {{ __('We\'ve received your order :number. You\'ll get updates when it ships.', ['number' => $order->order_number]) }}
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8f5f1;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" class="bn-container" style="width:600px;max-width:600px;">

                    {{-- Logo header --}}
                    <tr>
                        <td align="center" style="padding:0 0 24px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:0 0 10px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="width:48px;height:48px;">
                                                    <img src="{{ asset('email-logo-icon.png') }}" width="48" height="48" alt="{{ config('app.name') }}" style="display:block;width:48px;height:48px;object-fit:contain;">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center">
                                        <img src="{{ asset('email-logo-wordmark.png') }}" width="100" height="40" alt="{{ config('app.name') }}" style="display:block;width:100px;height:40px;">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td class="bn-card" style="background-color:#ffffff;border:1px solid #eee6de;border-radius:16px;padding:40px;">

                            {{-- Eyebrow + heading --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:0 0 24px;">
                                        <p style="margin:0 0 10px;font-size:12px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#8f674b;">{{ __('Order confirmed') }}</p>
                                        <p style="margin:0 0 6px;font-size:22px;line-height:1.3;font-weight:700;color:#3a2f28;">{{ __('Thank you, :name!', ['name' => $order->shipping_first_name]) }}</p>
                                        <p style="margin:0;font-size:15px;color:#6c584a;">{!! __('We\'ve received your order and will let you know as soon as it ships.') !!}</p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Order meta --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8f5f1;border-radius:12px;">
                                <tr>
                                    <td style="padding:20px 24px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr class="bn-stack">
                                                <td class="bn-stack-cell" width="50%" style="padding:0 12px 0 0;vertical-align:top;">
                                                    <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#a58d78;">{{ __('Order') }}</p>
                                                    <p style="margin:0;font-size:15px;font-weight:600;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;color:#3a2f28;">{{ $order->order_number }}</p>
                                                </td>
                                                <td class="bn-stack-cell" width="50%" style="padding:0 0 0 12px;vertical-align:top;">
                                                    <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#a58d78;">{{ __('Date') }}</p>
                                                    <p style="margin:0;font-size:15px;font-weight:600;color:#3a2f28;">{{ $order->created_at->copy()->timezone(config('store.display_timezone'))->format('M j, Y') }}</p>
                                                </td>
                                            </tr>
                                            <tr class="bn-stack">
                                                <td class="bn-stack-cell" width="50%" style="padding:16px 12px 0 0;vertical-align:top;">
                                                    <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#a58d78;">{{ __('Payment') }}</p>
                                                    <p style="margin:0;font-size:15px;font-weight:600;color:#3a2f28;">{{ $order->payment_method->label() }}</p>
                                                </td>
                                                <td class="bn-stack-cell" width="50%" style="padding:16px 0 0 12px;vertical-align:top;">
                                                    <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#a58d78;">{{ __('Status') }}</p>
                                                    @php
                                                        $statusColors = [
                                                            'success' => ['bg' => '#e7f3ea', 'text' => '#1e7a3d'],
                                                            'danger' => ['bg' => '#fbeaea', 'text' => '#b3261e'],
                                                            'neutral' => ['bg' => '#eee6de', 'text' => '#58473c'],
                                                        ];
                                                        $statusColor = $statusColors[$order->payment_status->tone()] ?? $statusColors['neutral'];
                                                    @endphp
                                                    <span style="display:inline-block;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:700;background-color:{{ $statusColor['bg'] }};color:{{ $statusColor['text'] }};">{{ $order->payment_status->label() }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- Shipping address --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;">
                                <tr>
                                    <td style="padding:0;">
                                        <p style="margin:0 0 6px;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#a58d78;">{{ __('Ship to') }}</p>
                                        <p style="margin:0;font-size:14px;line-height:1.6;color:#58473c;">
                                            {{ $order->shipping_first_name }} {{ $order->shipping_last_name }}<br>
                                            {{ $order->shipping_street }}@if ($order->shipping_building), {{ $order->shipping_building }}@endif<br>
                                            {{ $order->shipping_city }}@if ($order->shipping_region), {{ $order->shipping_region }}@endif
                                            @if ($order->shipping_postal_code) {{ $order->shipping_postal_code }}@endif<br>
                                            {{ __(config('store.shipping.countries.'.$order->shipping_country.'.label') ?? $order->shipping_country) }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Items --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:28px;border-top:1px solid #eee6de;">
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td style="padding:18px 0;border-bottom:1px solid #eee6de;vertical-align:top;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td style="vertical-align:top;">
                                                        <p style="margin:0 0 4px;font-size:15px;font-weight:600;color:#3a2f28;">{{ $item->product_name }}</p>
                                                        <p style="margin:0;font-size:13px;color:#866f5d;">
                                                            {{ $item->color }} · {{ $item->size }}
                                                            @if ($item->sku)
                                                                <span class="bn-hide-mobile">· {{ $item->sku }}</span>
                                                            @endif
                                                            <br>
                                                            {{ __('Qty :qty × :price', ['qty' => $item->quantity, 'price' => config('store.currency_symbol').number_format((float) $item->unit_price, 2)]) }}
                                                        </p>
                                                    </td>
                                                    <td width="90" align="right" style="vertical-align:top;white-space:nowrap;">
                                                        <p style="margin:0;font-size:15px;font-weight:700;color:#3a2f28;">{{ config('store.currency_symbol') }}{{ number_format((float) $item->line_total, 2) }}</p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>

                            {{-- Totals --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px;">
                                <tr>
                                    <td style="padding:4px 0;font-size:14px;color:#6c584a;">{{ __('Subtotal') }}</td>
                                    <td align="right" style="padding:4px 0;font-size:14px;color:#3a2f28;">{{ config('store.currency_symbol') }}{{ number_format((float) $order->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 0;font-size:14px;color:#6c584a;">{{ __('Shipping') }}</td>
                                    <td align="right" style="padding:4px 0;font-size:14px;color:#3a2f28;">{{ config('store.currency_symbol') }}{{ number_format((float) $order->shipping_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 0 0;border-top:1px solid #eee6de;font-size:17px;font-weight:700;color:#3a2f28;">{{ __('Total') }}</td>
                                    <td align="right" style="padding:14px 0 0;border-top:1px solid #eee6de;font-size:17px;font-weight:700;color:#3a2f28;">{{ config('store.currency_symbol') }}{{ number_format((float) $order->total, 2) }}</td>
                                </tr>
                            </table>

                            @if ($order->payment_method === \App\Enums\PaymentMethod::BankTransfer)
                                {{-- Bank transfer reference --}}
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:24px;background-color:#f6efe8;border-radius:12px;">
                                    <tr>
                                        <td style="padding:16px 20px;">
                                            <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#8f674b;">{{ __('Bank transfer reference') }}</p>
                                            <p style="margin:0;font-size:16px;font-weight:700;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;color:#3a2f28;">{{ config('store.bank.reference_prefix') }}-{{ $order->order_number }}</p>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            {{-- CTA --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:32px;">
                                <tr>
                                    <td align="center">
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="border-radius:8px;background-color:#5f4332;">
                                                    <a href="{{ route('orders.show', $order) }}" style="display:inline-block;padding:14px 36px;font-size:14px;font-weight:700;letter-spacing:0.04em;color:#ffffff;text-decoration:none;border-radius:8px;">{{ __('View your order') }}</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding:28px 16px 0;">
                            <p style="margin:0 0 6px;font-size:13px;color:#a58d78;">
                                {!! __('Questions about your order? :link', ['link' => '<a href="'.route('contact').'" style="color:#8f674b;text-decoration:underline;">'.__('Contact us').'</a>']) !!}
                            </p>
                            <p style="margin:0;font-size:12px;color:#c4b19f;">
                                &copy; {{ now()->year }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
