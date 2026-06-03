<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrationCoupon extends Model
{
    protected $fillable = [
        'code',
        'issued_to_name',
        'issued_to_email',
        'issued_to_phone',
        'notes',
        'created_by',
        'used_by_user_id',
        'used_at',
        'revoked_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by_user_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(RegistrationCouponHistory::class);
    }

    public function isAvailable(): bool
    {
        return $this->used_at === null && $this->revoked_at === null;
    }

    public function statusLabel(): string
    {
        if ($this->used_at) {
            return 'Used';
        }

        if ($this->revoked_at) {
            return 'Revoked';
        }

        return 'Available';
    }
}
