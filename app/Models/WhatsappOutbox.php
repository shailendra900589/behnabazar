<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappOutbox extends Model
{
    protected $table = 'whatsapp_outbox';

    protected $fillable = [
        'to_phone',
        'recipient_label',
        'template',
        'message',
        'wa_url',
        'status',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public static function pendingCount(): int
    {
        return static::pending()->count();
    }

    public function displayPhone(): string
    {
        $digits = preg_replace('/\D/', '', $this->to_phone);
        if (strlen($digits) > 10) {
            return substr($digits, -10);
        }

        return $digits;
    }
}
