<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = ['vendor_id', 'amount', 'status', 'bank_details'];

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}
