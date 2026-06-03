<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationCouponHistory extends Model
{
    protected $fillable = [
        'registration_coupon_id',
        'action',
        'performed_by_user_id',
        'subject_name',
        'subject_email',
        'notes',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(RegistrationCoupon::class, 'registration_coupon_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }
}
