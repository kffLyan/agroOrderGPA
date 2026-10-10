<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecretaryOrderVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_preview_verification_console_with_demo_operator(): void
    {
        $response = $this->get(route('secretary.verification'));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSee('Sekretaris');
        $response->assertSee('ID : 007');
    }

    public function test_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Siti Rahmawati']);

        $response = $this->actingAs($user)->get(route('secretary.verification'));

        $response->assertOk();
        $response->assertSee('Siti Rahmawati');
        $response->assertSee('SR');
    }

    public function test_console_shows_heading_compliance_and_workflow_steps(): void
    {
        $response = $this->get(route('secretary.verification'));

        $response->assertOk();
        $response->assertSee('Konsol Verifikasi Pesanan &amp; Kesiapan Alokasi (Sekretaris Desk)', false);
        $response->assertSee('Tingkat Kepatuhan Validasi');
        $response->assertSee('100.0% (Zero Bypass)');
        $response->assertSee('01 Antrean Verifikasi Pesanan');
        $response->assertSee('02 Konsolidasi Faktur &amp; Penagihan Tempo', false);
        $response->assertSee('03 Verifikasi Pembayaran Manual');
    }

    public function test_queue_lists_three_orders_with_channel_filters(): void
    {
        $response = $this->get(route('secretary.verification'));

        $response->assertOk();
        $response->assertSee('Antrean Pesanan Masuk');
        $response->assertSee('3 Item');
        $response->assertSee('Semua (3)');
        $response->assertSee('Web B2B (2)');
        $response->assertSee('WA / Manual (1)');
        $response->assertSee('ORD-GPA-202610-0042');
        $response->assertSee('Katering Berkah Mandiri');
        $response->assertSee('ORD-GPA-202610-0045');
        $response->assertSee('Hotel Grand Pangrango');
        $response->assertSee('ORD-GPA-202610-0039');
        $response->assertSee('Resto Daun Hijau');
        $response->assertSee('Manual WA');
        $response->assertSee('Butuh Buffer');
        $response->assertSee('Injeksi Mitra Cisarua');
        $response->assertSee('Buffer Defisit 80 kg');
    }

    public function test_inspection_panel_mounts_alpine_console_with_selected_order(): void
    {
        $response = $this->get(route('secretary.verification'));

        $response->assertOk();
        $response->assertSee('secretaryVerification(', false);
        $response->assertSee('order-inspection', false);
        $response->assertSee('selected.order_no', false);
        $response->assertSee('selected.allocations', false);
        $response->assertSee('selected.pricing', false);
        $response->assertSee('selected.deliveries', false);
    }

    public function test_console_renders_stock_allocation_and_rule_notices(): void
    {
        $response = $this->get(route('secretary.verification'));

        $response->assertOk();
        $response->assertSee('Validasi Stok &amp; Alokasi Pasokan', false);
        $response->assertSee('Permintaan (PO)');
        $response->assertSee('Rencana Alokasi Pasokan (Harvest + Buffer)');
        $response->assertSee('Status Validasi');
    }

    public function test_console_renders_order_actions(): void
    {
        $response = $this->get(route('secretary.verification'));

        $response->assertOk();
        $response->assertSee('Tolak Pesanan');
        $response->assertSee('Minta Revisi Klien');
        $response->assertSee('Verifikasi &amp; Teruskan ke Koordinator Lapangan', false);
    }

    public function test_console_renders_rule_14_invoice_consolidation_preview(): void
    {
        $response = $this->get(route('secretary.verification'));

        $response->assertOk();
        $response->assertSee('Konsolidasi Faktur Bulanan &amp; Penagihan Tempo', false);
        $response->assertSee('Cetak Draft Tagihan');
        $response->assertSee('Terbitkan Invoice Tempo (TOP 30)');
        $response->assertSee('SJ-GPA', false);
        $response->assertSee('10-0198', false);
        $response->assertSee('10-0214', false);
        $response->assertSee('10-0229', false);
        $response->assertSee('Actual Netto: 650.0 kg (Net Weight Valid)', false);
        $response->assertSee('BAP TTD Lengkap');
        $response->assertSee('selected.deliveries_total', false);
        $response->assertSee('selected.deliveries_held', false);
    }

    public function test_navigation_marks_verification_module_as_active(): void
    {
        $response = $this->get(route('secretary.verification'));

        $response->assertOk();
        $response->assertSee('href="'.route('secretary.verification').'"', false);
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('Dashboard');
    }

    public function test_secretary_dashboard_route_is_not_overwritten(): void
    {
        $response = $this->get(route('secretary.dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Konsol Operasional &amp; Tata Kelola Administrasi', false);
        $response->assertSee('Pipeline Siklus Pesanan Hari Ini');
        $response->assertDontSee('Konsolidasi Faktur Bulanan &amp; Penagihan Tempo', false);
    }
}
