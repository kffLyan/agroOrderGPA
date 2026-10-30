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

        $response = $this->actingAs($direktur)->get(route('direktur.reports.download'));
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

        $response = $this->actingAs($sekretaris)->get(route('sekretaris.reports.print'));
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

        $ordersPrint = $this->actingAs($klien)->get(route('klien.orders.print-rekap'));
        $ordersPrint->assertStatus(200);
        $ordersPrint->assertViewIs('prints.client-orders-rekap');
        $ordersPrint->assertSee('REKAPITULASI RESMI TRANSAKSI PEMESANAN');

        $docsPrint = $this->actingAs($klien)->get(route('klien.documents.print-rekap'));
        $docsPrint->assertStatus(200);
        $docsPrint->assertViewIs('prints.client-documents-rekap');
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
}

