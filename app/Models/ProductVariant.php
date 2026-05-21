<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'color',
        'size',
        'attributes',
        'price',
        'compare_at_price',
        'stock',
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return array<string, string> */
    public function attributeMap(): array
    {
        $map = [];

        if ($this->color) {
            $map['Color'] = $this->color;
        }
        if ($this->size) {
            $map['Size'] = $this->size;
        }

        foreach ($this->attributes ?? [] as $key => $value) {
            if ($value !== null && $value !== '') {
                $map[(string) $key] = (string) $value;
            }
        }

        return $map;
    }

    public function displayLabel(): string
    {
        $parts = array_values($this->attributeMap());

        return $parts !== [] ? implode(' · ', $parts) : 'Standard';
    }

    /** @return array{sale: float, mrp: ?float, percent_off: ?int} */
    public function pricing(Product $product): array
    {
        return $product->pricing(
            $this->price !== null ? (float) $this->price : null,
            $this->compare_at_price !== null ? (float) $this->compare_at_price : null
        );
    }
}
