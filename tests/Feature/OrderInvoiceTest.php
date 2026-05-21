<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_download_order_invoice_pdf(): void
    {
        $this->seed();

        $customer = User::where('role', 'user')->firstOrFail();
        $product = Product::where('qc_status', 'approved')->firstOrFail();

        $order = Order::create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'product_name' => $product->title,
            'quantity' => 1,
            'unit_price' => $product->price,
            'total_price' => $product->price,
            'customer_name' => $customer->name,
            'phone' => $customer->phone ?? '9876543210',
            'address' => 'Test delivery address',
            'payment_method' => 'cod',
            'status' => 'processing',
        ]);

        $response = $this->actingAs($customer)->get(route('orders.invoice', $order));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }

    public function test_other_customer_cannot_download_invoice(): void
    {
        $this->seed();

        $orders = Order::count();
        if ($orders < 1) {
            $this->markTestSkipped('No orders in seed data.');
        }

        $order = Order::firstOrFail();
        $other = User::create([
            'name' => 'Other User',
            'email' => 'other-invoice@test.local',
            'password' => bcrypt('password'),
            'role' => 'user',
            'is_email_verified' => true,
            'account_status' => 'active',
        ]);

        $this->actingAs($other)->get(route('orders.invoice', $order))->assertForbidden();
    }
}
