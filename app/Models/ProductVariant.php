<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    /** @var list<string> */
    private const INTERNAL_KEYS = [
        'id',
        'product_id',
        'price',
        'compare_at_price',
        'stock',
        'created_at',
        'updated_at',
        'color',
        'size',
        'attributes',
    ];

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

    protected static function booted(): void
    {
        static::saving(function (ProductVariant $variant) {
            $variant->attributes = self::cleanAttributesArray($variant->attributes);
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return array<string, string> */
    public function attributeMap(): array
    {
        $map = [];

        foreach (self::cleanAttributesArray($this->rawAttributesPayload()) as $key => $value) {
            $map[$key] = $value;
        }

        if ($this->color && ! $this->mapHasValue($map, (string) $this->color)) {
            $map['Color'] = (string) $this->color;
        }

        if ($this->size && ! $this->mapHasValue($map, (string) $this->size)) {
            $map['Size'] = (string) $this->size;
        }

        return $map;
    }

    public function displayLabel(): string
    {
        $map = $this->attributeMap();

        if ($map === []) {
            return 'Standard';
        }

        $parts = [];
        ksort($map, SORT_NATURAL | SORT_FLAG_CASE);
        foreach ($map as $label => $value) {
            $parts[] = $label.': '.$value;
        }

        return implode(' · ', $parts);
    }

    /** @return array{sale: float, mrp: ?float, percent_off: ?int} */
    public function pricing(Product $product): array
    {
        return $product->pricing(
            $this->price !== null ? (float) $this->price : null,
            $this->compare_at_price !== null ? (float) $this->compare_at_price : null
        );
    }

    /** @return array<string, string> */
    public static function cleanAttributesArray(mixed $raw): array
    {
        $attrs = self::decodeAttributes($raw);
        $clean = [];

        foreach ($attrs as $key => $value) {
            $key = trim((string) $key);
            if (! self::isCustomerAttributeKey($key) || ! self::isCustomerAttributeValue($value)) {
                continue;
            }

            if (isset($clean[$key]) && strcasecmp($clean[$key], (string) $value) === 0) {
                continue;
            }

            $clean[$key] = (string) $value;
        }

        return $clean;
    }

    /** @return array<string, mixed> */
    private static function decodeAttributes(mixed $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        if (is_array($raw)) {
            return $raw;
        }

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private static function isCustomerAttributeKey(string $key): bool
    {
        if ($key === '' || ctype_digit($key)) {
            return false;
        }

        if (str_starts_with($key, '{') || str_starts_with($key, '[')) {
            return false;
        }

        return ! in_array(strtolower($key), self::INTERNAL_KEYS, true);
    }

    private static function isCustomerAttributeValue(mixed $value): bool
    {
        if ($value === null || is_array($value) || is_object($value)) {
            return false;
        }

        $string = trim((string) $value);
        if ($string === '') {
            return false;
        }

        if (str_starts_with($string, '{') || str_starts_with($string, '[')) {
            return false;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $string)) {
            return false;
        }

        return true;
    }

    private function rawAttributesPayload(): mixed
    {
        return $this->getAttributes()['attributes'] ?? null;
    }

    /** @param array<string, string> $map */
    private function mapHasValue(array $map, string $value): bool
    {
        foreach ($map as $existing) {
            if (strcasecmp($existing, $value) === 0) {
                return true;
            }
        }

        return false;
    }
}
