<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoinTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'description',
        'order_id',
        'referral_reward_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
