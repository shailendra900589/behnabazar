<?php

namespace App\Console\Commands;

use App\Models\WhatsappOutbox;
use App\Services\WhatsApp\WhatsAppCloudSender;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Console\Command;

class ProcessWhatsappOutbox extends Command
{
    protected $signature = 'whatsapp:process-outbox {--limit=30}';

    protected $description = 'Auto-send pending WhatsApp outbox (Meta Cloud API)';

    public function handle(WhatsAppCloudSender $cloud, WhatsAppService $whatsapp): int
    {
        if (! $cloud->isConfigured()) {
            $this->comment('Meta Cloud API not configured — outbox stays for manual jugad send.');

            return self::SUCCESS;
        }

        $limit = max(1, (int) $this->option('limit'));
        $sent = 0;
        $failed = 0;

        WhatsappOutbox::pending()
            ->oldest()
            ->limit($limit)
            ->get()
            ->each(function (WhatsappOutbox $row) use ($cloud, &$sent, &$failed) {
                if ($cloud->send($row->to_phone, $row->message, (string) $row->template)) {
                    $row->update(['status' => 'sent', 'sent_at' => now()]);
                    $sent++;
                    usleep(350000);
                } else {
                    $failed++;
                }
            });

        $this->info("Outbox auto-send: {$sent} sent, {$failed} failed, ".WhatsappOutbox::pendingCount().' pending.');

        return self::SUCCESS;
    }
}
