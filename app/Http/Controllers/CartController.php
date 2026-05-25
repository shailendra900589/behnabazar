<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Support\CartStockValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $items = $this->items();
        $total = $items->sum(fn ($item) => $item->quantity * ($item->variant ? ($item->variant->price ?? $item->product->price) : $item->product->price));

        return view('store.cart', [
            'items' => $items,
            'total' => $total,
            'freeShippingThreshold' => \App\Support\MarketplaceSettings::freeShippingThreshold(),
        ]);
    }

    public function add(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $qty = max(1, min(20, (int) $request->input('quantity', 1)));
        $variantId = $request->input('variant_id');
        
        $lookup = Auth::check() ? ['user_id' => Auth::id(), 'product_id' => $product->id, 'variant_id' => $variantId] : ['session_id' => session()->getId(), 'product_id' => $product->id, 'variant_id' => $variantId];
        $item = CartItem::firstOrCreate($lookup, ['quantity' => 0]);
        $newQty = $item->quantity + $qty;
        $item->load(['variant', 'product']);
        $stockCheck = CartStockValidator::validateItem($item, $newQty);
        if (! $stockCheck['ok']) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $stockCheck['message']], 422);
            }

            return back()->withErrors(['cart' => $stockCheck['message']]);
        }
        $item->update(['quantity' => $newQty]);

        if ($request->has('buy_now')) {
            return redirect()->route('checkout');
        }

        return $this->response($request, 'Added to cart.');
    }

    public function update(Request $request, CartItem $item): JsonResponse|RedirectResponse
    {
        $this->authorizeCartItem($item);
        $qty = max(1, min(20, (int) $request->input('quantity', 1)));
        $item->load(['variant', 'product']);
        $stockCheck = CartStockValidator::validateItem($item, $qty);
        if (! $stockCheck['ok']) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $stockCheck['message']], 422);
            }

            return back()->withErrors(['cart' => $stockCheck['message']]);
        }
        $item->update(['quantity' => $qty]);

        return $this->response($request, 'Cart updated.');
    }

    public function remove(Request $request, CartItem $item): JsonResponse|RedirectResponse
    {
        $this->authorizeCartItem($item);
        $item->delete();

        $items = $this->items();
        $count = $items->sum('quantity');
        $total = $items->sum(fn ($i) => $i->quantity * ($i->variant ? ($i->variant->price ?? $i->product->price) : $i->product->price));

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Item removed.',
                'operation' => 'removed',
                'cart_count' => $count,
                'cart_total' => number_format($total, 2),
            ]);
        }

        return back()->with('status', 'Item removed.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $this->items()->each->delete();
        return back()->with('status', 'Cart cleared.');
    }

    public function items()
    {
        return CartItem::with(['product', 'variant'])
            ->when(Auth::check(), fn ($q) => $q->where('user_id', Auth::id()), fn ($q) => $q->where('session_id', session()->getId()))
            ->latest()
            ->get();
    }

    private function authorizeCartItem(CartItem $item): void
    {
        abort_unless((Auth::check() && $item->user_id === Auth::id()) || (! Auth::check() && $item->session_id === session()->getId()), 403);
    }

    private function response(Request $request, string $message): JsonResponse|RedirectResponse
    {
        $items = $this->items();
        $count = $items->sum('quantity');
        $total = $items->sum(fn ($item) => $item->quantity * ($item->variant ? ($item->variant->price ?? $item->product->price) : $item->product->price));

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => $message, 'cart_count' => $count, 'cart_total' => number_format($total, 2)]);
        }

        return back()->with('status', $message);
    }
}
