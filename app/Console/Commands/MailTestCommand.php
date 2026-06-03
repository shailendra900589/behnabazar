<?php

namespace App\Console\Commands;

use App\Mail\OtpMail;
use App\Support\MailConfigurator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MailTestCommand extends Command
{
    protected $signature = 'marketplace:mail-test {email : Recipient address}';

    protected $description = 'Send a test OTP email and report SMTP / sendmail errors';

    public function handle(): int
    {
        MailConfigurator::applyTransportDefaults();

        $email = strtolower(trim((string) $this->argument('email')));

        $this->info('Mail diagnostics');
        $this->line('  Default mailer: '.config('mail.default'));
        $this->line('  SMTP host: '.(env('MAIL_HOST') ?: '(empty)'));
        $this->line('  SMTP port: '.(env('MAIL_PORT') ?: '(empty)'));
        $this->line('  SMTP scheme: '.(env('MAIL_SCHEME') ?: '(auto)'));
        $this->line('  From: '.config('mail.from.address'));

        foreach (MailConfigurator::diagnose() as $issue) {
            $this->warn('  • '.$issue);
        }

        if (! MailConfigurator::isConfigured()) {
            $this->error('Mail is not configured. Update .env (see docs/MAIL_HOSTINGER.md) then retry.');

            return self::FAILURE;
        }

        $otp = '123456';
        $sent = false;

        foreach (MailConfigurator::mailersToTry() as $mailer) {
            $this->line('Trying mailer: '.$mailer);

            try {
                Mail::mailer($mailer)->to($email)->send(new OtpMail($otp, 'customer'));
                $this->info("SUCCESS via {$mailer} — check inbox/spam for code {$otp}");

                return self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error("  Failed ({$mailer}): ".$e->getMessage());
            }
        }

        $this->error('All mailers failed. Fix .env MAIL_* settings on the server.');

        return self::FAILURE;
    }
}
