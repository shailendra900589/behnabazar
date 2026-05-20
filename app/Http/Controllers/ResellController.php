<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResellController extends Controller
{
    public function catalog(): View
    {
        $this->requireVendor();

        $products = Product::with(['category', 'vendor', 'images'])
            ->where('qc_status', 'approved')
            ->whereNotNull('vendor_id')
            ->where('vendor_id', '!=', Auth::id())
            ->whereNull('source_product_id')
            ->latest()
            ->paginate(12);

        return view('dashboards.resell-catalog', [
            'products' => $products,
            'customizeFee' => (float) Setting::value('resell_customize_fee', 99),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requireVendor();

        $data = $request->validate([
            'source_product_id' => ['required', 'exists:products,id'],
            'resell_mode' => ['required', 'in:direct,customized'],
            'sell_price' => ['required', 'numeric', 'min:1'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'image' => ['nullable', 'image', 'max:4096'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'max:4096'],
        ]);

        $source = Product::with('images')->where('qc_status', 'approved')
            ->whereNull('source_product_id')
            ->findOrFail($data['source_product_id']);

        if ((int) $source->vendor_id === (int) Auth::id()) {
            return back()->withErrors(['source_product_id' => 'You cannot resell your own product.']);
        }

        $sourceBase = (float) $source->price;
        if ((float) $data['sell_price'] < $sourceBase) {
            return back()->withErrors(['sell_price' => 'Selling price must be at least the source vendor price (₹'.number_format($sourceBase, 2).').']);
        }

        $fee = 0.0;
        if ($data['resell_mode'] === 'customized') {
            $fee = (float) Setting::value('resell_customize_fee', 99);
            if ((float) Auth::user()->sales_wallet_balance < $fee) {
                return back()->withErrors(['resell_mode' => 'Insufficient sales wallet for customize fee (₹'.number_format($fee, 2).' required).']);
            }
        }

        DB::transaction(function () use ($request, $data, $source, $sourceBase, $fee) {
            $title = $data['resell_mode'] === 'customized' && ! empty($data['title'])
                ? $data['title']
                : $source->title.' (Resell)';

            $listing = Product::create([
                'vendor_id' => Auth::id(),
                'source_product_id' => $source->id,
                'resell_mode' => $data['resell_mode'],
                'source_base_price' => $sourceBase,
                'resell_listing_fee' => $fee,
                'category_id' => $source->category_id,
                'title' => $title,
                'slug' => Str::slug($title).'-'.Str::random(5),
                'price' => $data['sell_price'],
                'description' => $data['description'] ?? $source->description,
                'qc_status' => 'pending',
            ]);

            if ($fee > 0) {
                Auth::user()->decrement('sales_wallet_balance', $fee);
            }

            $this->syncListingImages($request, $listing, $source, $data['resell_mode'] === 'customized');
        });

        return redirect()->route('dashboard')->with('status', 'Resell listing submitted for QC. Source vendor will fulfill orders.');
    }

    protected function syncListingImages(Request $request, Product $listing, Product $source, bool $customized): void
    {
        $sort = 0;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $listing->update(['image' => $path]);
            ProductImage::create(['product_id' => $listing->id, 'path' => $path, 'sort_order' => $sort++]);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('products', 'public');
                ProductImage::create(['product_id' => $listing->id, 'path' => $path, 'sort_order' => $sort++]);
            }
        }

        if ($sort === 0) {
            foreach ($source->images as $img) {
                ProductImage::create(['product_id' => $listing->id, 'path' => $img->path, 'sort_order' => $sort++]);
            }
            foreach (['image', 'image2', 'image3', 'image4'] as $col) {
                if ($source->{$col} && ! $source->images->contains('path', $source->{$col})) {
                    ProductImage::create(['product_id' => $listing->id, 'path' => $source->{$col}, 'sort_order' => $sort++]);
                }
            }
            $first = $listing->images()->orderBy('sort_order')->first();
            if ($first) {
                $listing->update(['image' => $first->path]);
            }
        }
    }

    protected function requireVendor(): void
    {
        abort_unless(Auth::check() && Auth::user()->role === 'vendor' && Auth::user()->account_status === 'active', 403);
    }
}
