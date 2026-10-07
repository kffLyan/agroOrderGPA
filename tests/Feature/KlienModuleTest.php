<?php

namespace Tests\Feature;

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

    public function test_sekretaris_can_approve_order_to_terverifikasi(): void
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
            'estimated_total' => 280000.00,
            'grand_total' => 280000.00,
            'status' => 'MENUNGGU_VERIFIKASI',
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'ordered_qty' => 20.00,
            'unit_price' => 14000.00,
        ]);

        $response = $this->actingAs($sekretaris)->post("/sekretaris/orders/{$order->id}/approve");
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'TERVERIFIKASI',
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
        $response->assertSee('PT GREEN PASUNDAN AGRICULTURE');
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
        $response->assertSee('PT GREEN PASUNDAN AGRICULTURE');
    }
}
