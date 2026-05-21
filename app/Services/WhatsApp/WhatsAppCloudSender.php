<?php

namespace App\Services\WhatsApp;

use App\Support\NotificationSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppCloudSender
{
    public function isConfigured(): bool
    {
        $c = $this->credentials();

        return $c['token'] !== '' && $c['phone_number_id'] !== '';
    }

    /** @return array{token: string, phone_number_id: string, api_version: string, template_name: string} */
    public function credentials(): array
    {
        $settings = NotificationSettings::all();

        return [
            'token' => trim($settings['whatsapp_cloud_token'] ?: (string) config('notifications.whatsapp.cloud.token', '')),
            'phone_number_id' => trim($settings['whatsapp_cloud_phone_id'] ?: (string) config('notifications.whatsapp.cloud.phone_number_id', '')),
            'api_version' => trim($settings['whatsapp_cloud_api_version'] ?: (string) config('notifications.whatsapp.cloud.api_version', 'v21.0')),
            'template_name' => trim($settings['whatsapp_cloud_template'] ?: (string) config('notifications.whatsapp.cloud.template_name', '')),
        ];
    }

    public function sendText(string $phone, string $message): bool
    {
        $c = $this->credentials();
        if (! $this->isConfigured()) {
            return false;
        }

        $to = $this->apiRecipient($phone);

        $response = Http::withToken($c['token'])
            ->timeout(25)
            ->post($this->endpoint($c), [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $to,
                'type' => 'text',
                'text' => [
                    'preview_url' => true,
                    'body' => $message,
                ],
            ]);

        if (! $response->successful()) {
            Log::warning('[WhatsApp Cloud] send failed', [
                'to' => $to,
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            return false;
        }

        return true;
    }

    public function send(string $phone, string $message, string $context = 'generic'): bool
    {
        $c = $this->credentials();
        if ($c['template_name'] !== '' && $this->shouldUseTemplate($context)) {
            return $this->sendTemplate($phone, $c['template_name'], $this->templateBodyParams($message, $context))
                || $this->sendText($phone, $message);
        }

        return $this->sendText($phone, $message);
    }

    private function shouldUseTemplate(string $context): bool
    {
        return in_array($context, [
            'customer_order_confirm', 'vendor_new_order', 'admin_new_order',
            'abandoned_cart', 'back_in_stock', 'generic',
        ], true);
    }

    /** @return array<int, array{type: string, text: string}> */
    private function templateBodyParams(string $message, string $context): array
    {
        $lines = array_values(array_filter(explode("\n", $message)));
        $site = $lines[0] ?? 'Behna Bazar';
        $body = $lines[1] ?? $message;

        return [
            ['type' => 'text', 'text' => Str::limit($site, 60, '')],
            ['type' => 'text', 'text' => Str::limit($body, 1024, '')],
        ];
    }

    /** @param  array<int, array{type: string, text: string}>  $bodyParams */
    private function sendTemplate(string $phone, string $templateName, array $bodyParams): bool
    {
        $c = $this->credentials();
        $to = $this->apiRecipient($phone);

        $components = [];
        if ($bodyParams !== []) {
            $components[] = [
                'type' => 'body',
                'parameters' => $bodyParams,
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => 'en'],
            ],
        ];

        if ($components !== []) {
            $payload['template']['components'] = $components;
        }

        $response = Http::withToken($c['token'])
            ->timeout(25)
            ->post($this->endpoint($c), $payload);

        if (! $response->successful()) {
            Log::warning('[WhatsApp Cloud] template failed', [
                'template' => $templateName,
                'to' => $to,
                'body' => $response->json() ?? $response->body(),
            ]);

            return false;
        }

        return true;
    }

    /** @param  array{token: string, phone_number_id: string, api_version: string}  $c */
    private function endpoint(array $c): string
    {
        return 'https://graph.facebook.com/'.$c['api_version'].'/'.$c['phone_number_id'].'/messages';
    }

    private function apiRecipient(string $phone): string
    {
        return preg_replace('/\D/', '', $phone);
    }
}
