<?php

namespace Tests\Unit;

use App\Models\ProductVariant;
use Tests\TestCase;

class ProductVariantTest extends TestCase
{
    public function test_display_label_shows_only_customer_attributes(): void
    {
        $variant = new ProductVariant([
            'color' => 'GREEN',
            'size' => null,
            'price' => 50,
            'stock' => 10,
            'attributes' => [
                'Color' => 'GREEN',
                'Capacity' => '1',
                'id' => 3,
                'product_id' => 10,
                'created_at' => '2026-06-03 22:30:43',
                '{"Color":"GREEN"}' => 'ignored',
            ],
        ]);

        $this->assertSame('Capacity: 1 · Color: GREEN', $variant->displayLabel());
    }

    public function test_display_label_parses_json_string_attributes(): void
    {
        $variant = new ProductVariant;
        $variant->setRawAttributes([
            'attributes' => '{"Capacity":"1"}',
        ]);

        $this->assertSame('Capacity: 1', $variant->displayLabel());
    }

    public function test_display_label_falls_back_to_standard(): void
    {
        $variant = new ProductVariant([
            'attributes' => [
                'id' => 2,
                'price' => 50,
            ],
        ]);

        $this->assertSame('Standard', $variant->displayLabel());
    }
}
