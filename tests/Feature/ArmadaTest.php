<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArmadaTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_armada_routes(): void
    {
        $response = $this->get('/armada/tugas');
        $response->assertRedirect('/login');

        $response = $this->get('/armada/surat-jalan');
        $response->assertRedirect('/login');

        $response = $this->get('/armada/scan-pod');
        $response->assertRedirect('/login');

        $response = $this->get('/armada/status');
        $response->assertRedirect('/login');
    }

    public function test_non_armada_cannot_access_armada_routes(): void
    {
        $klien = User::factory()->create([
            'role'      => 'KLIEN',
            'is_active' => true,
        ]);

        $response = $this->actingAs($klien)->get('/armada/tugas');
        $response->assertStatus(403);
    }

    public function test_armada_can_access_all_modules(): void
    {
        $armada = User::factory()->create([
            'name'      => 'Asep Driver',
            'role'      => 'ARMADA',
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
            'driver_id'            => $armada->id,
            'order_source'         => 'WEB_PORTAL',
            'status'               => 'DALAM_PENGIRIMAN',
            'target_delivery_date' => now()->addDay(),
            'vehicle_plate_number' => 'D 1234 ABC',
            'surat_jalan_number'   => 'SJ-20261021-0001',
            'delivery_address'     => 'Jl. Buah Batu No. 123, Bandung',
            'grand_total'          => 2500000,
        ]);

        OrderItem::create([
            'order_id'          => $order->id,
            'product_id'        => $product->id,
            'ordered_qty'       => 100,
            'unit_price'        => 25000,
            'subtotal'          => 2500000,
        ]);

        // 1. Tugas
        $response = $this->actingAs($armada)->get('/armada/tugas');
        $response->assertStatus(200);

        // 2. Surat Jalan
        $response = $this->actingAs($armada)->get('/armada/surat-jalan');
        $response->assertStatus(200);

        // 3. Scan PoD
        $response = $this->actingAs($armada)->get('/armada/scan-pod');
        $response->assertStatus(200);

        // 4. Status
        $response = $this->actingAs($armada)->get('/armada/status');
        $response->assertStatus(200);
    }

    public function test_armada_can_submit_pod_without_digital_signature(): void
    {
        $armada = User::factory()->create([
            'name'      => 'Asep Driver',
            'role'      => 'ARMADA',
            'is_active' => true,
        ]);

        $client = User::factory()->create(['role' => 'KLIEN', 'name' => 'Klien Test']);

        $order = Order::create([
            'order_number'         => 'ORD-GPA-202610-8888',
            'user_id'              => $client->id,
            'driver_id'            => $armada->id,
            'order_source'         => 'WEB_PORTAL',
            'status'               => 'DALAM_PENGIRIMAN',
            'target_delivery_date' => now()->addDay(),
            'vehicle_plate_number' => 'D 1234 ABC',
            'surat_jalan_number'   => 'SJ-20261021-0002',
            'delivery_address'     => 'Jl. Riau No. 45, Bandung',
            'grand_total'          => 1500000,
        ]);

        $response = $this->actingAs($armada)->post("/armada/scan-pod/{$order->id}/selesai", [
            'condition'        => 'clear',
            'received_by_name' => 'Muha',
        ]);

        $response->assertRedirect('/armada/tugas');
        $this->assertDatabaseHas('orders', [
            'id'               => $order->id,
            'status'           => 'SELESAI',
            'received_by_name' => 'Muha',
        ]);
    }
}
