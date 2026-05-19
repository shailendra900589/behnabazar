<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Behna Bazar');
    }

    public function test_support_info_pages_are_accessible(): void
    {
        $this->get(route('local-delivery'))
            ->assertOk()
            ->assertSee('Local Delivery');

        $this->get(route('returns-policy'))
            ->assertOk()
            ->assertSee('Returns & Refunds');
    }

    public function test_a_customer_can_add_a_product_to_the_cart(): void
    {
        $this->seed();

        $product = Product::where('qc_status', 'approved')->firstOrFail();

        $response = $this->postJson(route('cart.add', $product), [
            'quantity' => 2,
        ]);

        $response->assertOk();
        $response->assertJsonPath('cart_count', 2);
    }

    public function test_role_users_can_reach_their_dashboard(): void
    {
        $this->seed();

        foreach (['admin', 'vendor', 'qc_manager', 'user'] as $role) {
            $user = User::where('role', $role)->firstOrFail();

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertOk();
        }
    }

    public function test_admin_dashboard_accepts_section_query(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('dashboard', ['section' => 'products']))
            ->assertOk()
            ->assertSee('Product');

        $this->actingAs($admin)
            ->get(route('dashboard', ['section' => 'invalid']))
            ->assertOk()
            ->assertSee('Live snapshot');
    }
}
