<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountAddressController extends Controller
{
    public function index(): View
    {
        return view('account.addresses', [
            'addresses' => Auth::user()->addresses()->orderByDesc('is_default')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'max:100'],
            'phone' => ['required', 'max:30'],
            'address' => ['required', 'max:1000'],
            'city' => ['nullable', 'max:100'],
            'pincode' => ['nullable', 'digits:6'],
            'is_default' => ['nullable'],
        ]);

        if ($request->boolean('is_default')) {
            Auth::user()->addresses()->update(['is_default' => false]);
        }

        Auth::user()->addresses()->create($data + [
            'is_default' => $request->boolean('is_default') || ! Auth::user()->addresses()->exists(),
        ]);

        return back()->with('status', 'Address saved.');
    }

    public function destroy(UserAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === Auth::id(), 403);
        $address->delete();

        return back()->with('status', 'Address removed.');
    }

    public function makeDefault(UserAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === Auth::id(), 403);
        Auth::user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('status', 'Default address updated.');
    }
}
