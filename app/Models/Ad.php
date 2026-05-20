<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ad extends Model
{
    protected $fillable = [
        'vendor_id',
        'product_id',
        'location',
        'title',
        'subtitle',
        'cta_text',
        'ad_type',
        'image_path',
        'link_url',
        'video_url',
        'autoplay',
        'code',
        'clicks',
        'sort_order',
        'starts_at',
        'ends_at',
        'source',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'autoplay' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function isActiveNow(): bool
    {
        return $this->status
            && (! $this->starts_at || $this->starts_at->isPast())
            && (! $this->ends_at || $this->ends_at->isFuture());
    }
}
