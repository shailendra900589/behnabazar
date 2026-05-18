<?php

namespace App\Support;

use App\Models\CartItem;
use App\Models\User;

final class MergeGuestCart
{
    public static function intoUser(?string $guestSessionId, User $user): void
    {
        if (! $guestSessionId) {
            return;
        }

        $guestItems = CartItem::query()
            ->where('session_id', $guestSessionId)
            ->whereNotNull('session_id')
            ->get();

        foreach ($guestItems as $guest) {
            $existing = CartItem::query()
                ->where('user_id', $user->id)
                ->where('product_id', $guest->product_id)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $guest->quantity);
                $guest->delete();
            } else {
                $guest->forceFill([
                    'user_id' => $user->id,
                    'session_id' => null,
                ])->save();
            }
        }
    }
}
