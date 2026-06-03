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

        if ($request->expectsJson()) {
            return response()->json(array_merge($this->cartJsonPayload('Item removed.'), [
                'operation' => 'removed',
            ]));
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
        return CartItem::with(['product.images', 'variant'])
            ->when(Auth::check(), fn ($q) => $q->where('user_id', Auth::id()), fn ($q) => $q->where('session_id', session()->getId()))
            ->latest()
            ->get();
    }

    private function authorizeCartItem(CartItem $item): void
    {
        abort_unless((Auth::check() && $item->user_id === Auth::id()) || (! Auth::check() && $item->session_id === session()->getId()), 403);
    }

    public function preview(Request $request): JsonResponse
    {
        return response()->json($this->cartJsonPayload(''));
    }

    private function response(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json($this->cartJsonPayload($message));
        }

        return back()->with('status', $message);
    }

    private function cartJsonPayload(string $message): array
    {
        $items = $this->items();
        $count = $items->sum('quantity');
        $total = $items->sum(fn ($item) => $item->quantity * ($item->variant ? ($item->variant->price ?? $item->product->price) : $item->product->price));

        $preview = $items->take(3);

        $payload = [
            'status' => 'success',
            'cart_count' => $count,
            'cart_total' => number_format($total, 2),
            'cart_more' => max(0, $count - 3),
            'cart_items' => $preview->map(function ($item) {
                $unit = $item->variant ? ($item->variant->price ?? $item->product->price) : $item->product->price;

                return [
                    'title' => $item->product->title,
                    'image' => $item->product->imageUrl(),
                    'quantity' => $item->quantity,
                    'unit_price' => number_format((float) $unit, 2),
                ];
            })->values()->all(),
            'cart_dropdown_html' => view('partials.cart-dropdown-menu', [
                'cartItemsPreview' => $preview,
                'cartCount' => $count,
            ])->render(),
        ];

        if ($message !== '') {
            $payload['message'] = $message;
        }

        return $payload;
    }
}
