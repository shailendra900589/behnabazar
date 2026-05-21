<?php

namespace App\Console\Commands;

use App\Models\WhatsappOutbox;
use App\Services\WhatsApp\WhatsAppCloudSender;
use App\Services\WhatsApp\WhatsAppService;
use App\Support\NotificationSettings;
use Illuminate\Console\Command;

class WhatsAppTestCommand extends Command
{
    protected $signature = 'whatsapp:test {phone? : 10-digit recipient}';

    protected $description = 'Test WhatsApp auto-send (Meta Cloud) or queue to Outbox';

    public function handle(WhatsAppService $whatsapp, WhatsAppCloudSender $cloud): int
    {
        $this->line('Driver: '.config('notifications.whatsapp.driver', 'auto').' → '.$whatsapp->activeDriverLabel());
        $this->line('Meta Cloud configured: '.($cloud->isConfigured() ? 'yes' : 'no'));
        $this->line('Business phone: '.(NotificationSettings::all()['whatsapp_business_phone'] ?: '(not set)'));

        $to = $this->argument('phone') ?: NotificationSettings::adminAlertPhone();
        if (! $to) {
            $this->error('Pass phone: php artisan whatsapp:test 9876543210');

            return self::FAILURE;
        }

        $whatsapp->send($to, 'Behna Bazar test — automatic WhatsApp is configured.', 'test', 'Test');

        if ($cloud->isConfigured()) {
            $this->call('whatsapp:process-outbox', ['--limit' => 1]);
        }

        $this->info('Pending outbox: '.WhatsappOutbox::pendingCount());
        if (! $cloud->isConfigured()) {
            $this->line('Tip: Add Meta Cloud token in Admin → Program for 100% auto.');
        }

        return self::SUCCESS;
    }
}
