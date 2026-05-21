<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\VendorResellInventory;
use App\Models\VendorWalletTransaction;
use App\Services\VendorNotificationService;
use App\Support\MarketplaceSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResellController extends Controller
{
    public function catalog(Request $request): View
    {
        $this->requireVendor();
        abort_unless(MarketplaceSettings::resellEnabled(), 404);

        $query = $this->availableSourcesQuery();

        if ($search = trim((string) $request->get('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhereHas('vendor', fn ($v) => $v->where('shop_name', 'like', '%'.$search.'%'));
            });
        }

        if ($categoryId = $request->integer('category')) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->latest()->paginate(12)->withQueryString();

        $myListingIds = Product::where('vendor_id', Auth::id())
            ->whereNotNull('source_product_id')
            ->pluck('source_product_id')
            ->all();

        $inventory = VendorResellInventory::where('vendor_id', Auth::id())
            ->where('qty_remaining', '>', 0)
            ->get()
            ->keyBy('source_product_id');

        return view('dashboards.resell-catalog', [
            'products' => $products,
            'customizeFee' => MarketplaceSettings::resellCustomizeFee(),
            'bulkMinQty' => MarketplaceSettings::resellBulkMinQty(),
            'bulkDiscountPercent' => MarketplaceSettings::resellBulkDiscountPercent(),
            'walletBalance' => (float) Auth::user()->sales_wallet_balance,
            'myListingIds' => $myListingIds,
            'inventory' => $inventory,
            'categories' => \App\Models\Category::orderBy('name')->get(),
            'catalogTotal' => $this->availableSourcesQuery()->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requireVendor();
        abort_unless(MarketplaceSettings::resellEnabled(), 404);

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

        $source = $this->availableSourcesQuery()->find($data['source_product_id']);
        if (! $source) {
            return back()->withErrors(['source_product_id' => 'This product is not available for resell.']);
        }

        if (Product::where('vendor_id', Auth::id())->where('source_product_id', $source->id)->exists()) {
            return back()->withErrors(['source_product_id' => 'You already have a resell listing for this product. Edit it from My products.']);
        }

        $sourceBase = (float) $source->price;
        if ((float) $data['sell_price'] < $sourceBase) {
            return back()->withErrors(['sell_price' => 'Selling price must be at least ₹'.number_format($sourceBase, 2).' (source vendor share per unit).']);
        }

        $fee = 0.0;
        if ($data['resell_mode'] === 'customized') {
            $fee = MarketplaceSettings::resellCustomizeFee();
            if ((float) Auth::user()->sales_wallet_balance < $fee) {
                return back()->withErrors(['resell_mode' => 'Insufficient sales wallet for branding fee (₹'.number_format($fee, 2).' required).']);
            }
        }

        $listing = null;
        DB::transaction(function () use ($request, $data, $source, $sourceBase, $fee, &$listing) {
            $title = $data['resell_mode'] === 'customized' && ! empty($data['title'])
                ? $data['title']
                : $source->title;

            $listing = Product::create([
                'vendor_id' => Auth::id(),
                'source_product_id' => $source->id,
                'resell_mode' => $data['resell_mode'],
                'source_base_price' => $sourceBase,
                'resell_listing_fee' => $fee,
                'resell_allowed' => false,
                'category_id' => $source->category_id,
                'title' => $title,
                'slug' => Str::slug($title).'-'.Str::random(5),
                'price' => $data['sell_price'],
                'description' => $data['description'] ?? $source->description,
                'qc_status' => 'pending',
            ]);

            if ($fee > 0) {
                Auth::user()->decrement('sales_wallet_balance', $fee);
                VendorWalletTransaction::create([
                    'vendor_id' => Auth::id(),
                    'amount' => -$fee,
                    'type' => 'resell_listing_fee',
                    'order_id' => null,
                    'description' => 'Branded resell listing fee — '.$source->title,
                ]);
            }

            $this->syncListingImages($request, $listing, $source, $data['resell_mode'] === 'customized');
        });

        if ($listing) {
            app(VendorNotificationService::class)->notifyResellListingCreated(
                $listing->fresh(),
                $source,
                Auth::user()
            );
        }

        return redirect()->route('dashboard')->with('status', 'Resell listing submitted for QC. The source vendor fulfills customer orders.');
    }

    public function bulkPurchase(Request $request): RedirectResponse
    {
        $this->requireVendor();
        abort_unless(MarketplaceSettings::resellEnabled(), 404);

        $data = $request->validate([
            'source_product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $minQty = MarketplaceSettings::resellBulkMinQty();
        if ((int) $data['quantity'] < $minQty) {
            return back()->withErrors(['quantity' => 'Minimum bulk quantity is '.$minQty.' units.']);
        }

        $source = $this->availableSourcesQuery()->find($data['source_product_id']);
        if (! $source) {
            return back()->withErrors(['source_product_id' => 'Product not available for bulk purchase.']);
        }

        $discount = MarketplaceSettings::resellBulkDiscountPercent() / 100;
        $unitBase = $source->effectiveResellerUnitCost();
        $unitCost = round($unitBase * (1 - $discount), 2);
        $total = round($unitCost * (int) $data['quantity'], 2);

        if ((float) Auth::user()->sales_wallet_balance < $total) {
            return back()->withErrors(['quantity' => 'Insufficient sales wallet. Need ₹'.number_format($total, 2).'.']);
        }

        $sourceVendorId = (int) $source->vendor_id;

        DB::transaction(function () use ($data, $source, $unitCost, $total, $sourceVendorId) {
            Auth::user()->decrement('sales_wallet_balance', $total);
            VendorWalletTransaction::create([
                'vendor_id' => Auth::id(),
                'amount' => -$total,
                'type' => 'bulk_stock_purchase',
                'order_id' => null,
                'description' => 'Bulk stock — '.$source->title.' × '.$data['quantity'],
            ]);

            if ($sourceVendorId) {
                \App\Models\User::where('id', $sourceVendorId)->increment('sales_wallet_balance', $total);
                VendorWalletTransaction::create([
                    'vendor_id' => $sourceVendorId,
                    'amount' => $total,
                    'type' => 'bulk_stock_sale',
                    'order_id' => null,
                    'description' => 'Bulk stock sold to vendor #'.Auth::id().' — '.$source->title,
                ]);
            }

            $inv = VendorResellInventory::firstOrNew([
                'vendor_id' => Auth::id(),
                'source_product_id' => $source->id,
            ]);
            $inv->unit_cost = $unitCost;
            $inv->qty_purchased = (int) $inv->qty_purchased + (int) $data['quantity'];
            $inv->qty_remaining = (int) $inv->qty_remaining + (int) $data['quantity'];
            $inv->save();
        });

        app(VendorNotificationService::class)->notifyResellBulkPurchase(
            $source,
            Auth::user(),
            (int) $data['quantity'],
            $total
        );

        return redirect()->route('manage.resell.catalog')->with('status', 'Bulk stock purchased. List on your shop with Quick or Branded resell.');
    }

    protected function availableSourcesQuery()
    {
        return Product::with(['category', 'vendor', 'images'])
            ->where('qc_status', 'approved')
            ->whereNotNull('vendor_id')
            ->where('vendor_id', '!=', Auth::id())
            ->whereNull('source_product_id')
            ->where(function ($q) {
                $q->where('resell_allowed', true)->orWhereNull('resell_allowed');
            });
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
        $user = Auth::user();
        $canManage = $user
            && $user->role === 'vendor'
            && ($user->account_status === 'active' || session()->has('impersonated_by'));

        abort_unless($canManage, 403);
    }
}
