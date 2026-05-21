<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StockAlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockAlertController extends Controller
{
    public function store(Request $request, Product $product, StockAlertService $alerts): RedirectResponse
    {
        abort_unless($product->qc_status === 'approved', 404);

        $data = $request->validate([
            'variant_id' => ['nullable', 'exists:product_variants,id'],
            'email' => ['nullable', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $variantId = $data['variant_id'] ?? null;
        if ($variantId && ! $product->variants()->where('id', $variantId)->exists()) {
            return back()->withErrors(['variant_id' => 'Invalid variant.']);
        }

        if (! $alerts->isOutOfStock($product, $variantId)) {
            return back()->with('status', 'This item is already in stock — you can add it to cart.');
        }

        $email = $data['email'] ?? Auth::user()?->email;
        $phone = $data['phone'] ?? Auth::user()?->phone;

        if (! $email && ! $phone) {
            return back()->withErrors(['email' => 'Enter email or phone so we can notify you.']);
        }

        $alerts->subscribe($product, $variantId, $email, $phone, Auth::id());

        return back()->with('status', 'We will email/SMS you when this product is back in stock.');
    }
}
