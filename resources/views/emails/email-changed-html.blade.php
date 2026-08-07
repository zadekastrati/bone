<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>This is your new account email</title>
</head>
<body style="margin:0;font-family:system-ui,-apple-system,sans-serif;font-size:16px;line-height:1.6;color:#3f3731;background:#f4f0eb;padding:24px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;margin:0 auto;background:#ffffff;border-radius:12px;padding:32px;box-shadow:0 1px 3px rgba(62,52,44,0.08);">
        <tr>
            <td>
                <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#8f7d6d;">Security alert</p>
                <p style="margin:0 0 16px;font-size:20px;font-weight:700;color:#2e2824;">This is now your {{ $appName }} account email</p>
                <p style="margin:0 0 16px;">Hi {{ $userName }},</p>
                <p style="margin:0 0 24px;">This confirms that on <strong>{{ $changedAt }}</strong>, this email address became the one linked to your {{ $appName }} account. You'll use it to log in and receive order updates from now on.</p>
                <p style="margin:0 0 16px;font-size:14px;color:#73655a;">If you didn't make this change, please contact us right away.</p>
                <p style="margin:0;font-size:14px;color:#8f7d6d;">— {{ $appName }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
