<?php

namespace Tests\Feature\Coordinator;

use App\Models\User;
use App\Support\CoordinatorDashboardData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CoordinatorDashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Data Alpine ditulis sebagai `JSON.parse('...')` sehingga nilai yang hanya
     * hidup di payload harus dicocokkan lewat ekspresi `Js::from()`.
     */
    private function assertJsPayload(TestResponse $response, mixed $payload): void
    {
        $response->assertSee((string) Js::from($payload), false);
    }

    public function test_guest_can_preview_coordinator_console_with_demo_operator(): void
    {
        $response = $this->get(route('coordinator.dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard Operasional Pasokan &amp; Kesiapan', false);
        $response->assertSee('Koordinator Lapangan');
        $response->assertSee('Agus Tusan');
        $response->assertSee('ID : 002');
    }

    public function test_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Andi Nugroho']);

        $response = $this->actingAs($user)->get(route('coordinator.dashboard'));

        $response->assertOk();
        $response->assertSee('Andi Nugroho');
        $response->assertSee('AN');
    }

    public function test_console_renders_operational_kpi_cards(): void
    {
        $response = $this->get(route('coordinator.dashboard'));

        $response->assertOk();
        $response->assertSee('Pesanan Terverifikasi (Siap Pack)');
        $response->assertSee('1.840');
        $response->assertSee('28 PO Aktif');
        $response->assertSee('100% Terlock');
        $response->assertSee('Komoditas Butuh Timbang');
        $response->assertSee('82.4%');
        $response->assertSee('5.93 / 7.20 TON');
        $response->assertSee('-1.18%');
        $response->assertSee('In Tolerance');
    }

    public function test_console_renders_supply_table_reconciliation(): void
    {
        $supply = CoordinatorDashboardData::supply();

        $response = $this->get(route('coordinator.dashboard'));

        $response->assertOk();
        $response->assertSee($supply['title']);
        $response->assertSee($supply['description'], false);
        $response->assertSee('1.920 KG');
        $response->assertSee('1.840 KG');
        $response->assertSee('+80 KG');

        $this->assertSame(
            1840,
            array_sum(array_column($supply['rows'], 'order')),
            'Total order aktif harus sesuai dengan KPI 1.840 KG.',
        );
        $this->assertSame(
            1920,
            array_sum(array_map(
                static fn (array $row): int => $row['supply'] + $row['buffer'],
                $supply['rows'],
            )),
            'Total tersedia harus sesuai dengan header 1.920 KG.',
        );
        $this->assertSame(
            80,
            array_sum(array_map(
                static fn (array $row): int => $row['supply'] + $row['buffer'] - $row['order'],
                $supply['rows'],
            )),
            'Sisa buffer bebas harus tetap +80 KG.',
        );
    }

    public function test_console_renders_supply_rows_in_alpine_payload(): void
    {
        $supply = CoordinatorDashboardData::supply();

        $response = $this->get(route('coordinator.dashboard'));

        $response->assertOk();
        $this->assertJsPayload($response, $supply['rows']);

        foreach ($supply['rows'] as $row) {
            $response->assertSee($row['batch'], false);
            $response->assertSee($row['gap'], false);
        }
    }

    public function test_console_renders_packing_cold_chain_stage(): void
    {
        $packing = CoordinatorDashboardData::packing();

        $response = $this->get(route('coordinator.dashboard'));

        $response->assertOk();
        $response->assertSee($packing['title']);
        $response->assertSee($packing['chip'], false);
        $response->assertSee('Buka Kontrol Cold-Storage Hub');

        $this->assertSame([85, 62, 35], array_column($packing['orders'], 'percent'));
        $this->assertJsPayload($response, $packing['orders']);
    }

    public function test_console_renders_gate_weighing_logs_with_consistent_net_weight(): void
    {
        $gate = CoordinatorDashboardData::gate();

        $response = $this->get(route('coordinator.dashboard'));

        $response->assertOk();
        $response->assertSee($gate['title']);
        $response->assertSee('Timbangan Digital Metrologi #MET-SUB-01');
        $response->assertSee('Input Tiket Baru');

        foreach ($gate['logs'] as $log) {
            $response->assertSee($log['ticket'], false);

            $this->assertSame(
                $log['gross'] - $log['tare'],
                $log['net'],
                "Netto {$log['ticket']} harus sama dengan bruto dikurangi tara.",
            );
        }

        $this->assertJsPayload($response, $gate['logs']);
    }

    public function test_console_renders_operator_navigation_shell(): void
    {
        $response = $this->get(route('coordinator.dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard', false);
        $response->assertSee('Rencana Panen');
        $response->assertSee('Timbangan &amp; Sortir', false);
        $response->assertSee('Manajemen Stok');
        $response->assertSee('Surat Jalan &amp; Logistik', false);
        $response->assertSee('Monitoring');
        $response->assertSee('Laporan Retur');
        $response->assertSee('Sync Timbangan');
        $response->assertSee('Ctrl + K', false);
        $response->assertSee('Input Timbangan Cepat');
    }

    public function test_console_renders_global_search_shortcut_and_alpine_components(): void
    {
        $response = $this->get(route('coordinator.dashboard'));

        $response->assertOk();
        $response->assertSee('Cari No. PO, Klien B2B, Resi Logistik...', false);
        $response->assertSee('x-data="coordinatorDashboard"', false);
        $response->assertSee('coordinatorConsole(', false);
    }

    public function test_console_renders_status_footer(): void
    {
        $footer = CoordinatorDashboardData::footer();

        $response = $this->get(route('coordinator.dashboard'));

        $response->assertOk();
        $response->assertSee($footer['right'], false);

        foreach ($footer['left'] as $item) {
            $response->assertSee($item['label']);
        }
    }
}
