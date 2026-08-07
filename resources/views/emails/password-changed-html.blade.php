<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your password was changed</title>
</head>
<body style="margin:0;font-family:system-ui,-apple-system,sans-serif;font-size:16px;line-height:1.6;color:#3f3731;background:#f4f0eb;padding:24px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;margin:0 auto;background:#ffffff;border-radius:12px;padding:32px;box-shadow:0 1px 3px rgba(62,52,44,0.08);">
        <tr>
            <td>
                <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#8f7d6d;">Security alert</p>
                <p style="margin:0 0 16px;font-size:20px;font-weight:700;color:#2e2824;">Your password was changed</p>
                <p style="margin:0 0 16px;">Hi {{ $userName }},</p>
                <p style="margin:0 0 24px;">This confirms the password for your {{ $appName }} account was changed on <strong>{{ $changedAt }}</strong>.</p>
                <p style="margin:0 0 16px;font-size:14px;color:#73655a;">If you made this change, no further action is needed. If you didn't, please reset your password immediately and contact us.</p>
                <p style="margin:0;font-size:14px;color:#8f7d6d;">— {{ $appName }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
