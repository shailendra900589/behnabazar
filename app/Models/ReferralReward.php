<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralReward extends Model
{
    protected $fillable = [
        'referrer_id',
        'referee_id',
        'beneficiary_type',
        'trigger_type',
        'status',
        'reward_coins',
        'reward_amount',
        'order_id',
        'product_id',
        'referral_share_id',
        'admin_note',
        'approved_by',
        'approved_at',
        'qualified_at',
    ];

    protected function casts(): array
    {
        return [
            'reward_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'qualified_at' => 'datetime',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referee_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
