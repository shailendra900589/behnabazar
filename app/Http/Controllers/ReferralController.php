<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ReferralReward;
use App\Services\ReferralProgramService;
use App\Support\ReferralSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    public function recordShare(Request $request, Product $product): JsonResponse
    {
        abort_unless(Auth::check(), 401);

        $data = $request->validate([
            'channel' => ['nullable', 'string', 'max:40'],
        ]);

        $service = app(ReferralProgramService::class);
        $user = Auth::user();
        $code = $service->ensureReferralCode($user);
        $baseUrl = route('product.show', $product);
        $url = ReferralSettings::enabled() ? $baseUrl.'?ref='.$code : $baseUrl;

        $shareToken = null;
        $message = 'Link ready to share.';

        if (ReferralSettings::enabled()) {
            $share = $service->recordShare($user, $product->id, $data['channel'] ?? 'share');
            $shareToken = $share->share_token;
            $message = 'Share recorded. Referral rewards unlock when your friend completes their first qualifying purchase.';
        }

        return response()->json([
            'message' => $message,
            'share_url' => $url,
            'url' => $url,
            'title' => $product->title,
            'text' => 'Check out '.$product->title.' on Behna Bazar!',
            'share_token' => $shareToken,
            'referral_enabled' => ReferralSettings::enabled(),
        ]);
    }

    public function sharePayload(Product $product): JsonResponse
    {
        $user = Auth::user();
        $service = app(ReferralProgramService::class);
        $code = $user ? $service->ensureReferralCode($user) : null;
        $baseUrl = route('product.show', $product);
        $url = ($code && ReferralSettings::enabled()) ? $baseUrl.'?ref='.$code : $baseUrl;
        $text = 'Check out '.$product->title.' on Behna Bazar!';

        return response()->json([
            'url' => $url,
            'title' => $product->title,
            'text' => $text,
            'referral_enabled' => ReferralSettings::enabled(),
            'links' => [
                'whatsapp' => 'https://api.whatsapp.com/send?text='.urlencode($text.' '.$url),
                'facebook' => 'https://www.facebook.com/sharer/sharer.php?u='.urlencode($url),
                'twitter' => 'https://twitter.com/intent/tweet?text='.urlencode($text).'&url='.urlencode($url),
                'telegram' => 'https://t.me/share/url?url='.urlencode($url).'&text='.urlencode($text),
                'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url='.urlencode($url),
                'email' => 'mailto:?subject='.rawurlencode($product->title).'&body='.urlencode($text."\n\n".$url),
                'sms' => 'sms:?body='.urlencode($text.' '.$url),
            ],
        ]);
    }

    public function myRewards(): JsonResponse
    {
        $rewards = ReferralReward::where('referrer_id', Auth::id())
            ->with(['referee', 'order'])
            ->latest()
            ->take(50)
            ->get();

        return response()->json(['rewards' => $rewards]);
    }
}
