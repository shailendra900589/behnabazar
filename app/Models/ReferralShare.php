<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralShare extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'share_token',
        'channel',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(ReferralReward::class);
    }
}
