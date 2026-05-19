@extends('emails.layout')

@section('body')
    <p style="margin:0 0 16px;">Hello,</p>
    <p style="margin:0 0 16px;">{{ $intro }}</p>
    <p style="margin:0 0 20px;font-family:Consolas,Monaco,monospace;font-size:32px;font-weight:700;letter-spacing:6px;color:#4f46e5;">{{ $otp }}</p>
    <p style="margin:0 0 12px;font-size:14px;color:#64748b;">This code expires in 10 minutes.</p>
    <p style="margin:0;font-size:14px;color:#64748b;">If you did not request this, you can safely ignore this email. No changes will be made to your account.</p>
@endsection

@section('footer')
    <p style="margin:0;">This is an automated security message from {{ config('app.name') }}.</p>
@endsection
