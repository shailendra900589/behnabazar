<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'product_id', 'product_name', 'quantity', 'variant_id', 'unit_price', 'total_price',
        'customer_name', 'phone', 'address', 'coupon_code', 'discount_amount', 'coin_discount', 'coins_earned',
        'payment_method', 'status', 'tracking_msg', 'return_status', 'return_reason'
    ];

    protected $casts = ['unit_price' => 'decimal:2', 'total_price' => 'decimal:2', 'discount_amount' => 'decimal:2', 'coin_discount' => 'decimal:2'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'variant_id'); }
    public function trackings() { return $this->hasMany(OrderTracking::class)->orderBy('created_at', 'desc'); }
}
