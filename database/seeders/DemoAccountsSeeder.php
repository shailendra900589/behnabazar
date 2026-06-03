<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoAccountsSeeder extends Seeder
{
    /**
     * Demo logins for admin, vendor, QC, and customer (local + live after deploy).
     * Password is stored via the User model "hashed" cast — do not use Hash::make() here.
     */
    public static function definitions(): array
    {
        return [
            [
                'name' => 'Admin',
                'email' => 'admin@behnabazar.test',
                'password' => 'password',
                'role' => 'admin',
            ],
            [
                'name' => 'Demo Vendor',
                'email' => 'vendor@behnabazar.test',
                'password' => 'password',
                'role' => 'vendor',
                'shop_name' => 'Bloom Local Studio',
                'city' => 'Indore',
                'reg_fee_paid' => true,
            ],
            [
                'name' => 'QC Manager',
                'email' => 'qc@behnabazar.test',
                'password' => 'password',
                'role' => 'qc_manager',
            ],
            [
                'name' => 'Customer',
                'email' => 'user@behnabazar.test',
                'password' => 'password',
                'role' => 'user',
                'coins' => 120,
                'phone' => '9999999999',
                'address' => 'Demo Street, Local Market',
                'city' => 'Indore',
                'pincode' => '452001',
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::definitions() as $row) {
            $email = strtolower($row['email']);
            $password = $row['password'];
            unset($row['email'], $row['password']);

            $user = User::updateOrCreate(
                ['email' => $email],
                array_merge($row, [
                    'password' => $password,
                    'account_status' => 'active',
                    'is_email_verified' => true,
                    'email_verified_at' => now(),
                ])
            );

            if ($user->role === 'vendor' && empty($user->referral_code)) {
                $user->update(['referral_code' => strtoupper(Str::random(8))]);
            }
            if ($user->role === 'user' && empty($user->referral_code)) {
                $user->update(['referral_code' => strtoupper(Str::random(8))]);
            }
        }
    }
}
