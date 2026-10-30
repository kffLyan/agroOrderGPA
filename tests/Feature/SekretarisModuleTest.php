<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\HarvestBatch;
use App\Models\Invoice;
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

    public function test_sekretaris_can_approve_payment_and_update_related_entities(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $klien = User::factory()->create([
            'role'      => 'KLIEN',
            'is_active' => true,
        ]);

        $invoice = Invoice::create([
            'invoice_number'  => 'INV-GPA-202610-0099',
            'user_id'         => $klien->id,
            'period_start'    => now()->toDateString(),
            'period_end'      => now()->addDays(7)->toDateString(),
            'subtotal_amount' => 500000,
            'grand_total'     => 500000,
            'due_date'        => now()->addDays(14)->toDateString(),
            'status'          => 'UNPAID',
        ]);

        $orderEligible = Order::create([
            'order_number'         => 'ORD-GPA-PAY-001',
            'user_id'              => $klien->id,
            'invoice_id'           => $invoice->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now()->addDay(),
            'delivery_address'     => 'Resto Rasa Lezat, Jakarta',
            'grand_total'          => 500000,
            'status'               => 'MENUNGGU_PEMBAYARAN',
        ]);

        $orderIneligible = Order::create([
            'order_number'         => 'ORD-GPA-PAY-002',
            'user_id'              => $klien->id,
            'invoice_id'           => $invoice->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now()->addDay(),
            'delivery_address'     => 'Resto Rasa Lezat, Jakarta',
            'grand_total'          => 200000,
            'status'               => 'BATAL',
        ]);

        $payment = Payment::create([
            'invoice_id'        => $invoice->id,
            'order_id'          => $orderEligible->id,
            'payment_reference' => 'PAY-TEST-APPROVE-01',
            'payment_method'    => 'TRANSFER_BANK',
            'amount'            => 500000,
            'status'            => 'MENUNGGU_VERIFIKASI',
        ]);

        $response = $this->actingAs($sekretaris)->postJson("/sekretaris/pembayaran/{$payment->id}/approve", [
            'note' => 'Rekening koran BCA klir dan sesuai mutasi.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Pembayaran PAY-' . str_pad($payment->id, 4, '0', STR_PAD_LEFT) . ' berhasil diverifikasi dan disahkan lunas.',
        ]);

        $payment->refresh();
        $this->assertEquals('LUNAS', $payment->status);
        $this->assertEquals($sekretaris->id, $payment->verified_by);
        $this->assertNotNull($payment->paid_at);
        $this->assertEquals('Rekening koran BCA klir dan sesuai mutasi.', $payment->verification_note);

        $invoice->refresh();
        $this->assertEquals('PAID', $invoice->status);

        $orderEligible->refresh();
        $this->assertEquals('LUNAS', $orderEligible->status);

        // Pesanan dengan status BATAL tidak boleh diubah menjadi LUNAS
        $orderIneligible->refresh();
        $this->assertEquals('BATAL', $orderIneligible->status);

        // Skenario 2: Pembayaran terhubung langsung ke Order tanpa Invoice (menguji pembaruan verified_by pada order langsung)
        $directOrder = Order::create([
            'order_number'         => 'ORD-GPA-PAY-DIRECT',
            'user_id'              => $klien->id,
            'invoice_id'           => null,
            'order_source'         => 'WHATSAPP_ADMIN',
            'target_delivery_date' => now()->addDay(),
            'delivery_address'     => 'Klien Langsung',
            'grand_total'          => 150000,
            'status'               => 'MENUNGGU_PEMBAYARAN',
            'verified_by'          => null,
        ]);

        $directPayment = Payment::create([
            'invoice_id'        => null,
            'order_id'          => $directOrder->id,
            'payment_reference' => 'PAY-TEST-DIRECT-01',
            'payment_method'    => 'TRANSFER_BANK',
            'amount'            => 150000,
            'status'            => 'MENUNGGU_VERIFIKASI',
        ]);

        $directResponse = $this->actingAs($sekretaris)->postJson("/sekretaris/pembayaran/{$directPayment->id}/approve");
        $directResponse->assertStatus(200);

        $directOrder->refresh();
        $this->assertEquals('LUNAS', $directOrder->status);
        $this->assertEquals($sekretaris->id, $directOrder->verified_by);
    }

    public function test_sekretaris_verification_queue_displays_client_contract_plafon_correctly(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $klienWithContract = User::factory()->create([
            'role'         => 'KLIEN',
            'client_type'  => 'B2B_KONTRAK',
            'company_name' => 'PT Pangan Nusantara',
            'is_active'    => true,
        ]);

        $klienWithoutContract = User::factory()->create([
            'role'         => 'KLIEN',
            'client_type'  => 'REGULER',
            'company_name' => 'Kedai Mandiri',
            'is_active'    => true,
        ]);

        $product = Product::create([
            'sku'           => 'GPA-CTR-001',
            'name'          => 'Selada Romaine',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 20000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        // Buat 2 kontrak aktif untuk klienWithContract
        // Kontrak 1 (id lebih awal): harga 15.000, volume 200 -> plafon = 15.000 * 200 * 2 = 6.000.000 (Rp 6.000.000)
        Contract::create([
            'user_id'                    => $klienWithContract->id,
            'product_id'                 => $product->id,
            'contract_number'            => 'CTR-GPA-TEST-001',
            'fixed_price_per_kg'         => 15000,
            'top_days'                   => 30,
            'committed_volume_per_cycle' => 200,
            'status'                     => 'ACTIVE',
            'approved_by'                => $sekretaris->id,
            'approved_at'                => now(),
        ]);

        // Kontrak 2 (id lebih akhir): harga 30.000, volume 500 -> plafon = 30.000 * 500 * 2 = 30.000.000 (Rp 30.000.000)
        Contract::create([
            'user_id'                    => $klienWithContract->id,
            'product_id'                 => $product->id,
            'contract_number'            => 'CTR-GPA-TEST-002',
            'fixed_price_per_kg'         => 30000,
            'top_days'                   => 30,
            'committed_volume_per_cycle' => 500,
            'status'                     => 'ACTIVE',
            'approved_by'                => $sekretaris->id,
            'approved_at'                => now(),
        ]);

        $orderWithContract = Order::create([
            'order_number'         => 'ORD-GPA-TEST-CTR',
            'user_id'              => $klienWithContract->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now()->addDay(),
            'delivery_address'     => 'Gudang PT Pangan Nusantara',
            'grand_total'          => 150000,
            'status'               => 'MENUNGGU_VERIFIKASI',
        ]);

        OrderItem::create([
            'order_id'    => $orderWithContract->id,
            'product_id'  => $product->id,
            'ordered_qty' => 10,
            'unit_price'  => 15000,
            'subtotal'    => 150000,
        ]);

        $orderWithoutContract = Order::create([
            'order_number'         => 'ORD-GPA-TEST-REG',
            'user_id'              => $klienWithoutContract->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now()->addDay(),
            'delivery_address'     => 'Kedai Mandiri, Bandung',
            'grand_total'          => 100000,
            'status'               => 'MENUNGGU_VERIFIKASI',
        ]);

        OrderItem::create([
            'order_id'    => $orderWithoutContract->id,
            'product_id'  => $product->id,
            'ordered_qty' => 5,
            'unit_price'  => 20000,
            'subtotal'    => 100000,
        ]);

        $response = $this->actingAs($sekretaris)->get('/sekretaris/verifikasi');
        $response->assertStatus(200);

        $mappedOrders = $response->viewData('mappedOrders');
        $this->assertIsArray($mappedOrders);

        $orderContractItem = collect($mappedOrders)->firstWhere('order_id', $orderWithContract->id);
        $this->assertNotNull($orderContractItem);
        // Pemilihan kontrak harus memilih kontrak aktif pertama (Contract 1: Rp 6.000.000, bukan Contract 2: Rp 30.000.000)
        $this->assertEquals('Rp 6.000.000', $orderContractItem['plafon']);

        $orderRegulerItem = collect($mappedOrders)->firstWhere('order_id', $orderWithoutContract->id);
        $this->assertNotNull($orderRegulerItem);
        // Klien reguler / tanpa kontrak aktif menggunakan teks fallback
        $this->assertEquals('Sesuai Ketentuan PO', $orderRegulerItem['plafon']);

        // Verifikasi dalam representasi HTML / JSON Alpine yang dirender
        $response->assertSee('Rp 6.000.000');
        $response->assertSee('Sesuai Ketentuan PO');
        $response->assertDontSee('Rp 30.000.000');
    }

    public function test_sekretaris_pod_monitoring_calculates_total_return_kg_accurately(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $klien = User::factory()->create([
            'role'      => 'KLIEN',
            'is_active' => true,
        ]);

        $driver = User::factory()->create([
            'name'      => 'Asep Driver Monitoring',
            'role'      => 'ARMADA',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku'           => 'GPA-POD-001',
            'name'          => 'Buncis Kenia',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 15000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        // 1. Pesanan SELESAI_CATATAN dengan beberapa item (nilai retur positif, nol, dan null)
        $orderDiscrepancy = Order::create([
            'order_number'         => 'ORD-GPA-POD-DISC',
            'user_id'              => $klien->id,
            'driver_id'            => $driver->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now(),
            'delivery_address'     => 'Klien Catatan Resto',
            'grand_total'          => 450000,
            'status'               => 'SELESAI_CATATAN',
            'pod_photo_url'        => 'pod/test_disc.jpg',
        ]);

        OrderItem::create([
            'order_id'        => $orderDiscrepancy->id,
            'product_id'      => $product->id,
            'ordered_qty'     => 10,
            'unit_price'      => 15000,
            'subtotal'        => 150000,
            'returned_weight' => 3.5,
            'return_reason'   => 'Layu dalam perjalanan',
        ]);

        OrderItem::create([
            'order_id'        => $orderDiscrepancy->id,
            'product_id'      => $product->id,
            'ordered_qty'     => 10,
            'unit_price'      => 15000,
            'subtotal'        => 150000,
            'returned_weight' => 1.2,
            'return_reason'   => 'Sortir ukuran kecil',
        ]);

        OrderItem::create([
            'order_id'        => $orderDiscrepancy->id,
            'product_id'      => $product->id,
            'ordered_qty'     => 5,
            'unit_price'      => 15000,
            'subtotal'        => 75000,
            'returned_weight' => 0.0,
            'return_reason'   => null,
        ]);

        OrderItem::create([
            'order_id'        => $orderDiscrepancy->id,
            'product_id'      => $product->id,
            'ordered_qty'     => 5,
            'unit_price'      => 15000,
            'subtotal'        => 75000,
            'returned_weight' => null,
            'return_reason'   => null,
        ]);

        // Total retur dari orderDiscrepancy: 3.5 + 1.2 + 0.0 + 0 = 4.7 kg

        // 2. Pesanan SELESAI (pengantaran bersih tanpa retur)
        $orderClean = Order::create([
            'order_number'         => 'ORD-GPA-POD-CLEAN',
            'user_id'              => $klien->id,
            'driver_id'            => $driver->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now(),
            'delivery_address'     => 'Klien Bersih Resto',
            'grand_total'          => 150000,
            'status'               => 'SELESAI',
            'pod_photo_url'        => 'pod/test_clean.jpg',
        ]);

        OrderItem::create([
            'order_id'        => $orderClean->id,
            'product_id'      => $product->id,
            'ordered_qty'     => 10,
            'unit_price'      => 15000,
            'subtotal'        => 150000,
            'returned_weight' => 0.0,
        ]);

        // 3. Pesanan DALAM_PENGIRIMAN dengan returned_weight dummy yang TIDAK BOLEH ikut dihitung ke total retur
        $orderInTransit = Order::create([
            'order_number'         => 'ORD-GPA-POD-TRANSIT',
            'user_id'              => $klien->id,
            'driver_id'            => $driver->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now(),
            'delivery_address'     => 'Klien Transit Resto',
            'grand_total'          => 150000,
            'status'               => 'DALAM_PENGIRIMAN',
        ]);

        OrderItem::create([
            'order_id'        => $orderInTransit->id,
            'product_id'      => $product->id,
            'ordered_qty'     => 10,
            'unit_price'      => 15000,
            'subtotal'        => 150000,
            'returned_weight' => 8.0, // Harus diabaikan karena status bukan SELESAI_CATATAN
        ]);

        $response = $this->actingAs($sekretaris)->get('/sekretaris/monitoring-pod');
        $response->assertStatus(200);

        // Verifikasi metrik ringkasan
        $response->assertViewHas('discrepancyCount', 1);
        $response->assertViewHas('cleanCount', 1);
        $response->assertViewHas('inTransitCount', 1);
        $response->assertViewHas('totalCompleted', 2);
        $response->assertViewHas('totalReturnKg', 4.7);

        // Verifikasi tampilan blade yang dirender
        $response->assertSee('Total 4,7 kg susut/sortir');
    }

    public function test_sekretaris_manual_order_page_includes_governance_payment_channels(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $response = $this->actingAs($sekretaris)->get('/sekretaris/pesanan-manual');
        $response->assertStatus(200);
        $response->assertViewHas('paymentChannels');
        $response->assertSee('Gateway &amp; Rekening Resmi Direksi', false);
        $response->assertSee('Kanal Resmi GPA (Bank / QRIS)');
        $response->assertSee('Tunai / Cash (Kasir)');
    }

    public function test_sekretaris_can_submit_manual_order_with_payment_channel_and_uploaded_proof(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $klien = User::factory()->create([
            'role'      => 'KLIEN',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku'           => 'GPA-VGT-TMT09',
            'name'          => 'Tomat Organik Spesial',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 20000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        $fakeFile = \Illuminate\Http\UploadedFile::fake()->image('transfer_receipt.jpg', 600, 400);

        $payload = [
            'client_id'          => $klien->id,
            'client_name'        => $klien->name,
            'client_phone'       => '08123456789',
            'delivery_address'   => 'Jl. Buah Batu No. 12, Bandung',
            'target_delivery_date' => now()->addDay()->toDateString(),
            'order_source'       => 'WHATSAPP_ADMIN',
            'payment_method'     => 'transfer',
            'payment_channel_id' => 'bca',
            'sender_bank'        => 'Bank BCA',
            'sender_account'     => '8210-992-3841',
            'payment_reference'  => 'TRX-BCA-88291',
            'proof_file'         => $fakeFile,
            'status'             => 'MENUNGGU_VERIFIKASI',
            'items'              => [
                [
                    'product_id' => $product->id,
                    'quantity'   => 10,
                    'price'      => 20000,
                ],
            ],
        ];

        $response = $this->actingAs($sekretaris)->post('/sekretaris/pesanan-manual', $payload);
        $response->assertRedirect(route('sekretaris.verification'));

        $this->assertDatabaseHas('orders', [
            'user_id'     => $klien->id,
            'grand_total' => 200000,
            'status'      => 'MENUNGGU_VERIFIKASI',
        ]);

        $this->assertDatabaseHas('payments', [
            'payment_reference' => 'TRX-BCA-88291',
            'payment_method'    => 'TRANSFER_BANK',
            'amount'            => 200000,
        ]);
    }

    public function test_sekretaris_can_submit_manual_order_with_cash_and_calculates_change(): void
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
            'sku'           => 'GPA-VGT-CASH01',
            'name'          => 'Wortel Brastagi Kasir',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 15000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        $payload = [
            'client_id'          => $klien->id,
            'client_name'        => $klien->name,
            'client_phone'       => '08123456789',
            'delivery_address'   => 'Jl. Riau No. 45, Bandung',
            'target_delivery_date' => now()->addDay()->toDateString(),
            'order_source'       => 'WHATSAPP_ADMIN',
            'payment_method'     => 'cash',
            'payment_channel_id' => 'cash',
            'cash_amount'        => 200000,
            'cash_change'        => 50000,
            'status'             => 'MENUNGGU_VERIFIKASI',
            'items'              => [
                [
                    'product_id' => $product->id,
                    'quantity'   => 10,
                    'price'      => 15000,
                ],
            ],
        ];

        $response = $this->actingAs($sekretaris)->post('/sekretaris/pesanan-manual', $payload);
        $response->assertRedirect(route('sekretaris.verification'));

        $this->assertDatabaseHas('orders', [
            'user_id'     => $klien->id,
            'grand_total' => 150000,
            'status'      => 'MENUNGGU_VERIFIKASI',
        ]);

        $payment = Payment::where('order_id', Order::latest('id')->first()->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('CASH', $payment->payment_method);
        $this->assertEquals(150000, $payment->amount);
        $this->assertStringContainsString('Pembayaran Tunai Kasir: Total Rp 150.000', $payment->verification_note);
        $this->assertStringContainsString('Diterima Rp 200.000', $payment->verification_note);
        $this->assertStringContainsString('Kembalian Rp 50.000', $payment->verification_note);
    }

    public function test_manual_order_rejects_duplicate_items(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku'           => 'GPA-VGT-DUP01',
            'name'          => 'Kailan Organik',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 12000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        $payload = [
            'delivery_address'   => 'Jl. Test No. 1',
            'target_delivery_date' => now()->addDay()->toDateString(),
            'order_source'       => 'WHATSAPP_ADMIN',
            'payment_method'     => 'cash',
            'items'              => [
                ['product_id' => $product->id, 'quantity' => 5, 'price' => 12000],
                ['product_id' => $product->id, 'quantity' => 10, 'price' => 12000],
            ],
        ];

        $response = $this->actingAs($sekretaris)->post('/sekretaris/pesanan-manual', $payload);
        $response->assertSessionHasErrors(['items.0.product_id', 'items.1.product_id']);
    }

    public function test_manual_order_rejects_when_stock_insufficient(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku'           => 'GPA-VGT-STK01',
            'name'          => 'Bayam Jepang',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 10000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        \App\Models\HarvestBatch::create([
            'product_id'         => $product->id,
            'source_type'        => 'PETANI_BINAAN',
            'supplier_name'      => 'Petani Ciwidey',
            'batch_date'         => now()->toDateString(),
            'initial_quantity'   => 20,
            'available_quantity' => 20,
            'inputted_by'        => $sekretaris->id,
        ]);

        $payload = [
            'delivery_address'   => 'Jl. Test No. 2',
            'target_delivery_date' => now()->addDay()->toDateString(),
            'order_source'       => 'WHATSAPP_ADMIN',
            'payment_method'     => 'cash',
            'items'              => [
                ['product_id' => $product->id, 'quantity' => 50, 'price' => 10000],
            ],
        ];

        $response = $this->actingAs($sekretaris)->post('/sekretaris/pesanan-manual', $payload);
        $response->assertSessionHasErrors(['items']);
    }

    public function test_manual_order_rejects_when_master_freeze_active(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku'           => 'GPA-VGT-FRZ01',
            'name'          => 'Selada Keriting',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 15000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        $gov = app(\App\Services\GovernanceService::class);
        $gov->toggleMasterFreeze(true, 'Uji Coba Darurat', 'Direktur Utama');

        $payload = [
            'delivery_address'   => 'Jl. Test No. 3',
            'target_delivery_date' => now()->addDay()->toDateString(),
            'order_source'       => 'WHATSAPP_ADMIN',
            'payment_method'     => 'cash',
            'items'              => [
                ['product_id' => $product->id, 'quantity' => 5, 'price' => 15000],
            ],
        ];

        $response = $this->actingAs($sekretaris)->post('/sekretaris/pesanan-manual', $payload);
        $response->assertSessionHasErrors(['general']);

        // Kembalikan ke normal
        $gov->toggleMasterFreeze(false, null, 'Direktur Utama');
    }

    public function test_manual_order_rejects_when_client_is_frozen(): void
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
            'sku'           => 'GPA-VGT-CLF01',
            'name'          => 'Pakcoy Super',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 14000,
            'minimum_order' => 5,
            'is_active'     => true,
        ]);

        $gov = app(\App\Services\GovernanceService::class);
        $gov->toggleClientFreeze($klien->id, true);

        $payload = [
            'client_id'          => $klien->id,
            'delivery_address'   => 'Jl. Test No. 4',
            'target_delivery_date' => now()->addDay()->toDateString(),
            'order_source'       => 'WHATSAPP_ADMIN',
            'payment_method'     => 'cash',
            'items'              => [
                ['product_id' => $product->id, 'quantity' => 5, 'price' => 14000],
            ],
        ];

        $response = $this->actingAs($sekretaris)->post('/sekretaris/pesanan-manual', $payload);
        $response->assertSessionHasErrors(['client_id']);

        // Kembalikan ke normal
        $gov->toggleClientFreeze($klien->id, false);
    }

    public function test_invoice_number_generator_produces_unique_sequential_numbers(): void
    {
        $orderService = app(\App\Services\OrderService::class);
        $num1 = $orderService->generateInvoiceNumber();
        $this->assertStringStartsWith('INV-GPA-' . date('Ym') . '-', $num1);

        Invoice::create([
            'invoice_number'  => $num1,
            'user_id'         => User::factory()->create(['role' => 'KLIEN'])->id,
            'period_start'    => now()->toDateString(),
            'period_end'      => now()->toDateString(),
            'subtotal_amount' => 100000,
            'grand_total'     => 100000,
            'due_date'        => now()->addDays(14)->toDateString(),
            'status'          => 'UNPAID',
        ]);

        $num2 = $orderService->generateInvoiceNumber();
        $this->assertNotEquals($num1, $num2);
    }
}
