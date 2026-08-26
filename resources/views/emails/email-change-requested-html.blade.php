<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Your account email is being changed') }}</title>
</head>
<body style="margin:0;font-family:system-ui,-apple-system,sans-serif;font-size:16px;line-height:1.6;color:#3f3731;background:#f4f0eb;padding:24px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;margin:0 auto;background:#ffffff;border-radius:12px;padding:32px;box-shadow:0 1px 3px rgba(62,52,44,0.08);">
        <tr>
            <td>
                <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#8f7d6d;">{{ __('Security alert') }}</p>
                <p style="margin:0 0 16px;font-size:20px;font-weight:700;color:#2e2824;">{{ __('Your account email is being changed') }}</p>
                <p style="margin:0 0 16px;">{{ __('Hi :name,', ['name' => $userName]) }}</p>
                <p style="margin:0 0 24px;">{!! __('On :date, a request was made to change the email on your :app account from this address to :email. The change won\'t take effect until that address is verified with a code.', ['date' => '<strong>'.e($requestedAt).'</strong>', 'app' => e($appName), 'email' => '<strong>'.e($newEmail).'</strong>']) !!}</p>
                <p style="margin:0 0 16px;font-size:14px;color:#73655a;">{{ __('If you made this request, no action is needed here, just check :email for the verification code. If you didn\'t request this, log in and cancel the pending change from your profile page, or contact us right away.', ['email' => $newEmail]) }}</p>
                <p style="margin:0;font-size:14px;color:#8f7d6d;">- {{ $appName }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
