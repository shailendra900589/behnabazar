<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTracking extends Model
{
    protected $fillable = ['order_id', 'status', 'location', 'message'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
