<?php

namespace Tests\Feature;

use App\Models\HarvestBatch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KoordinatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_koordinator_routes(): void
    {
        $response = $this->get('/koordinator/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/koordinator/rencana-panen');
        $response->assertRedirect('/login');

        $response = $this->get('/koordinator/penimbangan');
        $response->assertRedirect('/login');

        $response = $this->get('/koordinator/stok');
        $response->assertRedirect('/login');

        $response = $this->get('/koordinator/surat-jalan');
        $response->assertRedirect('/login');

        $response = $this->get('/koordinator/monitoring');
        $response->assertRedirect('/login');
    }

    public function test_non_koordinator_cannot_access_koordinator_routes(): void
    {
        $klien = User::factory()->create([
            'role'      => 'KLIEN',
            'is_active' => true,
        ]);

        $response = $this->actingAs($klien)->get('/koordinator/dashboard');
        $response->assertStatus(403);
    }

    public function test_koordinator_can_access_all_modules(): void
    {
        $koordinator = User::factory()->create([
            'name'      => 'Agus Koordinator',
            'role'      => 'KOORDINATOR',
            'is_active' => true,
        ]);

        $client = User::factory()->create(['role' => 'KLIEN', 'name' => 'Klien Test']);
        $product = Product::create([
            'sku'           => 'GPA-VGT-TMT01',
            'name'          => 'Tomat Cherry Hidroponik',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 25000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        $order = Order::create([
            'order_number'         => 'ORD-GPA-202610-9999',
            'user_id'              => $client->id,
            'order_source'         => 'WEB_PORTAL',
            'status'               => 'TERVERIFIKASI',
            'target_delivery_date' => now()->addDay(),
            'delivery_address'     => 'Jl. Buah Batu No. 123, Bandung',
        ]);

        OrderItem::create([
            'order_id'          => $order->id,
            'product_id'        => $product->id,
            'ordered_qty'       => 100,
            'actual_net_weight' => 100,
            'unit_price'        => 25000,
            'subtotal'          => 2500000,
        ]);

        HarvestBatch::create([
            'product_id'         => $product->id,
            'source_type'        => 'PETANI_BINAAN',
            'supplier_name'      => 'Poktan Sukamaju',
            'batch_date'         => now()->toDateString(),
            'initial_quantity'   => 500,
            'available_quantity' => 400,
            'inputted_by'        => $koordinator->id,
        ]);

        // 1. Dashboard
        $response = $this->actingAs($koordinator)->get('/koordinator/dashboard');
        $response->assertStatus(200);

        // 2. Rencana Panen
        $response = $this->actingAs($koordinator)->get('/koordinator/rencana-panen');
        $response->assertStatus(200);

        // 3. Penimbangan
        $response = $this->actingAs($koordinator)->get('/koordinator/penimbangan');
        $response->assertStatus(200);

        // 4. Stok
        $response = $this->actingAs($koordinator)->get('/koordinator/stok');
        $response->assertStatus(200);

        // 5. Surat Jalan
        $response = $this->actingAs($koordinator)->get('/koordinator/surat-jalan');
        $response->assertStatus(200);

        // 6. Monitoring
        $response = $this->actingAs($koordinator)->get('/koordinator/monitoring');
        $response->assertStatus(200);
    }
}
