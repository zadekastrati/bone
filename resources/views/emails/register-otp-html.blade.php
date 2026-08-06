<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirm your email</title>
</head>
<body style="margin:0;font-family:system-ui,-apple-system,sans-serif;font-size:16px;line-height:1.6;color:#3f3731;background:#f4f0eb;padding:24px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;margin:0 auto;background:#ffffff;border-radius:12px;padding:32px;box-shadow:0 1px 3px rgba(62,52,44,0.08);">
        <tr>
            <td>
                <p style="margin:0 0 16px;">Hi {{ $userName }},</p>
                <p style="margin:0 0 24px;">Use this code to <strong>confirm your email</strong> and finish creating your {{ $appName }} account:</p>
                <p style="margin:0 0 24px;font-size:28px;font-weight:700;letter-spacing:0.25em;text-align:center;font-family:ui-monospace,monospace;">{{ $code }}</p>
                <p style="margin:0 0 16px;font-size:14px;color:#73655a;">This code expires in 15 minutes. If you did not register, you can ignore this email.</p>
                <p style="margin:0;font-size:14px;color:#8f7d6d;">— {{ $appName }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
