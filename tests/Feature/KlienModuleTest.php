<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KlienModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/klien/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_non_klien_user_cannot_access_klien_routes(): void
    {
        $direktur = User::factory()->create([
            'role' => 'DIREKTUR',
            'is_active' => true,
        ]);

        $response = $this->actingAs($direktur)->get('/klien/dashboard');
        $response->assertStatus(403);
    }

    public function test_klien_can_access_all_five_core_steps(): void
    {
        $klien = User::factory()->create([
            'role' => 'KLIEN',
            'client_type' => 'B2B_KONTRAK',
            'company_name' => 'PT Mitra Agro Perkasa',
            'is_active' => true,
        ]);

        // Langkah 5: Dashboard
        $response = $this->actingAs($klien)->get('/klien/dashboard');
        $response->assertStatus(200);

        // Langkah 1: Katalog Komoditas
        $response = $this->actingAs($klien)->get('/klien/catalog');
        $response->assertStatus(200);

        // Langkah 2: Keranjang & Pemesanan
        $response = $this->actingAs($klien)->get('/klien/cart');
        $response->assertStatus(200);

        // Langkah 3: Riwayat & Pelacakan Pesanan
        $response = $this->actingAs($klien)->get('/klien/orders');
        $response->assertStatus(200);

        // Langkah 4: Pusat Dokumen & Bukti Bayar
        $response = $this->actingAs($klien)->get('/klien/documents');
        $response->assertStatus(200);

        $response = $this->actingAs($klien)->get('/klien/payment-proof');
        $response->assertStatus(200);
    }

    public function test_klien_can_submit_order_with_tempo_top_without_database_errors(): void
    {
        $koordinator = User::factory()->create([
            'role' => 'KOORDINATOR',
            'is_active' => true,
        ]);

        $klien = User::factory()->create([
            'role' => 'KLIEN',
            'client_type' => 'B2B_KONTRAK',
            'company_name' => 'PT Aerofood ACS Indonesia',
            'is_active' => true,
        ]);

        $product = \App\Models\Product::create([
            'sku' => 'GPA-VGT-RMN01',
            'name' => 'Selada Romaine Hydroponic Super',
            'unit' => 'kg',
            'grade' => 'Grade A',
            'base_price' => 14000.00,
            'minimum_order' => 10.00,
            'is_active' => true,
        ]);

        \App\Models\HarvestBatch::create([
            'product_id' => $product->id,
            'source_type' => 'PETANI_BINAAN',
            'supplier_name' => 'Kelompok Tani Panundaan',
            'batch_date' => now()->toDateString(),
            'initial_quantity' => 1000.00,
            'available_quantity' => 1000.00,
            'inputted_by' => $koordinator->id,
        ]);

        $response = $this->actingAs($klien)->post('/klien/orders', [
            'target_delivery_date' => now()->addDays(2)->format('Y-m-d'),
            'delivery_address' => 'Bandara Soekarno Hatta Loading Dock',
            'payment_method' => 'TEMPO_TOP',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 20,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('orders', [
            'user_id' => $klien->id,
            'status' => 'MENUNGGU_VERIFIKASI',
        ]);
        $this->assertDatabaseHas('payments', [
            'payment_method' => 'TEMPO_TOP',
            'status' => 'MENUNGGU_VERIFIKASI',
        ]);

        $order = \App\Models\Order::where('user_id', $klien->id)->first();
        $response->assertRedirect(route('klien.orders.show', $order->id));

        // Test show order tracking page
        $showResponse = $this->actingAs($klien)->get(route('klien.orders.show', $order->id));
        $showResponse->assertStatus(200);
    }

    public function test_klien_order_double_submission_is_prevented(): void
    {
        $koordinator = User::factory()->create([
            'role' => 'KOORDINATOR',
            'is_active' => true,
        ]);

        $klien = User::factory()->create([
            'role' => 'KLIEN',
            'client_type' => 'B2B_KONTRAK',
            'company_name' => 'PT Resto Sejahtera',
            'is_active' => true,
        ]);

        $product = \App\Models\Product::create([
            'sku' => 'GPA-VGT-KL01',
            'name' => 'Kailan Baby',
            'unit' => 'kg',
            'grade' => 'Grade A',
            'base_price' => 18000.00,
            'minimum_order' => 5.00,
            'is_active' => true,
        ]);

        \App\Models\HarvestBatch::create([
            'product_id' => $product->id,
            'source_type' => 'PETANI_BINAAN',
            'supplier_name' => 'Kelompok Tani Lembang',
            'batch_date' => now()->toDateString(),
            'initial_quantity' => 500.00,
            'available_quantity' => 500.00,
            'inputted_by' => $koordinator->id,
        ]);

        $payload = [
            'submission_token' => 'unique-token-test-1234',
            'target_delivery_date' => now()->addDays(2)->format('Y-m-d'),
            'delivery_address' => 'Gudang Utama Resto',
            'payment_method' => 'TEMPO_TOP',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 25,
                ],
            ],
        ];

        // Pengajuan pertama
        $response1 = $this->actingAs($klien)->post('/klien/orders', $payload);
        $order = \App\Models\Order::where('user_id', $klien->id)->first();
        $this->assertNotNull($order);
        $response1->assertRedirect(route('klien.orders.show', $order->id));

        // Pengajuan kedua dengan token sama (misal: double click)
        $response2 = $this->actingAs($klien)->post('/klien/orders', $payload);
        $response2->assertRedirect(route('klien.orders.show', $order->id));

        // Pengajuan ketiga dengan token berbeda tapi item dan tanggal identik (misal: back browser lalu submit ulang)
        $payloadWithNewToken = array_merge($payload, ['submission_token' => 'new-token-5678']);
        $response3 = $this->actingAs($klien)->post('/klien/orders', $payloadWithNewToken);
        $response3->assertRedirect(route('klien.orders.show', $order->id));

        // Pastikan pesanan di database tetap HANYA 1 (tidak duplikat)
        $this->assertEquals(1, \App\Models\Order::where('user_id', $klien->id)->count());
    }

    public function test_sekretaris_can_approve_order_to_menunggu_pembayaran(): void
    {
        $klien = User::factory()->create([
            'role' => 'KLIEN',
            'client_type' => 'B2B_KONTRAK',
            'is_active' => true,
        ]);

        $sekretaris = User::factory()->create([
            'role' => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $koordinator = User::factory()->create([
            'role' => 'KOORDINATOR',
            'is_active' => true,
        ]);

        $product = \App\Models\Product::create([
            'sku' => 'GPA-VGT-RMN02',
            'name' => 'Selada Romaine B',
            'unit' => 'kg',
            'grade' => 'Grade A',
            'base_price' => 14000.00,
            'minimum_order' => 10.00,
            'is_active' => true,
        ]);

        \App\Models\HarvestBatch::create([
            'product_id' => $product->id,
            'source_type' => 'PETANI_BINAAN',
            'supplier_name' => 'Kelompok Tani Panundaan',
            'batch_date' => now()->toDateString(),
            'initial_quantity' => 500.00,
            'available_quantity' => 500.00,
            'inputted_by' => $koordinator->id,
        ]);

        $order = \App\Models\Order::create([
            'order_number' => 'ORD-GPA-202610-9999',
            'user_id' => $klien->id,
            'target_delivery_date' => now()->addDays(2)->toDateString(),
            'delivery_address' => 'Jl. Test No. 1',
            'grand_total' => 280000.00,
            'status' => 'MENUNGGU_VERIFIKASI',
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'ordered_qty' => 20.00,
            'unit_price' => 14000.00,
            'subtotal' => 280000.00,
        ]);

        $response = $this->actingAs($sekretaris)->post("/sekretaris/orders/{$order->id}/approve");
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'MENUNGGU_PEMBAYARAN',
            'verified_by' => $sekretaris->id,
        ]);
    }

    public function test_klien_can_upload_payment_proof_with_valid_status(): void
    {
        $klien = User::factory()->create([
            'role' => 'KLIEN',
            'client_type' => 'B2B_KONTRAK',
            'is_active' => true,
        ]);

        $invoice = \App\Models\Invoice::create([
            'invoice_number' => 'INV-TEST-001',
            'user_id' => $klien->id,
            'period_start' => now()->subDays(15)->toDateString(),
            'period_end' => now()->toDateString(),
            'subtotal_amount' => 5000000.00,
            'grand_total' => 5000000.00,
            'due_date' => now()->addDays(15)->toDateString(),
            'status' => 'UNPAID',
        ]);

        $response = $this->actingAs($klien)->post('/klien/payment-proof', [
            'invoice_po' => $invoice->invoice_number,
            'amount' => 5000000,
            'sender_bank' => 'BCA',
            'sender_account' => '0821-1234-5678',
            'reference' => 'TRX-123456',
            'transfer_at' => now()->format('d/m/Y H:i'),
            'note' => 'Pelunasan tagihan tempo konsolidasi',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'status' => 'MENUNGGU_VERIFIKASI',
            'payment_reference' => 'TRX-123456',
        ]);
    }

    public function test_klien_can_view_orders_print_rekap_pdf(): void
    {
        $klien = User::factory()->create([
            'role' => 'KLIEN',
            'client_type' => 'B2B_KONTRAK',
            'is_active' => true,
        ]);

        $response = $this->actingAs($klien)->get(route('klien.orders.print-rekap'));
        $response->assertStatus(200);
        $response->assertSee('REKAPITULASI RESMI TRANSAKSI PEMESANAN');
        $response->assertSee('GREEN PASUNDAN AGRICULTURE');
    }

    public function test_klien_can_view_documents_print_rekap_pdf(): void
    {
        $klien = User::factory()->create([
            'role' => 'KLIEN',
            'client_type' => 'B2B_KONTRAK',
            'is_active' => true,
        ]);

        $response = $this->actingAs($klien)->get(route('klien.documents.print-rekap'));
        $response->assertStatus(200);
        $response->assertSee('REKAPITULASI RESMI FAKTUR KONSOLIDASI');
        $response->assertSee('GREEN PASUNDAN AGRICULTURE');
    }

    public function test_klien_can_export_orders_csv(): void
    {
        $klien = User::factory()->create([
            'role' => 'KLIEN',
            'client_type' => 'B2B_KONTRAK',
            'is_active' => true,
        ]);

        $response = $this->actingAs($klien)->get(route('klien.orders.export'));
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }

    public function test_klien_can_export_documents_csv(): void
    {
        $klien = User::factory()->create([
            'role' => 'KLIEN',
            'client_type' => 'B2B_KONTRAK',
            'is_active' => true,
        ]);

        $response = $this->actingAs($klien)->get(route('klien.documents.export'));
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }

    public function test_regular_client_sees_b2b_contract_invitation_banner(): void
    {
        $regulerClient = User::factory()->create([
            'role'        => 'KLIEN',
            'client_type' => 'REGULER',
            'is_active'   => true,
        ]);

        $response = $this->actingAs($regulerClient)->get(route('klien.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Tingkatkan Akun ke Kemitraan B2B Kontrak Resmi');
        $response->assertSee('Ajukan Kontrak Kemitraan B2B');
    }

    public function test_regular_client_can_propose_b2b_contract_and_direktur_approves(): void
    {
        $regulerClient = User::factory()->create([
            'role'        => 'KLIEN',
            'client_type' => 'REGULER',
            'is_active'   => true,
        ]);

        $direktur = User::factory()->create([
            'role'      => 'DIREKTUR',
            'is_active' => true,
        ]);

        $product = \App\Models\Product::create([
            'sku'           => 'GPA-VGT-TMT99',
            'name'          => 'Tomat Organik Spesial',
            'unit'          => 'kg',
            'grade'         => 'Grade A',
            'base_price'    => 20000.00,
            'minimum_order' => 10.00,
            'is_active'     => true,
        ]);

        // 1. Klien reguler mengajukan kontrak B2B
        $response = $this->actingAs($regulerClient)->post(route('klien.contracts.propose'), [
            'product_id'                 => $product->id,
            'committed_volume_per_cycle' => 200,
            'top_days'                   => 30,
        ]);

        $response->assertRedirect(route('klien.dashboard'));
        $response->assertSessionHas('success');

        $contract = \App\Models\Contract::where('user_id', $regulerClient->id)->first();
        $this->assertNotNull($contract);
        $this->assertEquals('PENDING_APPROVAL', $contract->status);

        // 2. Klien melihat banner status pending di dasbor
        $dashResponse = $this->actingAs($regulerClient)->get(route('klien.dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Pengajuan Kontrak Kemitraan B2B Sedang Ditinjau Direktur Utama');
        $dashResponse->assertSee($contract->contract_number);

        // 3. Direktur menyetujui kontrak kemitraan
        $approveResponse = $this->actingAs($direktur)->post(route('direktur.contracts.approve', $contract->id));
        $approveResponse->assertSessionHas('success');

        $contract->refresh();
        $this->assertEquals('ACTIVE', $contract->status);
        $this->assertEquals($direktur->id, $contract->approved_by);

        // 4. Client otomatis ter-upgrade menjadi B2B_KONTRAK
        $regulerClient->refresh();
        $this->assertEquals('B2B_KONTRAK', $regulerClient->client_type);
    }

    public function test_klien_order_detail_shows_bayar_sekarang_when_status_menunggu_pembayaran(): void
    {
        $klien = User::factory()->create([
            'role' => 'KLIEN',
            'client_type' => 'B2B_KONTRAK',
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number'         => 'ORD-TEST-1001',
            'user_id'              => $klien->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now()->addDays(2),
            'delivery_address'     => 'Jl. Raya Mitra No. 1, Jakarta',
            'status'               => 'MENUNGGU_PEMBAYARAN',
            'grand_total'          => 1500000,
        ]);

        $response = $this->actingAs($klien)->get(route('klien.orders.show', $order->id));
        $response->assertStatus(200);
        $response->assertSee('Bayar Sekarang');
        $response->assertSee('Menunggu Pembayaran');
    }

    public function test_klien_can_view_payment_proof_with_payment_channels(): void
    {
        $klien = User::factory()->create([
            'role' => 'KLIEN',
            'client_type' => 'B2B_KONTRAK',
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number'         => 'ORD-TEST-1002',
            'user_id'              => $klien->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now()->addDays(2),
            'delivery_address'     => 'Jl. Industri Mitra No. 5, Bogor',
            'status'               => 'MENUNGGU_PEMBAYARAN',
            'grand_total'          => 2000000,
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-TEST-001',
            'user_id' => $klien->id,
            'subtotal_amount' => 2000000,
            'grand_total' => 2000000,
            'status' => 'UNPAID',
            'period_start' => now(),
            'period_end' => now()->addDays(7),
            'due_date' => now()->addDays(30),
        ]);

        $response = $this->actingAs($klien)->get(route('klien.payment-proof', ['tagihan' => $invoice->invoice_number, 'order_id' => $order->id]));
        $response->assertStatus(200);
        $response->assertSee('Pilih Kanal Pembayaran Resmi GPA');
        $response->assertSee('Bank BCA');
        $response->assertSee('QRIS');
    }

    public function test_klien_order_detail_shows_completed_banner_and_valid_pod_when_order_is_selesai(): void
    {
        $klien = User::factory()->create([
            'role'        => 'KLIEN',
            'client_type' => 'B2B_KONTRAK',
            'is_active'   => true,
        ]);

        $order = Order::create([
            'order_number'         => 'ORD-TEST-9999',
            'user_id'              => $klien->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now(),
            'delivery_address'     => 'Dock Bongkar Sentra Klien',
            'status'               => 'SELESAI',
            'grand_total'          => 1500000,
            'received_by_name'     => 'Pak Joko Dock Head',
            'arrival_time'         => now(),
            'pod_photo_url'        => 'uploads/pod/pod_test.jpg',
            'surat_jalan_number'   => 'SJ-GPA-202610-9999',
        ]);

        $response = $this->actingAs($klien)->get(route('klien.orders.show', $order->id));
        $response->assertStatus(200);
        $response->assertSee('Transaksi Tuntas &bull; Barang Diterima', false);
        $response->assertSee('Pak Joko Dock Head');
        $response->assertSee('SJ-GPA-202610-9999');
        $response->assertSee('uploads/pod/pod_test.jpg');
    }

    public function test_pod_photo_asset_url_accessor_formats_url_safely(): void
    {
        $order1 = new Order(['pod_photo_url' => 'uploads/pod/sample.jpg']);
        $this->assertStringContainsString('uploads/pod/sample.jpg', $order1->pod_photo_asset_url);
        $this->assertStringNotContainsString('storage/uploads', $order1->pod_photo_asset_url);

        $order2 = new Order(['pod_photo_url' => 'pod/sample2.jpg']);
        $this->assertStringContainsString('storage/pod/sample2.jpg', $order2->pod_photo_asset_url);
    }

    public function test_klien_sees_rejection_notification_when_payment_verification_is_rejected(): void
    {
        $klien = User::factory()->create([
            'role'        => 'KLIEN',
            'client_type' => 'B2B_KONTRAK',
            'is_active'   => true,
        ]);

        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number'         => 'ORD-REJECT-001',
            'user_id'              => $klien->id,
            'order_source'         => 'WEB_PORTAL',
            'target_delivery_date' => now()->addDays(2),
            'delivery_address'     => 'Dock Sentra Klien',
            'status'               => 'MENUNGGU_PEMBAYARAN',
            'grand_total'          => 2500000,
        ]);

        $invoice = Invoice::create([
            'invoice_number'  => 'INV-REJECT-001',
            'user_id'         => $klien->id,
            'subtotal_amount' => 2500000,
            'grand_total'     => 2500000,
            'status'          => 'UNPAID',
            'period_start'    => now()->subDays(5),
            'period_end'      => now(),
            'due_date'        => now()->addDays(25),
        ]);

        $order->update(['invoice_id' => $invoice->id]);

        $payment = Payment::create([
            'order_id'          => $order->id,
            'invoice_id'        => $invoice->id,
            'payment_reference' => 'PAY-TRANSFER-777',
            'payment_method'    => 'TRANSFER_MANUAL',
            'amount'            => 2500000,
            'status'            => 'MENUNGGU_VERIFIKASI',
            'proof_url'         => 'payments/proof_invalid.jpg',
            'paid_at'           => now(),
        ]);

        // Sekretaris menolak bukti pembayaran dengan catatan
        $rejectionNote = 'Nominal transfer Rp 2.000.000 tidak sesuai tagihan Rp 2.500.000.';
        $rejectResponse = $this->actingAs($sekretaris)->post("/sekretaris/pembayaran/{$payment->id}/reject", [
            'note' => $rejectionNote,
        ]);
        $rejectResponse->assertRedirect();

        $payment->refresh();
        $this->assertEquals('DITOLAK', $payment->status);
        $this->assertEquals($rejectionNote, $payment->verification_note);

        // Klien mengakses Dashboard: harus ada banner penolakan dan instruksi re-upload
        $dashResponse = $this->actingAs($klien)->get(route('klien.dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Bukti Pembayaran Ditolak Verifikator');
        $dashResponse->assertSee($rejectionNote);
        $dashResponse->assertSee('Unggah Ulang Bukti Bayar');

        // Klien mengakses Form Bukti Pembayaran: harus ada alert penolakan dan form aktif
        $proofResponse = $this->actingAs($klien)->get(route('klien.payment-proof', ['tagihan' => $invoice->invoice_number]));
        $proofResponse->assertStatus(200);
        $proofResponse->assertSee('Verifikasi Pembayaran Ditolak');
        $proofResponse->assertSee($rejectionNote);
        $proofResponse->assertSee('PAY-TRANSFER-777');
        $proofResponse->assertSee('Silakan Unggah Ulang Bukti Pembayaran');

        // Klien mengakses Detail Pesanan: harus ada banner penolakan
        $orderResponse = $this->actingAs($klien)->get(route('klien.orders.show', $order->id));
        $orderResponse->assertStatus(200);
        $orderResponse->assertSee('Verifikasi Pembayaran Ditolak');
        $orderResponse->assertSee($rejectionNote);
        $orderResponse->assertSee('Unggah Ulang Bukti Pembayaran');

        // Klien mengakses Daftar Pesanan: status badge harus menunjukkan bukti bayar ditolak
        $ordersResponse = $this->actingAs($klien)->get(route('klien.orders.index'));
        $ordersResponse->assertStatus(200);
        $ordersResponse->assertSee('Bukti Bayar Ditolak');

        // Klien mengakses Pusat Dokumen: status tagihan menunjukkan bukti bayar ditolak
        $docsResponse = $this->actingAs($klien)->get(route('klien.documents'));
        $docsResponse->assertStatus(200);
        $docsResponse->assertSee('Bukti Bayar Ditolak');
        $docsResponse->assertSee('Unggah Ulang Bukti');
    }
}


