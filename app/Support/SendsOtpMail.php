<?php

namespace App\Support;

use App\Mail\OtpMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

trait SendsOtpMail
{
    protected function sendOtpMail(string $email, string $otp, string $purpose): bool
    {
        MailConfigurator::applyTransportDefaults();

        if (! MailConfigurator::isConfigured()) {
            Log::warning('OTP mail skipped — mail not configured', [
                'email' => $email,
                'purpose' => $purpose,
                'hints' => MailConfigurator::diagnose(),
            ]);

            return false;
        }

        $mailable = new OtpMail($otp, $purpose);
        $lastError = null;

        foreach (MailConfigurator::mailersToTry() as $mailer) {
            try {
                Mail::mailer($mailer)->to($email)->send($mailable);

                if ($mailer !== config('mail.default')) {
                    Log::info('OTP mail sent via fallback mailer', ['mailer' => $mailer, 'email' => $email]);
                }

                return true;
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                Log::error('OTP mail failed', [
                    'email' => $email,
                    'purpose' => $purpose,
                    'mailer' => $mailer,
                    'error' => $lastError,
                ]);
            }
        }

        if ($lastError) {
            Log::error('OTP mail exhausted all mailers', ['email' => $email, 'error' => $lastError]);
        }

        return false;
    }
}
