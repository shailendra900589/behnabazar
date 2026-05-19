<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subjectLine ?? config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f1f5f9;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;">
                <tr>
                    <td style="background-color:#4f46e5;padding:20px 28px;">
                        <p style="margin:0;font-size:18px;font-weight:700;color:#ffffff;">{{ config('app.name') }}</p>
                        <p style="margin:6px 0 0;font-size:13px;color:#e0e7ff;">Multipurpose marketplace</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:28px;color:#0f172a;font-size:16px;line-height:1.6;">
                        @yield('body')
                    </td>
                </tr>
                <tr>
                    <td style="padding:20px 28px;background-color:#f8fafc;border-top:1px solid #e2e8f0;font-size:12px;line-height:1.5;color:#64748b;">
                        @yield('footer')
                        <p style="margin:12px 0 0;">Questions? Reply to this email or contact {{ \App\Support\MailConfig::supportEmail() }}</p>
                        <p style="margin:8px 0 0;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
