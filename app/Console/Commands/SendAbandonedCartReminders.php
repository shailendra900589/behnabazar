<?php

namespace App\Console\Commands;

use App\Mail\AbandonedCartMail;
use App\Models\CartItem;
use App\Models\CartReminderLog;
use App\Models\User;
use App\Services\Sms\SmsService;
use App\Services\WhatsApp\WhatsAppService;
use App\Support\NotificationSettings;
use App\Support\SiteBranding;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAbandonedCartReminders extends Command
{
    protected $signature = 'cart:abandoned-remind';

    protected $description = 'Email/SMS customers who left items in cart';

    public function handle(SmsService $sms, WhatsAppService $whatsapp): int
    {
        $cfg = NotificationSettings::all();
        if (! $cfg['abandoned_cart_enabled'] && ! config('notifications.abandoned_cart.enabled')) {
            $this->info('Abandoned cart reminders disabled.');

            return self::SUCCESS;
        }

        $idleHours = $cfg['abandoned_cart_idle_hours'] ?: config('notifications.abandoned_cart.idle_hours', 24);
        $cooldownHours = $cfg['abandoned_cart_cooldown_hours'] ?: config('notifications.abandoned_cart.cooldown_hours', 72);
        $idleBefore = now()->subHours($idleHours);
        $cooldownAfter = now()->subHours($cooldownHours);
        $site = SiteBranding::name();
        $sent = 0;

        $userIds = CartItem::query()
            ->whereNotNull('user_id')
            ->select('user_id')
            ->distinct()
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            $items = CartItem::with(['product', 'variant'])
                ->where('user_id', $userId)
                ->get();

            if ($items->isEmpty()) {
                continue;
            }

            $lastActivity = $items->max('updated_at');
            if ($lastActivity && $lastActivity->gt($idleBefore)) {
                continue;
            }

            $recentReminder = CartReminderLog::where('user_id', $userId)
                ->where('sent_at', '>=', $cooldownAfter)
                ->exists();

            if ($recentReminder) {
                continue;
            }

            $user = User::find($userId);
            if (! $user || (! $user->email && ! $user->phone)) {
                continue;
            }

            $total = $items->sum(fn ($i) => $i->quantity * ($i->variant ? ($i->variant->price ?? $i->product->price) : $i->product->price));

            $cartMsg = "Hi {$user->name}, you left items in your {$site} cart (₹".number_format($total, 0).'). Complete checkout: '.route('cart');

            $dispatched = false;
            if ($user->email && $cfg['abandoned_cart_email']) {
                try {
                    Mail::to($user->email)->send(new AbandonedCartMail($user, $items, $total));
                    $dispatched = true;
                } catch (\Throwable $e) {
                    Log::warning('Abandoned cart email failed', ['user_id' => $userId, 'error' => $e->getMessage()]);
                }
            }

            if ($user->phone && $cfg['abandoned_cart_sms'] && ($cfg['sms_enabled'] || config('notifications.sms.enabled'))) {
                $sms->send($user->phone, $cartMsg, 'abandoned_cart');
                $dispatched = true;
            }

            if ($user->phone && $cfg['abandoned_cart_whatsapp'] && ($cfg['whatsapp_enabled'] || config('notifications.whatsapp.enabled'))) {
                $whatsapp->send($user->phone, $cartMsg, 'abandoned_cart');
                $dispatched = true;
            }

            if (! $dispatched) {
                continue;
            }

            CartReminderLog::create([
                'user_id' => $userId,
                'sent_at' => now(),
                'item_count' => (int) $items->sum('quantity'),
                'cart_total' => $total,
            ]);

            $sent++;
        }

        $this->info("Abandoned cart reminders sent: {$sent}");

        return self::SUCCESS;
    }
}
