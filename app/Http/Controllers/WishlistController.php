<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(): View
    {
        return view('store.wishlist', [
            'items' => Auth::user()->wishlistItems()->with('product')->latest()->get(),
        ]);
    }

    public function toggle(Request $request, Product $product): JsonResponse
    {
        $item = WishlistItem::where('user_id', Auth::id())->where('product_id', $product->id)->first();

        if ($item) {
            $item->delete();
            $operation = 'removed';
        } else {
            WishlistItem::create(['user_id' => Auth::id(), 'product_id' => $product->id]);
            $operation = 'added';
        }

        return response()->json([
            'status' => 'success',
            'operation' => $operation,
            'wishlist_count' => Auth::user()->wishlistItems()->count(),
        ]);
    }
}
