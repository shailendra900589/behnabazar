<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code', 'discount_type', 'discount_value', 'min_cart_value', 'status'];
    protected $casts = ['discount_value' => 'decimal:2', 'min_cart_value' => 'decimal:2', 'status' => 'boolean'];
}
