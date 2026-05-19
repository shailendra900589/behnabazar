Hello,

{{ $intro }}

Your code: {{ $otp }}

This code expires in 10 minutes.

If you did not request this, ignore this email.

— {{ config('app.name') }}
{{ \App\Support\MailConfig::supportEmail() }}
