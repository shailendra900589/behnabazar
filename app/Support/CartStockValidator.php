<?php

namespace App\Support;

use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class CartStockValidator
{
    /** @return array{ok: bool, message: ?string} */
    public static function validateItem(CartItem $item, int $requestedQty): array
    {
        if ($item->variant_id) {
            $variant = $item->variant ?? ProductVariant::find($item->variant_id);
            if ($variant && $variant->stock > 0 && $requestedQty > $variant->stock) {
                return [
                    'ok' => false,
                    'message' => "Only {$variant->stock} left in stock for this variant.",
                ];
            }
            if ($variant && $variant->stock <= 0) {
                return ['ok' => false, 'message' => 'This variant is out of stock.'];
            }
        }

        return ['ok' => true, 'message' => null];
    }

    /** @param  Collection<int, CartItem>  $items */
    public static function validateCart(Collection $items): ?string
    {
        foreach ($items as $item) {
            $check = self::validateItem($item, (int) $item->quantity);
            if (! $check['ok']) {
                return $item->product->title.': '.$check['message'];
            }
        }

        return null;
    }
}
