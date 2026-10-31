<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Pengujian Rute /pesanan-manual hanya untuk Sekretaris & Direktur
     */
    public function test_klien_cannot_access_pesanan_manual_route(): void
    {
        $klien = User::factory()->create([
            'role'      => 'KLIEN',
            'is_active' => true,
        ]);

        $response = $this->actingAs($klien)->get('/pesanan-manual');
        $response->assertStatus(403);
    }

    public function test_armada_cannot_access_pesanan_manual_route(): void
    {
        $armada = User::factory()->create([
            'role'      => 'ARMADA',
            'is_active' => true,
        ]);

        $response = $this->actingAs($armada)->get('/pesanan-manual');
        $response->assertStatus(403);
    }

    public function test_sekretaris_can_access_pesanan_manual_route(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $response = $this->actingAs($sekretaris)->get('/pesanan-manual');
        $response->assertStatus(200);
    }

    /**
     * 2. Pengujian Rute /cetak/laporan-eksekutif hanya untuk Direktur & Sekretaris
     */
    public function test_klien_cannot_access_laporan_eksekutif_print(): void
    {
        $klien = User::factory()->create([
            'role'      => 'KLIEN',
            'is_active' => true,
        ]);

        $response = $this->actingAs($klien)->get('/cetak/laporan-eksekutif');
        $response->assertStatus(403);
    }

    public function test_armada_cannot_access_laporan_eksekutif_print(): void
    {
        $armada = User::factory()->create([
            'role'      => 'ARMADA',
            'is_active' => true,
        ]);

        $response = $this->actingAs($armada)->get('/cetak/laporan-eksekutif');
        $response->assertStatus(403);
    }

    public function test_direktur_can_access_laporan_eksekutif_print(): void
    {
        $direktur = User::factory()->create([
            'role'      => 'DIREKTUR',
            'is_active' => true,
        ]);

        $response = $this->actingAs($direktur)->get('/cetak/laporan-eksekutif');
        $response->assertStatus(200);
    }

    /**
     * 3. Anti-IDOR: Klien A dilarang mengintip pesanan Klien B
     */
    public function test_klien_cannot_view_other_clients_order(): void
    {
        $klienA = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);
        $klienB = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);

        $orderB = Order::create([
            'order_number'         => 'ORD-B-001',
            'user_id'              => $klienB->id,
            'target_delivery_date' => now()->addDays(2),
            'delivery_address'     => 'Alamat Klien B',
            'grand_total'          => 500000,
            'status'               => 'MENUNGGU_VERIFIKASI',
        ]);

        $response = $this->actingAs($klienA)->get('/klien/pesanan/' . $orderB->id);
        $response->assertStatus(403);
    }

    /**
     * 4. Anti-IDOR: Klien A dilarang mencetak faktur milik Klien B
     */
    public function test_klien_cannot_view_other_clients_invoice_print(): void
    {
        $klienA = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);
        $klienB = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);

        $invoiceB = Invoice::create([
            'invoice_number'  => 'INV-B-001',
            'user_id'         => $klienB->id,
            'period_end'      => now()->addDays(30),
            'subtotal_amount' => 1000000,
            'grand_total'     => 1000000,
            'due_date'        => now()->addDays(30),
            'status'          => 'UNPAID',
        ]);

        $response = $this->actingAs($klienA)->get('/cetak/faktur/' . $invoiceB->invoice_number);
        $response->assertStatus(403);
    }

    /**
     * 5. Anti-IDOR: Klien A dilarang melihat Surat Jalan pesanan milik Klien B
     */
    public function test_klien_cannot_view_other_clients_surat_jalan_print(): void
    {
        $klienA = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);
        $klienB = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);

        $orderB = Order::create([
            'order_number'         => 'ORD-B-002',
            'user_id'              => $klienB->id,
            'target_delivery_date' => now()->addDays(2),
            'delivery_address'     => 'Alamat Klien B',
            'grand_total'          => 750000,
            'status'               => 'SIAP_KIRIM',
        ]);

        $response = $this->actingAs($klienA)->get('/cetak/surat-jalan?order_id=' . $orderB->id);
        $response->assertStatus(403);
    }

    /**
     * 6. Anti-IDOR: Klien A dilarang mengunggah bukti bayar untuk invoice Klien B
     */
    public function test_klien_cannot_submit_payment_proof_for_other_clients_invoice(): void
    {
        $klienA = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);
        $klienB = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);

        $invoiceB = Invoice::create([
            'invoice_number'  => 'INV-B-002',
            'user_id'         => $klienB->id,
            'period_end'      => now()->addDays(30),
            'subtotal_amount' => 500000,
            'grand_total'     => 500000,
            'due_date'        => now()->addDays(30),
            'status'          => 'UNPAID',
        ]);

        $response = $this->actingAs($klienA)->post('/klien/bukti-pembayaran', [
            'invoice_po'     => $invoiceB->invoice_number,
            'amount'         => 500000,
            'sender_bank'    => 'BCA',
            'sender_account' => '1234567890',
        ]);

        $response->assertSessionHasErrors('invoice_po');
    }

    /**
     * 7. Anti-IDOR: Supir Armada A dilarang memulai rute atau mengirim PoD untuk pesanan Supir Armada B
     */
    public function test_armada_cannot_alter_order_assigned_to_another_driver(): void
    {
        $driverA = User::factory()->create(['role' => 'ARMADA', 'is_active' => true]);
        $driverB = User::factory()->create(['role' => 'ARMADA', 'is_active' => true]);
        $klien   = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);

        $orderB = Order::create([
            'order_number'         => 'ORD-DRV-001',
            'user_id'              => $klien->id,
            'driver_id'            => $driverB->id,
            'target_delivery_date' => now()->addDays(1),
            'delivery_address'     => 'Toko Sayur Segar',
            'grand_total'          => 400000,
            'status'               => 'SIAP_KIRIM',
        ]);

        // Coba mulai rute oleh Driver A
        $startResponse = $this->actingAs($driverA)->post('/armada/surat-jalan/' . $orderB->id . '/mulai');
        $startResponse->assertStatus(403);

        // Coba kirim PoD oleh Driver A
        $podResponse = $this->actingAs($driverA)->post('/armada/bukti-terima/' . $orderB->id . '/selesai', [
            'condition' => 'clear',
        ]);
        $podResponse->assertStatus(403);
    }
}

