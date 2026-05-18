<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdWalletTransaction extends Model
{
    protected $fillable = [
        'vendor_id',
        'amount',
        'type',
        'purpose',
        'razorpay_order_id',
        'razorpay_payment_id',
        'reference',
        'notes',
    ];

    protected $casts = ['amount' => 'decimal:2'];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}
