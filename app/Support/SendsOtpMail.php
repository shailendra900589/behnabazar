<?php

namespace App\Support;

use App\Mail\OtpMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

trait SendsOtpMail
{
    protected function sendOtpMail(string $email, string $otp, string $purpose): bool
    {
        try {
            Mail::to($email)->send(new OtpMail($otp, $purpose));

            return true;
        } catch (\Throwable $e) {
            Log::error('OTP mail failed', ['email' => $email, 'purpose' => $purpose, 'error' => $e->getMessage()]);

            return false;
        }
    }
}
