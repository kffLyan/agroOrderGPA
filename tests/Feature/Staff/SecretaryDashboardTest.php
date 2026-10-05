<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecretaryDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_preview_secretary_console_with_demo_operator(): void
    {
        $response = $this->get(route('secretary.dashboard'));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSee('Sekretaris');
        $response->assertSee('ID : 007');
    }

    public function test_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Siti Rahmawati']);

        $response = $this->actingAs($user)->get(route('secretary.dashboard'));

        $response->assertOk();
        $response->assertSee('Siti Rahmawati');
        $response->assertSee('SR');
    }

    public function test_console_shows_heading_and_key_metrics(): void
    {
        $response = $this->get(route('secretary.dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Konsol Operasional &amp; Tata Kelola Administrasi (Sekretaris)', false);
        $response->assertSee('Total Pesanan Masuk (Hari Ini)');
        $response->assertSee('Menunggu Verifikasi Sekre');
        $response->assertSee('Surat Jalan Siap Terbit');
        $response->assertSee('Antrean Pembayaran Manual');
        $response->assertSee('Rp 84.650.000');
        $response->assertSee('Rp 19.420.000');
        $response->assertSee('H-1 Pukul 15:00 WIB');
        $response->assertSee('Locked Net Weight');
    }

    public function test_console_lists_priority_orders_with_authorisation_actions(): void
    {
        $response = $this->get(route('secretary.dashboard'));

        $response->assertOk();
        $response->assertSee('Antrean Verifikasi Pesanan Masuk Prioritas');
        $response->assertSee('PO-2024-0941');
        $response->assertSee('Hotel Santika Premiere');
        $response->assertSee('PO-2024-0942');
        $response->assertSee('Resto Dapur Sunda Cianjur');
        $response->assertSee('PO-2024-0943');
        $response->assertSee('Catering Melati Nusantara');
        $response->assertSee('PO-2024-0944');
        $response->assertSee('Supermarket Segar Fresh (Cab. BSD)');
        $response->assertSee('Setujui');
        $response->assertSee('Eskalasi');
        $response->assertSee('Split PO');
        $response->assertSee('Total 4 PO Pending');
    }

    public function test_console_renders_pipeline_stock_and_alert_sections(): void
    {
        $response = $this->get(route('secretary.dashboard'));

        $response->assertOk();
        $response->assertSee('Pipeline Siklus Pesanan Hari Ini');
        $response->assertSee('Batch Logistik: B-02');
        $response->assertSee('32 Menit / Order');
        $response->assertSee('Kesiapan Stok &amp; Buffer Panen', false);
        $response->assertSee('Gudang Transit Cibitung');
        $response->assertSee('Stroberi Ciwidey');
        $response->assertSee('Kol Putih Medan');
        $response->assertSee('Peringatan Operasional &amp; Logistik', false);
        $response->assertSee('Sisa 91 Menit');
        $response->assertSee('B-9021-UYX');
    }

    public function test_console_navigation_exposes_secretary_modules(): void
    {
        $response = $this->get(route('secretary.dashboard'));

        $response->assertOk();
        $response->assertSee('Verifikasi Pesanan');
        $response->assertSee('Surat Jalan');
        $response->assertSee('Faktur &amp; Tagihan', false);
        $response->assertSee('Verifikasi Pembayaran');
        $response->assertSee('Rekap Laporan');
        $response->assertSee('Input Order Manual');
    }

    public function test_client_dashboard_route_is_not_overwritten(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Ringkasan &amp; Operasional Klien B2B', false);
        $response->assertDontSee('Antrean Verifikasi Pesanan Masuk Prioritas');
    }
}
