<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NotificationLog extends Model
{
    protected $fillable = ['channel', 'recipient', 'template', 'message', 'status'];

    public static function record(string $channel, string $recipient, string $template, string $message, string $status = 'sent'): void
    {
        static::create([
            'channel' => $channel,
            'recipient' => $recipient,
            'template' => $template,
            'message' => Str::limit($message, 500),
            'status' => $status,
        ]);
    }
}
