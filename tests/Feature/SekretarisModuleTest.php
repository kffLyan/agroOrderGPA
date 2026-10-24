<?php

namespace Tests\Feature;

use App\Models\HarvestBatch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SekretarisModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_sekretaris_routes(): void
    {
        $response = $this->get('/sekretaris/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/sekretaris/verifikasi');
        $response->assertRedirect('/login');
    }

    public function test_non_sekretaris_cannot_access_sekretaris_routes(): void
    {
        $klien = User::factory()->create([
            'role'      => 'KLIEN',
            'is_active' => true,
        ]);

        $response = $this->actingAs($klien)->get('/sekretaris/dashboard');
        $response->assertStatus(403);

        $response = $this->actingAs($klien)->get('/sekretaris/verifikasi');
        $response->assertStatus(403);
    }

    public function test_sekretaris_can_access_all_eight_modules(): void
    {
        $sekretaris = User::factory()->create([
            'name'      => 'Siti Sekretaris Operasional',
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        // 1. Dashboard
        $response = $this->actingAs($sekretaris)->get('/sekretaris/dashboard');
        $response->assertStatus(200);

        // 2. Verifikasi Pesanan
        $response = $this->actingAs($sekretaris)->get('/sekretaris/verifikasi');
        $response->assertStatus(200);

        // 3. Stok & Buffer Panen Gudang
        $response = $this->actingAs($sekretaris)->get('/sekretaris/stok');
        $response->assertStatus(200);

        // 4. Surat Jalan & Dispatch
        $response = $this->actingAs($sekretaris)->get('/sekretaris/surat-jalan');
        $response->assertStatus(200);

        // 5. Faktur Konsolidasi
        $response = $this->actingAs($sekretaris)->get('/sekretaris/faktur');
        $response->assertStatus(200);

        // 6. Verifikasi Pembayaran & Rekening Koran
        $response = $this->actingAs($sekretaris)->get('/sekretaris/pembayaran');
        $response->assertStatus(200);

        // 7. Rekap Laporan & Jurnal Harian
        $response = $this->actingAs($sekretaris)->get('/sekretaris/laporan');
        $response->assertStatus(200);

        // 8. Input Pesanan Manual Telepon / WA
        $response = $this->actingAs($sekretaris)->get('/sekretaris/pesanan-manual');
        $response->assertStatus(200);

        // 9. Monitoring PoD (Proof of Delivery / Serah Terima Armada)
        $response = $this->actingAs($sekretaris)->get('/sekretaris/monitoring-pod');
        $response->assertStatus(200);
    }

    public function test_sekretaris_can_view_real_orders_in_verification_queue(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $klien = User::factory()->create([
            'role'         => 'KLIEN',
            'client_type'  => 'B2B_KONTRAK',
            'company_name' => 'PT Resto Sedap Rasa',
            'is_active'    => true,
        ]);

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
            'user_id'              => $klien->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now()->addDay(),
            'delivery_address'     => 'Resto Sedap Rasa, Jakarta Selatan',
            'grand_total'          => 250000,
            'status'               => 'MENUNGGU_VERIFIKASI',
        ]);

        OrderItem::create([
            'order_id'    => $order->id,
            'product_id'  => $product->id,
            'ordered_qty' => 10,
            'unit_price'  => 25000,
            'subtotal'    => 250000,
        ]);

        $response = $this->actingAs($sekretaris)->get('/sekretaris/verifikasi');
        $response->assertStatus(200);
        $response->assertSee('ORD-GPA-202610-9999');
        $response->assertSee('PT Resto Sedap Rasa');
    }

    public function test_sekretaris_can_approve_order_and_allocate_stock_atomically(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $koordinator = User::factory()->create([
            'role'      => 'KOORDINATOR',
            'is_active' => true,
        ]);

        $klien = User::factory()->create([
            'role'      => 'KLIEN',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku'           => 'GPA-VGT-WRT01',
            'name'          => 'Wortel Berastagi Super',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 18000,
            'minimum_order' => 10,
            'is_active'     => true,
        ]);

        // Buat batch panen dengan stok tersedia 100 kg
        $batch = HarvestBatch::create([
            'product_id'         => $product->id,
            'source_type'        => 'PETANI_BINAAN',
            'supplier_name'      => 'Kelompok Tani Berastagi',
            'batch_date'         => now()->toDateString(),
            'initial_quantity'   => 100,
            'available_quantity' => 100,
            'inputted_by'        => $koordinator->id,
        ]);

        $order = Order::create([
            'order_number'         => 'ORD-GPA-202610-0010',
            'user_id'              => $klien->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now()->addDay(),
            'delivery_address'     => 'Gudang Klien',
            'grand_total'          => 360000,
            'status'               => 'MENUNGGU_VERIFIKASI',
        ]);

        OrderItem::create([
            'order_id'    => $order->id,
            'product_id'  => $product->id,
            'ordered_qty' => 20,
            'unit_price'  => 18000,
            'subtotal'    => 360000,
        ]);

        $response = $this->actingAs($sekretaris)->post("/sekretaris/orders/{$order->id}/approve");

        $response->assertRedirect(route('sekretaris.verification'));
        $response->assertSessionHas('success');

        // Pastikan status pesanan berubah menjadi MENUNGGU_PEMBAYARAN dan invoice diterbitkan
        $order->refresh();
        $this->assertEquals('MENUNGGU_PEMBAYARAN', $order->status);
        $this->assertNotNull($order->invoice_id);
        $this->assertEquals($sekretaris->id, $order->verified_by);

        // Pastikan kuota stok panen berkurang 20 kg (tersisa 80 kg)
        $batch->refresh();
        $this->assertEquals(80, (float) $batch->available_quantity);
    }

    public function test_sekretaris_can_reject_order_with_reason(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $klien = User::factory()->create([
            'role'      => 'KLIEN',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku'           => 'GPA-VGT-BYM01',
            'name'          => 'Bayam Organik',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 12000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        $order = Order::create([
            'order_number'         => 'ORD-GPA-202610-0011',
            'user_id'              => $klien->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now()->addDay(),
            'delivery_address'     => 'Dapur Klien',
            'grand_total'          => 120000,
            'status'               => 'MENUNGGU_VERIFIKASI',
        ]);

        Payment::create([
            'order_id'          => $order->id,
            'payment_reference' => 'PAY-TEST-0011',
            'payment_method'    => 'TRANSFER_MANUAL',
            'amount'            => 120000,
            'status'            => 'MENUNGGU_VERIFIKASI',
        ]);

        $response = $this->actingAs($sekretaris)->post("/sekretaris/orders/{$order->id}/reject", [
            'rejection_reason' => 'Kapasitas armada logistik rute Bogor telah melebihi batas muat harian.',
        ]);

        $response->assertRedirect(route('sekretaris.verification'));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('BATAL', $order->status);
        $this->assertEquals($sekretaris->id, $order->verified_by);

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertEquals('DITOLAK', $payment->status);
        $this->assertEquals('Kapasitas armada logistik rute Bogor telah melebihi batas muat harian.', $payment->verification_note);
    }

    public function test_sekretaris_can_export_operational_reports_csv(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $response = $this->actingAs($sekretaris)->get('/sekretaris/laporan/export');
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }

    public function test_sekretaris_can_view_official_print_report_pdf(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $response = $this->actingAs($sekretaris)->get('/sekretaris/laporan/print');
        $response->assertStatus(200);
        $response->assertSee('KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)');
        $response->assertSee('Jurnal Rekapitulasi Operasional Penimbangan');
    }

    public function test_sekretaris_can_export_invoices_csv(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $response = $this->actingAs($sekretaris)->get('/sekretaris/faktur/export');
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }

    public function test_sekretaris_can_assign_driver_to_order(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $driver = User::factory()->create([
            'name'      => 'Joko Driver Test',
            'role'      => 'ARMADA',
            'is_active' => true,
        ]);

        $klien = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);

        $order = Order::create([
            'order_number'         => 'ORD-GPA-202610-8888',
            'user_id'              => $klien->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now()->addDay(),
            'delivery_address'     => 'Resto Sedap Rasa, Jakarta Selatan',
            'grand_total'          => 250000,
            'status'               => 'LUNAS',
            'driver_id'            => null,
        ]);

        $response = $this->actingAs($sekretaris)->postJson("/sekretaris/surat-jalan/{$order->id}/assign-driver", [
            'driver_id'            => $driver->id,
            'vehicle_plate_number' => 'B 9014 KPA',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'     => true,
            'driver_id'   => $driver->id,
            'driver_name' => 'Joko Driver Test',
        ]);

        $order->refresh();
        $this->assertEquals($driver->id, $order->driver_id);
        $this->assertEquals('B 9014 KPA', $order->vehicle_plate_number);

        // Ubah status menjadi SIAP_KIRIM (Surat Jalan sudah terbit)
        $order->update([
            'status'             => 'SIAP_KIRIM',
            'surat_jalan_number' => 'SJ-GPA-202610-0001',
        ]);

        $anotherDriver = User::factory()->create([
            'name'      => 'Ujang Driver Cadangan',
            'role'      => 'ARMADA',
            'is_active' => true,
        ]);

        // Percobaan mengubah supir setelah Surat Jalan terbit HARUS DITOLAK
        $failResponse = $this->actingAs($sekretaris)->postJson("/sekretaris/surat-jalan/{$order->id}/assign-driver", [
            'driver_id' => $anotherDriver->id,
        ]);

        $failResponse->assertStatus(422);
        $failResponse->assertJson([
            'success' => false,
        ]);

        $order->refresh();
        $this->assertEquals($driver->id, $order->driver_id); // Supir tetap Joko, tidak berubah
    }

    public function test_sekretaris_can_approve_order_via_ajax_json(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $klien = User::factory()->create([
            'role'      => 'KLIEN',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku'           => 'GPA-VGT-AJAX01',
            'name'          => 'Bayam Organik',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 10000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        HarvestBatch::create([
            'product_id'         => $product->id,
            'source_type'        => 'PETANI_BINAAN',
            'supplier_name'      => 'Petani Bayam',
            'batch_date'         => now()->toDateString(),
            'initial_quantity'   => 50,
            'available_quantity' => 50,
            'inputted_by'        => $sekretaris->id,
        ]);

        $order = Order::create([
            'order_number'         => 'ORD-GPA-AJAX-001',
            'user_id'              => $klien->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now()->addDay(),
            'delivery_address'     => 'Resto Bayam, Depok',
            'grand_total'          => 100000,
            'status'               => 'MENUNGGU_VERIFIKASI',
        ]);

        OrderItem::create([
            'order_id'    => $order->id,
            'product_id'  => $product->id,
            'ordered_qty' => 10,
            'unit_price'  => 10000,
            'subtotal'    => 100000,
        ]);

        $response = $this->actingAs($sekretaris)->postJson("/sekretaris/orders/{$order->id}/approve");

        $response->assertStatus(200);
        $response->assertJson([
            'success'  => true,
            'order_id' => $order->id,
            'status'   => 'MENUNGGU_PEMBAYARAN',
        ]);

        $order->refresh();
        $this->assertEquals('MENUNGGU_PEMBAYARAN', $order->status);

        // Pastikan pesanan yang sudah disetujui tidak lagi muncul pada antrean verifikasi
        $queueResponse = $this->actingAs($sekretaris)->get('/sekretaris/verifikasi');
        $queueResponse->assertStatus(200);
        $queueResponse->assertDontSee('ORD-GPA-AJAX-001');
    }
}
