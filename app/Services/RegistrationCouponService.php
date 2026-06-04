<?php

namespace App\Services;

use App\Models\RegistrationCoupon;
use App\Models\RegistrationCouponHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrationCouponService
{
    public function create(User $admin, array $data): RegistrationCoupon
    {
        $code = $this->normalizeCode($data['code'] ?? '') ?: $this->generateUniqueCode();

        if (RegistrationCoupon::where('code', $code)->exists()) {
            throw ValidationException::withMessages([
                'code' => 'This coupon code already exists. Choose another code.',
            ]);
        }

        return DB::transaction(function () use ($admin, $code) {
            $coupon = RegistrationCoupon::create([
                'code' => $code,
                'created_by' => $admin->id,
            ]);

            $this->log($coupon, 'created', $admin, [
                'notes' => 'Coupon code created',
            ]);

            return $coupon;
        });
    }

    public function redeem(string $rawCode, User $vendor): RegistrationCoupon
    {
        $code = $this->normalizeCode($rawCode);

        if ($code === '') {
            throw ValidationException::withMessages([
                'registration_coupon_code' => 'Enter a valid registration coupon code.',
            ]);
        }

        return DB::transaction(function () use ($code, $vendor) {
            /** @var RegistrationCoupon|null $coupon */
            $coupon = RegistrationCoupon::where('code', $code)->lockForUpdate()->first();

            if (! $coupon) {
                throw ValidationException::withMessages([
                    'registration_coupon_code' => 'Invalid registration coupon code.',
                ]);
            }

            if ($coupon->used_at) {
                throw ValidationException::withMessages([
                    'registration_coupon_code' => 'This coupon was already used and cannot be reused.',
                ]);
            }

            if ($coupon->revoked_at) {
                throw ValidationException::withMessages([
                    'registration_coupon_code' => 'This coupon is no longer valid.',
                ]);
            }

            $userDetails = $this->userDetailsNote($vendor);

            $coupon->update([
                'issued_to_name' => $vendor->name,
                'issued_to_email' => $vendor->email,
                'issued_to_phone' => $vendor->phone,
                'notes' => $userDetails,
                'used_by_user_id' => $vendor->id,
                'used_at' => now(),
            ]);

            $this->log($coupon, 'used', $vendor, [
                'subject_name' => $vendor->name,
                'subject_email' => $vendor->email,
                'notes' => $userDetails,
            ]);

            return $coupon->fresh(['usedBy']);
        });
    }

    public function revoke(User $admin, RegistrationCoupon $coupon): void
    {
        if ($coupon->used_at) {
            throw ValidationException::withMessages([
                'coupon' => 'Used coupons cannot be revoked.',
            ]);
        }

        if ($coupon->revoked_at) {
            return;
        }

        DB::transaction(function () use ($admin, $coupon) {
            $coupon->update(['revoked_at' => now()]);

            $this->log($coupon, 'revoked', $admin, [
                'notes' => 'Revoked by admin',
            ]);
        });
    }

    private function userDetailsNote(User $vendor): string
    {
        $parts = array_filter([
            $vendor->shop_name ? 'Shop: '.$vendor->shop_name : null,
            $vendor->city ? 'City: '.$vendor->city : null,
            $vendor->phone ? 'Phone: '.$vendor->phone : null,
            $vendor->email ? 'Email: '.$vendor->email : null,
        ]);

        return implode(' | ', $parts);
    }

    private function log(
        RegistrationCoupon $coupon,
        string $action,
        ?User $performer,
        array $extra = []
    ): void {
        RegistrationCouponHistory::create([
            'registration_coupon_id' => $coupon->id,
            'action' => $action,
            'performed_by_user_id' => $performer?->id,
            'subject_name' => $extra['subject_name'] ?? null,
            'subject_email' => $extra['subject_email'] ?? null,
            'notes' => $extra['notes'] ?? null,
        ]);
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = 'REG'.strtoupper(Str::random(8));
        } while (RegistrationCoupon::where('code', $code)->exists());

        return $code;
    }

    private function normalizeCode(?string $code): string
    {
        return strtoupper(trim((string) $code));
    }
}
