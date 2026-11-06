<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_print_routes(): void
    {
        $response = $this->get('/cetak/surat-jalan');
        $response->assertRedirect('/login');

        $response = $this->get('/cetak/faktur/INV-001');
        $response->assertRedirect('/login');

        $response = $this->get('/cetak/berita-acara-retur');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_surat_jalan_print_page(): void
    {
        $user = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('prints.surat-jalan'));
        $response->assertStatus(200);
        $response->assertSee('SURAT JALAN PENGIRIMAN');
        $response->assertSee('KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)');
    }

    public function test_authenticated_user_can_view_invoice_print_page(): void
    {
        $user = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('prints.invoice', ['invoice' => 'INV-GPA-202610-001']));
        $response->assertStatus(200);
        $response->assertSee('FAKTUR / INVOICE PENAGIHAN');
        $response->assertSee('KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)');
    }

    public function test_authenticated_user_can_view_return_act_print_page(): void
    {
        $user = User::factory()->create([
            'role'      => 'KOORDINATOR',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('prints.return-act'));
        $response->assertStatus(200);
        $response->assertSee('BERITA ACARA RETUR &amp; SELISIH MUTU', false);
        $response->assertSee('KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)');
    }

    public function test_authenticated_direktur_can_view_print_report(): void
    {
        $direktur = User::factory()->create([
            'role'      => 'DIREKTUR',
            'is_active' => true,
        ]);

        $response = $this->actingAs($direktur)->get(route('direktur.laporan.download'));
        $response->assertStatus(200);
        $response->assertSee('KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)');
        $response->assertSee('REKAPITULASI RESMI PENJUALAN KOMODITAS');

        $printResponse = $this->actingAs($direktur)->get(route('prints.report'));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)');
        $printResponse->assertSee('REKAPITULASI RESMI PENJUALAN KOMODITAS');
    }

    public function test_authenticated_sekretaris_can_view_print_report(): void
    {
        $sekretaris = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $response = $this->actingAs($sekretaris)->get(route('sekretaris.laporan.print'));
        $response->assertStatus(200);
        $response->assertSee('KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)');
        $response->assertSee('Jurnal Rekapitulasi Operasional Penimbangan');
    }

    public function test_authenticated_klien_can_view_client_print_views(): void
    {
        $klien = User::factory()->create([
            'role'        => 'KLIEN',
            'client_type' => 'B2B_KONTRAK',
            'is_active'   => true,
        ]);

        $ordersPrint = $this->actingAs($klien)->get(route('klien.pesanan.print-rekap'));
        $ordersPrint->assertStatus(200);
        $ordersPrint->assertViewIs('prints.klien-orders-rekap');
        $ordersPrint->assertSee('REKAPITULASI RESMI TRANSAKSI PEMESANAN');

        $docsPrint = $this->actingAs($klien)->get(route('klien.dokumen.print-rekap'));
        $docsPrint->assertStatus(200);
        $docsPrint->assertViewIs('prints.klien-dokumen');
        $docsPrint->assertSee('REKAPITULASI RESMI FAKTUR KONSOLIDASI');
    }

    public function test_klien_cannot_view_other_clients_return_act(): void
    {
        $klienA = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);
        $klienB = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);

        $orderB = Order::create([
            'order_number'         => 'ORD-TEST-RET-01',
            'user_id'              => $klienB->id,
            'order_source'         => 'WEB_PORTAL',
            'status'               => 'SELESAI',
            'target_delivery_date' => now()->toDateString(),
            'delivery_address'     => 'Bandung',
            'grand_total'          => 500000,
        ]);

        $response = $this->actingAs($klienA)->get(route('prints.return-act', ['order_id' => $orderB->id]));
        $response->assertStatus(403);
    }

    public function test_klien_can_view_own_return_act(): void
    {
        $klienA = User::factory()->create(['role' => 'KLIEN', 'is_active' => true]);

        $orderA = Order::create([
            'order_number'         => 'ORD-TEST-RET-02',
            'user_id'              => $klienA->id,
            'order_source'         => 'WEB_PORTAL',
            'status'               => 'SELESAI',
            'target_delivery_date' => now()->toDateString(),
            'delivery_address'     => 'Bandung',
            'grand_total'          => 500000,
        ]);

        $response = $this->actingAs($klienA)->get(route('prints.return-act', ['order_id' => $orderA->id]));
        $response->assertStatus(200);
        $response->assertSee('BERITA ACARA RETUR &amp; SELISIH MUTU', false);
    }

    public function test_authenticated_user_can_view_thermal_receipt_print_page(): void
    {
        $user = User::factory()->create([
            'role'      => 'SEKRETARIS',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('prints.thermal-receipt', ['invoice' => 'INV-GPA-202610-001']));
        $response->assertStatus(200);
        $response->assertSee('STRUK KASIR');
        $response->assertSee('Green Pasundan Agriculture');
    }

    public function test_invoice_print_displays_standard_format_for_b2b_client(): void
    {
        $sekretaris = User::factory()->create(['role' => 'SEKRETARIS', 'is_active' => true]);
        $client = User::factory()->create([
            'role'         => 'KLIEN',
            'client_type'  => 'B2B_KONTRAK',
            'company_name' => 'PT Katering Boga Mandiri',
            'is_active'    => true,
        ]);

        $invoice = Invoice::create([
            'invoice_number'  => 'INV-TEST-B2B-001',
            'user_id'         => $client->id,
            'period_start'    => now()->toDateString(),
            'period_end'      => now()->toDateString(),
            'subtotal_amount' => 1500000,
            'grand_total'     => 1500000,
            'due_date'        => now()->addDays(14)->toDateString(),
            'status'          => 'UNPAID',
        ]);

        $response = $this->actingAs($sekretaris)->get(route('prints.invoice', ['invoice' => $invoice->invoice_number]));
        $response->assertStatus(200);
        $response->assertSee('FAKTUR / INVOICE PENAGIHAN');
        $response->assertSee('PT Katering Boga Mandiri');
        $response->assertSee('Daftar Pasokan Komoditas');
        $response->assertViewHas('is_contract', false);
    }

    public function test_invoice_print_displays_standard_format_for_reguler_client(): void
    {
        $sekretaris = User::factory()->create(['role' => 'SEKRETARIS', 'is_active' => true]);
        $client = User::factory()->create([
            'role'         => 'KLIEN',
            'client_type'  => 'REGULER',
            'name'         => 'Pak Budi Retail',
            'company_name' => null,
            'is_active'    => true,
        ]);

        $invoice = Invoice::create([
            'invoice_number'  => 'INV-TEST-REG-001',
            'user_id'         => $client->id,
            'period_start'    => now()->toDateString(),
            'period_end'      => now()->toDateString(),
            'subtotal_amount' => 250000,
            'grand_total'     => 250000,
            'due_date'        => now()->toDateString(),
            'status'          => 'PAID',
        ]);

        $response = $this->actingAs($sekretaris)->get(route('prints.invoice', ['invoice' => $invoice->invoice_number]));
        $response->assertStatus(200);
        $response->assertSee('FAKTUR / INVOICE PENAGIHAN');
        $response->assertSee('Pak Budi Retail');
        $response->assertSee('Pelanggan Reguler / Perorangan');
        $response->assertSee('Daftar Pasokan Komoditas');
        $response->assertViewHas('is_contract', false);
    }

    public function test_return_act_displays_credit_note_for_b2b_client(): void
    {
        $sekretaris = User::factory()->create(['role' => 'SEKRETARIS', 'is_active' => true]);
        $b2bClient = User::factory()->create([
            'role'         => 'KLIEN',
            'client_type'  => 'B2B_KONTRAK',
            'company_name' => 'PT Hotel Mewah B2B',
            'is_active'    => true,
        ]);

        $order = Order::create([
            'order_number'         => 'ORD-B2B-RET-01',
            'user_id'              => $b2bClient->id,
            'order_source'         => 'WEB_PORTAL',
            'status'               => 'SELESAI_CATATAN',
            'target_delivery_date' => now()->toDateString(),
            'delivery_address'     => 'Jl Asia Afrika Bandung',
            'grand_total'          => 1500000,
        ]);

        $response = $this->actingAs($sekretaris)->get(route('prints.return-act', ['order_id' => $order->id]));
        $response->assertStatus(200);
        $response->assertSee('BERITA ACARA RETUR');
        $response->assertSee('Mitra B2B (Kontrak / Tempo)');
        $response->assertSee('Pemotongan Nilai Faktur Tagihan (Credit Note)');
    }

    public function test_return_act_displays_direct_refund_for_reguler_client(): void
    {
        $sekretaris = User::factory()->create(['role' => 'SEKRETARIS', 'is_active' => true]);
        $regulerClient = User::factory()->create([
            'role'         => 'KLIEN',
            'client_type'  => 'REGULER',
            'company_name' => null,
            'name'         => 'Ibu Siska Reguler',
            'is_active'    => true,
        ]);

        $order = Order::create([
            'order_number'         => 'ORD-REG-RET-01',
            'user_id'              => $regulerClient->id,
            'order_source'         => 'WEB_PORTAL',
            'status'               => 'SELESAI_CATATAN',
            'target_delivery_date' => now()->toDateString(),
            'delivery_address'     => 'Jl Buah Batu Bandung',
            'grand_total'          => 300000,
        ]);

        $response = $this->actingAs($sekretaris)->get(route('prints.return-act', ['order_id' => $order->id]));
        $response->assertStatus(200);
        $response->assertSee('BERITA ACARA RETUR');
        $response->assertSee('Pelanggan Reguler (Retail / Tunai)');
        $response->assertSee('Pengembalian Dana Langsung (Direct Transfer Refund)');
    }
}

