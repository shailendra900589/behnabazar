<?php

namespace App\Support;

class ProductVariantInput
{
    /** @return list<array{color: ?string, size: ?string, attributes: ?array, price: mixed, compare_at_price: mixed, stock: int}> */
    public static function normalizeRows(?array $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $normalized = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $attributes = self::parseAttributes($row);

            $price = isset($row['price']) && $row['price'] !== '' ? (float) $row['price'] : null;
            $compareAt = isset($row['compare_at_price']) && $row['compare_at_price'] !== '' ? (float) $row['compare_at_price'] : null;
            $stock = isset($row['stock']) && $row['stock'] !== '' ? (int) $row['stock'] : 0;

            if ($attributes === [] && $price === null && $stock === 0) {
                continue;
            }

            $color = $attributes['Color'] ?? (isset($row['color']) && $row['color'] !== '' ? trim((string) $row['color']) : null);
            $size = $attributes['Size'] ?? (isset($row['size']) && $row['size'] !== '' ? trim((string) $row['size']) : null);

            $normalized[] = [
                'color' => $color,
                'size' => $size,
                'attributes' => $attributes !== [] ? $attributes : null,
                'price' => $price,
                'compare_at_price' => $compareAt,
                'stock' => max(0, $stock),
            ];
        }

        return $normalized;
    }

    /** @return array<string, string> */
    private static function parseAttributes(array $row): array
    {
        $attributes = [];

        if (! empty($row['attribute_keys']) && is_array($row['attribute_keys'])) {
            $values = $row['attribute_values'] ?? [];
            foreach ($row['attribute_keys'] as $index => $key) {
                $key = trim((string) $key);
                $value = trim((string) ($values[$index] ?? ''));
                if ($key !== '' && $value !== '') {
                    $attributes[$key] = $value;
                }
            }
        }

        foreach (config('product.variant_attribute_types', []) as $type) {
            $field = self::fieldName($type);
            if (! empty($row[$field])) {
                $attributes[$type] = trim((string) $row[$field]);
            }
        }

        if (! empty($row['color']) && ! isset($attributes['Color'])) {
            $attributes['Color'] = trim((string) $row['color']);
        }
        if (! empty($row['size']) && ! isset($attributes['Size'])) {
            $attributes['Size'] = trim((string) $row['size']);
        }

        return $attributes;
    }

    public static function fieldName(string $type): string
    {
        return str_replace([' ', '-'], '_', strtolower($type));
    }
}
