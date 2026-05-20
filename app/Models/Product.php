<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'vendor_id',
        'source_product_id',
        'resell_mode',
        'source_base_price',
        'resell_listing_fee',
        'category_id',
        'title',
        'slug',
        'price',
        'description',
        'image',
        'image2',
        'image3',
        'image4',
        'qc_status',
        'reject_reason',
        'qc_verified_by',
        'qc_verified_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'source_base_price' => 'decimal:2',
        'resell_listing_fee' => 'decimal:2',
        'qc_verified_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function sourceProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'source_product_id');
    }

    public function resellListings(): HasMany
    {
        return $this->hasMany(Product::class, 'source_product_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function qcOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'qc_verified_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ProductQuestion::class);
    }

    public function isResellListing(): bool
    {
        return $this->source_product_id !== null;
    }

    public function fulfillmentVendorId(): ?int
    {
        if ($this->isResellListing() && $this->relationLoaded('sourceProduct')) {
            return $this->sourceProduct?->vendor_id;
        }

        if ($this->isResellListing()) {
            return $this->sourceProduct()->value('vendor_id');
        }

        return $this->vendor_id;
    }

    /** @return list<string> */
    public function galleryUrls(): array
    {
        $urls = $this->images->map(fn (ProductImage $img) => $img->url())->values()->all();

        if ($urls !== []) {
            return $urls;
        }

        $legacy = [];
        foreach (['image', 'image2', 'image3', 'image4'] as $col) {
            if ($this->{$col}) {
                $legacy[] = str_starts_with($this->{$col}, 'http')
                    ? $this->{$col}
                    : asset('storage/'.$this->{$col});
            }
        }

        return $legacy !== [] ? $legacy : [$this->imageUrl()];
    }

    public function averageRating(): float
    {
        return (float) $this->reviews()->where('is_approved', true)->avg('rating') ?: 0;
    }

    public function imageUrl(): string
    {
        $first = $this->images->first();
        if ($first) {
            return $first->url();
        }

        if ($this->image) {
            return str_starts_with($this->image, 'http') ? $this->image : asset('storage/'.$this->image);
        }

        return 'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?q=80&w=900&auto=format&fit=crop';
    }

    public function emailSafeImageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http')) {
            $imageHost = parse_url($this->image, PHP_URL_HOST);
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

            return ($imageHost && $appHost && $imageHost === $appHost) ? $this->image : null;
        }

        return url('storage/'.$this->image);
    }
}
