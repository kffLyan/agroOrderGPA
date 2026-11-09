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

        $response = $this->get('/armada/bukti-terima');
        $response->assertRedirect('/login');

        $response = $this->get('/armada/status-kendaraan');
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

        // 3. Bukti Terima (PoD)
        $response = $this->actingAs($armada)->get('/armada/bukti-terima');
        $response->assertStatus(200);

        // 4. Status Kendaraan
        $response = $this->actingAs($armada)->get('/armada/status-kendaraan');
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

        $response = $this->actingAs($armada)->post("/armada/bukti-terima/{$order->id}/selesai", [
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

    public function test_driver_only_sees_their_own_assigned_orders_in_queue(): void
    {
        $driverJoko = User::factory()->create([
            'name'      => 'Joko Driver',
            'role'      => 'ARMADA',
            'is_active' => true,
        ]);

        $driverBudi = User::factory()->create([
            'name'      => 'Budi Driver',
            'role'      => 'ARMADA',
            'is_active' => true,
        ]);

        $client = User::factory()->create(['role' => 'KLIEN', 'name' => 'Klien Mitra']);

        $orderJoko = Order::create([
            'order_number'         => 'ORD-JOKO-001',
            'user_id'              => $client->id,
            'driver_id'            => $driverJoko->id,
            'order_source'         => 'WEB_PORTAL',
            'status'               => 'SIAP_KIRIM',
            'target_delivery_date' => now()->addDay(),
            'vehicle_plate_number' => 'B 9014 KPA',
            'surat_jalan_number'   => 'SJ-20261103-0001',
            'delivery_address'     => 'Restoran Pasundan',
            'grand_total'          => 2000000,
        ]);

        // 1. Login sebagai Joko -> Harus melihat pesanan miliknya
        $responseJoko = $this->actingAs($driverJoko)->get('/armada/tugas');
        $responseJoko->assertStatus(200);
        $responseJoko->assertSee('SJ-20261103-0001');
        $responseJoko->assertSee('Restoran Pasundan');

        // 2. Login sebagai Budi -> TIDAK BOLEH melihat pesanan milik Joko
        $responseBudi = $this->actingAs($driverBudi)->get('/armada/tugas');
        $responseBudi->assertStatus(200);
        $responseBudi->assertDontSee('SJ-20261103-0001');
        $responseBudi->assertDontSee('Restoran Pasundan');
        $responseBudi->assertSee('Belum Ada Tugas Pengantaran');

        // 3. Budi mencoba mengakses surat jalan Joko via URL parameter -> Harus 403 Ditolak
        $responseBudiSj = $this->actingAs($driverBudi)->get('/armada/surat-jalan?order_id=' . $orderJoko->id);
        $responseBudiSj->assertStatus(403);

        // 4. Budi mencoba mengakses halaman PoD Joko via URL parameter -> Harus 403 Ditolak
        $responseBudiPod = $this->actingAs($driverBudi)->get('/armada/bukti-terima?order_id=' . $orderJoko->id);
        $responseBudiPod->assertStatus(403);
    }

    public function test_driver_can_confirm_plate_and_start_route(): void
    {
        $driver = User::factory()->create([
            'role'      => 'ARMADA',
            'is_active' => true,
        ]);

        $client = User::factory()->create(['role' => 'KLIEN', 'name' => 'Klien Mitra']);

        $order = Order::create([
            'order_number'         => 'ORD-START-001',
            'user_id'              => $client->id,
            'driver_id'            => $driver->id,
            'order_source'         => 'WEB_PORTAL',
            'status'               => 'SIAP_KIRIM',
            'target_delivery_date' => now()->addDay(),
            'vehicle_plate_number' => 'B 9014 KPA',
            'surat_jalan_number'   => 'SJ-20261103-0002',
            'delivery_address'     => 'Restoran Pasundan',
            'grand_total'          => 2000000,
        ]);

        $response = $this->actingAs($driver)->post("/armada/surat-jalan/{$order->id}/mulai", [
            'vehicle_plate_number' => 'D 8192 YZ',
        ]);

        $response->assertRedirect("/armada/surat-jalan?order_id={$order->id}");
        $order->refresh();
        $this->assertEquals('DALAM_PENGIRIMAN', $order->status);
        $this->assertEquals('D 8192 YZ', $order->vehicle_plate_number);
        $this->assertNotNull($order->departure_time);
    }

    public function test_driver_can_update_plate_from_surat_jalan(): void
    {
        $driver = User::factory()->create([
            'role'      => 'ARMADA',
            'is_active' => true,
        ]);

        $client = User::factory()->create(['role' => 'KLIEN', 'name' => 'Klien Mitra']);

        $order = Order::create([
            'order_number'         => 'ORD-PLATE-001',
            'user_id'              => $client->id,
            'driver_id'            => $driver->id,
            'order_source'         => 'WEB_PORTAL',
            'status'               => 'SIAP_KIRIM',
            'target_delivery_date' => now()->addDay(),
            'vehicle_plate_number' => 'B 9014 KPA',
            'surat_jalan_number'   => 'SJ-20261103-0003',
            'delivery_address'     => 'Restoran Pasundan',
            'grand_total'          => 2000000,
        ]);

        $response = $this->actingAs($driver)->post("/armada/surat-jalan/{$order->id}/plat", [
            'vehicle_plate_number' => 'D 8421 AB',
        ]);

        $response->assertRedirect();
        $order->refresh();
        $this->assertEquals('D 8421 AB', $order->vehicle_plate_number);
    }

    public function test_driver_can_update_plate_from_status_kendaraan(): void
    {
        $driver = User::factory()->create([
            'role'      => 'ARMADA',
            'is_active' => true,
        ]);

        $client = User::factory()->create(['role' => 'KLIEN', 'name' => 'Klien Mitra']);

        $order = Order::create([
            'order_number'         => 'ORD-FLEET-001',
            'user_id'              => $client->id,
            'driver_id'            => $driver->id,
            'order_source'         => 'WEB_PORTAL',
            'status'               => 'SIAP_KIRIM',
            'target_delivery_date' => now()->addDay(),
            'vehicle_plate_number' => 'B 9014 KPA',
            'surat_jalan_number'   => 'SJ-20261103-0004',
            'delivery_address'     => 'Restoran Pasundan',
            'grand_total'          => 2000000,
        ]);

        $response = $this->actingAs($driver)->post('/armada/status-kendaraan/plat', [
            'vehicle_plate_number' => 'D 8192 YZ',
        ]);

        $response->assertRedirect();
        $order->refresh();
        $this->assertEquals('D 8192 YZ', $order->vehicle_plate_number);
    }
}
