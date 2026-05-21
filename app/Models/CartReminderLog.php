<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartReminderLog extends Model
{
    protected $fillable = ['user_id', 'sent_at', 'item_count', 'cart_total'];

    protected $casts = [
        'sent_at' => 'datetime',
        'cart_total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
