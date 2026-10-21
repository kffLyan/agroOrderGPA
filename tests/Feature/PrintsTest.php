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
}
