<?php

namespace App\Services\Sms;

use App\Models\NotificationLog;
use App\Support\NotificationSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $phone, string $message, string $template = 'generic'): bool
    {
        $phone = $this->normalizePhone($phone);
        if ($phone === '') {
            return false;
        }

        $cfg = config('notifications.sms');
        $settings = NotificationSettings::all();

        if (! ($cfg['enabled'] || $settings['sms_enabled'])) {
            return $this->logOnly($phone, $message, $template);
        }

        $driver = $cfg['driver'] ?? 'log';

        try {
            $ok = match ($driver) {
                'twilio' => $this->sendTwilio($phone, $message),
                'msg91' => $this->sendMsg91($phone, $message),
                default => $this->logOnly($phone, $message, $template),
            };

            NotificationLog::record('sms', $phone, $template, $message, $ok ? 'sent' : 'failed');

            return $ok;
        } catch (\Throwable $e) {
            Log::warning('SMS send failed', ['phone' => $phone, 'error' => $e->getMessage()]);
            NotificationLog::record('sms', $phone, $template, $message, 'failed');

            return false;
        }
    }

    private function sendTwilio(string $phone, string $message): bool
    {
        $sid = config('notifications.sms.twilio.sid');
        $token = config('notifications.sms.twilio.token');
        $from = config('notifications.sms.twilio.from');

        if (! $sid || ! $token || ! $from) {
            return $this->logOnly($phone, $message, 'twilio_missing_config');
        }

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'To' => '+'.$phone,
                'From' => $from,
                'Body' => $message,
            ]);

        return $response->successful();
    }

    private function sendMsg91(string $phone, string $message): bool
    {
        $authKey = config('notifications.sms.msg91.auth_key');
        $sender = config('notifications.sms.msg91.sender');

        if (! $authKey) {
            return $this->logOnly($phone, $message, 'msg91_missing_config');
        }

        $response = Http::withHeaders(['authkey' => $authKey])
            ->post('https://control.msg91.com/api/sendhttp.php', [
                'mobiles' => $phone,
                'message' => $message,
                'sender' => $sender,
                'route' => config('notifications.sms.msg91.route', '4'),
                'country' => '91',
            ]);

        return $response->successful();
    }

    private function logOnly(string $phone, string $message, string $template): bool
    {
        Log::info('[SMS] '.$phone.' — '.$message);
        NotificationLog::record('sms', $phone, $template, $message, 'logged');

        return true;
    }

    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 10) {
            return config('notifications.sms.default_country_code', '91').$digits;
        }

        return $digits;
    }
}
