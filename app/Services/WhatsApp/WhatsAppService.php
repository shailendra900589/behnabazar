<?php

namespace App\Services\WhatsApp;

use App\Models\NotificationLog;
use App\Models\WhatsappOutbox;
use App\Support\NotificationSettings;
use App\Support\SiteBranding;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppService
{
    private static float $lastCallMeBotAt = 0;

    public function __construct(
        private readonly WhatsAppCloudSender $cloud,
    ) {}

    public function send(string $phone, string $message, string $template = 'generic', ?string $recipientLabel = null): bool
    {
        $phone = $this->normalizePhone($phone);
        if ($phone === '') {
            return false;
        }

        $cfg = config('notifications.whatsapp');
        $settings = NotificationSettings::all();

        if (! ($cfg['enabled'] || $settings['whatsapp_enabled'])) {
            return $this->logOnly($phone, $message, $template);
        }

        $message = $this->brandMessage($message);
        $driver = $this->resolveDriver($cfg['driver'] ?? 'auto');

        try {
            $ok = match ($driver) {
                'cloud', 'meta' => $this->sendCloud($phone, $message, $template),
                'webhook' => $this->sendWebhook($phone, $message),
                'callmebot' => $this->sendCallMeBot($phone, $message),
                'jugad', 'manual', 'outbox' => $this->queueOutbox($phone, $message, $template, $recipientLabel),
                'log' => $this->logOnly($phone, $message, $template),
                default => $this->sendBestAvailable($phone, $message, $template, $recipientLabel),
            };

            $status = match ($driver) {
                'jugad', 'manual', 'outbox' => 'queued',
                'cloud', 'meta', 'callmebot', 'webhook' => $ok ? 'sent' : 'failed',
                default => $ok ? 'sent' : 'queued',
            };
            NotificationLog::record('whatsapp', $phone, $template, $message, $status);

            return $ok;
        } catch (\Throwable $e) {
            Log::warning('WhatsApp send failed', ['phone' => $phone, 'error' => $e->getMessage()]);
            NotificationLog::record('whatsapp', $phone, $template, $message, 'failed');

            return false;
        }
    }

    private function sendBestAvailable(string $phone, string $message, string $template, ?string $label): bool
    {
        if ($this->cloud->isConfigured()) {
            return $this->sendCloud($phone, $message, $template);
        }
        if ($this->apiKey() !== '') {
            return $this->sendCallMeBot($phone, $message);
        }

        return $this->queueOutbox($phone, $message, $template, $label);
    }

    private function sendCloud(string $phone, string $message, string $template): bool
    {
        if (! $this->cloud->isConfigured()) {
            return $this->queueOutbox($phone, $message, $template, null);
        }

        $ok = $this->cloud->send($phone, $message, $template);
        if (! $ok) {
            return $this->queueOutbox($phone, $message, $template.'_retry', null);
        }

        return true;
    }

    public function businessPhone(): string
    {
        $settings = NotificationSettings::all();
        $phone = $settings['whatsapp_business_phone']
            ?: config('notifications.whatsapp.business_phone', '')
            ?: config('notifications.whatsapp.admin_phone', '');

        return preg_replace('/\D/', '', (string) $phone);
    }

    public function businessPhoneNormalized(): string
    {
        $phone = $this->businessPhone();

        return $phone !== '' ? $this->normalizePhone($phone) : '';
    }

    public function isBusinessReady(): bool
    {
        return $this->businessPhone() !== '';
    }

    public function isAutoSendReady(): bool
    {
        return $this->cloud->isConfigured() || $this->apiKey() !== '';
    }

    public function activeDriverLabel(): string
    {
        return $this->resolveDriver(config('notifications.whatsapp.driver', 'auto'));
    }

    public function waMeUrl(string $phone, string $message): string
    {
        $phone = $this->normalizePhone($phone);

        return 'https://wa.me/'.$phone.'?text='.urlencode($message);
    }

    public function businessContactUrl(string $message = ''): string
    {
        $phone = $this->businessPhoneNormalized();
        if ($phone === '') {
            return '';
        }

        return $message !== ''
            ? 'https://wa.me/'.$phone.'?text='.urlencode($message)
            : 'https://wa.me/'.$phone;
    }

    private function resolveDriver(string $driver): string
    {
        if ($driver === 'auto') {
            if ($this->cloud->isConfigured()) {
                return 'cloud';
            }
            if ($this->apiKey() !== '') {
                return 'callmebot';
            }

            return 'jugad';
        }

        if ($driver === 'callmebot') {
            return $this->apiKey() !== '' ? 'callmebot' : ($this->cloud->isConfigured() ? 'cloud' : 'jugad');
        }

        if (in_array($driver, ['business', 'free'], true)) {
            if ($this->cloud->isConfigured()) {
                return 'cloud';
            }

            return $this->apiKey() !== '' ? 'callmebot' : 'jugad';
        }

        if ($driver === 'cloud' || $driver === 'meta') {
            return $this->cloud->isConfigured() ? 'cloud' : 'jugad';
        }

        if (in_array($driver, ['jugad', 'manual', 'outbox'], true)) {
            return 'jugad';
        }

        return $driver;
    }

    private function apiKey(): string
    {
        $settings = NotificationSettings::all();

        return trim($settings['whatsapp_callmebot_api_key']
            ?: (string) config('notifications.whatsapp.callmebot.api_key', ''));
    }

    private function brandMessage(string $message): string
    {
        $site = SiteBranding::name();
        if (str_starts_with($message, '*') || str_starts_with($message, '🛒')) {
            return $message;
        }

        return "*{$site}*\n".$message;
    }

    private function queueOutbox(string $phone, string $message, string $template, ?string $label): bool
    {
        if ($this->businessPhone() === '') {
            Log::warning('[WhatsApp Outbox] Set Business WhatsApp number in Admin → Program settings.');

            return $this->logOnly($phone, $message, $template);
        }

        $recent = WhatsappOutbox::pending()
            ->where('to_phone', $phone)
            ->where('message', $message)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->exists();

        if ($recent) {
            return true;
        }

        WhatsappOutbox::create([
            'to_phone' => $phone,
            'recipient_label' => $label,
            'template' => $template,
            'message' => $message,
            'wa_url' => $this->waMeUrl($phone, $message),
            'status' => 'pending',
        ]);

        return true;
    }

    private function sendWebhook(string $phone, string $message): bool
    {
        $url = config('notifications.whatsapp.webhook_url');
        if (! $url) {
            return $this->queueOutbox($phone, $message, 'webhook_fallback', null);
        }

        $response = Http::timeout(15)->post($url, [
            'phone' => $phone,
            'message' => $message,
            'from' => $this->businessPhoneNormalized(),
        ]);

        return $response->successful();
    }

    private function sendCallMeBot(string $phone, string $message): bool
    {
        $apiKey = $this->apiKey();
        if ($apiKey === '') {
            return $this->sendCloud($phone, $message, 'callmebot_fallback') ?: $this->queueOutbox($phone, $message, 'callmebot_fallback', null);
        }

        $this->throttleCallMeBot();

        $response = Http::timeout(20)->get('https://api.callmebot.com/whatsapp.php', [
            'phone' => '+'.$phone,
            'text' => $message,
            'apikey' => $apiKey,
        ]);

        $body = strtolower((string) $response->body());
        $ok = $response->successful()
            && ! str_contains($body, 'error')
            && ! str_contains($body, 'invalid');

        if (! $ok) {
            return $this->sendCloud($phone, $message, 'callmebot_retry') ?: $this->queueOutbox($phone, $message, 'callmebot_retry', null);
        }

        return true;
    }

    private function throttleCallMeBot(): void
    {
        $minGap = 2.0;
        $now = microtime(true);
        $elapsed = $now - self::$lastCallMeBotAt;
        if (self::$lastCallMeBotAt > 0 && $elapsed < $minGap) {
            usleep((int) (($minGap - $elapsed) * 1_000_000));
        }
        self::$lastCallMeBotAt = microtime(true);
    }

    private function logOnly(string $phone, string $message, string $template): bool
    {
        $link = $this->waMeUrl($phone, $message);
        Log::info('[WhatsApp] TO '.$phone.' — '.$message.' | '.$link);
        NotificationLog::record('whatsapp', $phone, $template, $message.' | '.$link, 'logged');

        return true;
    }

    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 10) {
            return config('notifications.whatsapp.default_country_code', '91').$digits;
        }

        return $digits;
    }
}
