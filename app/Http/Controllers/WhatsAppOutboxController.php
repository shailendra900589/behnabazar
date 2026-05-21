<?php

namespace App\Http\Controllers;

use App\Models\WhatsappOutbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WhatsAppOutboxController extends Controller
{
    public function markSent(WhatsappOutbox $outbox): JsonResponse
    {
        $this->requireAdmin();

        if ($outbox->status === 'pending') {
            $outbox->update(['status' => 'sent', 'sent_at' => now()]);
        }

        $next = WhatsappOutbox::pending()->oldest()->first();

        return response()->json([
            'ok' => true,
            'next' => $next ? [
                'id' => $next->id,
                'wa_url' => $next->wa_url,
                'phone' => $next->displayPhone(),
                'template' => $next->template,
            ] : null,
            'pending' => WhatsappOutbox::pendingCount(),
        ]);
    }

    public function skip(WhatsappOutbox $outbox): RedirectResponse
    {
        $this->requireAdmin();
        $outbox->update(['status' => 'skipped', 'sent_at' => now()]);

        return redirect()->route('dashboard', ['section' => 'whatsapp'])->with('status', 'Message skipped.');
    }

    public function skipAllPending(): RedirectResponse
    {
        $this->requireAdmin();
        WhatsappOutbox::pending()->update(['status' => 'skipped', 'sent_at' => now()]);

        return redirect()->route('dashboard', ['section' => 'whatsapp'])->with('status', 'All pending WhatsApp messages cleared.');
    }

    private function requireAdmin(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
    }
}
