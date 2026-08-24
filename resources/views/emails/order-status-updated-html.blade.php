<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no, url=no">
    <title>{{ $order->status->label() }}</title>
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
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f8f5f1;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    @php
        $copy = match ($order->status) {
            \App\Enums\OrderStatus::Shipped => [
                'eyebrow' => __('Order shipped'),
                'heading' => __('Your order is on its way!'),
                'body' => __('Order :number has shipped and is headed your way.', ['number' => $order->order_number]),
            ],
            \App\Enums\OrderStatus::Delivered => [
                'eyebrow' => __('Order delivered'),
                'heading' => __('Your order has arrived'),
                'body' => __('Order :number was marked as delivered. We hope you love it!', ['number' => $order->order_number]),
            ],
            \App\Enums\OrderStatus::Cancelled => [
                'eyebrow' => __('Order cancelled'),
                'heading' => __('Your order was cancelled'),
                'body' => __('Order :number has been cancelled. If this is unexpected, get in touch and we\'ll sort it out.', ['number' => $order->order_number]),
            ],
            default => [
                'eyebrow' => __('Order update'),
                'heading' => __('Your order status changed'),
                'body' => __('Order :number is now :status.', ['number' => $order->order_number, 'status' => $order->status->label()]),
            ],
        };
    @endphp
    <div style="display:none;max-height:0;overflow:hidden;mso-hide:all;opacity:0;">
        {{ $copy['body'] }}
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8f5f1;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" class="bn-container" style="width:600px;max-width:600px;">

                    <tr>
                        <td align="center" style="padding:0 0 24px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:0 0 10px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="width:48px;height:48px;border-radius:12px;background-color:#000000;overflow:hidden;">
                                                    <img src="{{ asset('logo.jpeg') }}" width="48" height="48" alt="{{ config('app.name') }}" style="display:block;width:48px;height:48px;border-radius:12px;object-fit:cover;">
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="font-size:15px;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:#3a2f28;">
                                        {{ config('app.name') }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td class="bn-card" style="background-color:#ffffff;border:1px solid #eee6de;border-radius:16px;padding:40px;">

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:0 0 24px;">
                                        <p style="margin:0 0 10px;font-size:12px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#8f674b;">{{ $copy['eyebrow'] }}</p>
                                        <p style="margin:0 0 6px;font-size:22px;line-height:1.3;font-weight:700;color:#3a2f28;">{{ $copy['heading'] }}</p>
                                        <p style="margin:0;font-size:15px;color:#6c584a;">{{ $copy['body'] }}</p>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8f5f1;border-radius:12px;">
                                <tr>
                                    <td style="padding:20px 24px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="50%" style="padding:0 12px 0 0;vertical-align:top;">
                                                    <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#a58d78;">{{ __('Order') }}</p>
                                                    <p style="margin:0;font-size:15px;font-weight:600;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;color:#3a2f28;">{{ $order->order_number }}</p>
                                                </td>
                                                <td width="50%" style="padding:0 0 0 12px;vertical-align:top;">
                                                    <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#a58d78;">{{ __('Status') }}</p>
                                                    @php
                                                        $statusColors = [
                                                            'success' => ['bg' => '#e7f3ea', 'text' => '#1e7a3d'],
                                                            'danger' => ['bg' => '#fbeaea', 'text' => '#b3261e'],
                                                            'warning' => ['bg' => '#fdf3e2', 'text' => '#946200'],
                                                            'accent' => ['bg' => '#f6efe8', 'text' => '#8f674b'],
                                                            'neutral' => ['bg' => '#eee6de', 'text' => '#58473c'],
                                                        ];
                                                        $statusColor = $statusColors[$order->status->tone()] ?? $statusColors['neutral'];
                                                    @endphp
                                                    <span style="display:inline-block;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:700;background-color:{{ $statusColor['bg'] }};color:{{ $statusColor['text'] }};">{{ $order->status->label() }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

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
