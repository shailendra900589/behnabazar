<?php

namespace App\Http\Controllers;

use App\Support\MarketplaceSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function checkPincode(Request $request): JsonResponse
    {
        $pincode = preg_replace('/\D/', '', (string) $request->query('pincode', ''));

        if (strlen($pincode) !== 6) {
            return response()->json([
                'ok' => false,
                'message' => 'Enter a valid 6-digit PIN code.',
            ]);
        }

        $serviceable = MarketplaceSettings::isPincodeServiceable($pincode);
        $eta = now()->addDays(3)->format('D, d M Y');

        return response()->json([
            'ok' => $serviceable,
            'message' => $serviceable
                ? "Delivery available — estimated by {$eta}"
                : 'Sorry, we do not deliver to this PIN code yet.',
            'eta' => $serviceable ? $eta : null,
        ]);
    }
}
