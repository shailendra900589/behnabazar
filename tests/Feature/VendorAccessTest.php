<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_approval_vendor_cannot_request_payout(): void
    {
        $vendor = User::factory()->vendor()->create([
            'account_status' => 'pending_approval',
            'reg_fee_paid' => true,
        ]);

        $this->actingAs($vendor)
            ->post(route('manage.payouts.request'), [
                'amount' => 500,
                'bank_details' => 'Test bank',
            ])
            ->assertRedirect(route('dashboard', ['section' => 'overview']));
    }

    public function test_active_vendor_can_open_dashboard(): void
    {
        $vendor = User::factory()->vendor()->create();

        $this->actingAs($vendor)
            ->get(route('dashboard', ['section' => 'overview']))
            ->assertOk();
    }
}
