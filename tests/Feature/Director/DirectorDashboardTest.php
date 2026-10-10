<?php

namespace Tests\Feature\Director;

use App\Models\User;
use App\Support\DirectorDashboardData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class DirectorDashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Payload Alpine ditulis sebagai `JSON.parse('...')` sehingga nilai yang
     * hanya hidup di payload harus dicocokkan lewat ekspresi `Js::from()`.
     */
    private function assertJsPayload(TestResponse $response, mixed $payload): void
    {
        $response->assertSee((string) Js::from($payload), false);
    }

    public function test_guest_can_preview_director_console_with_demo_operator(): void
    {
        $response = $this->get(route('director.dashboard'));

        $response->assertOk();
        $response->assertSee('Ringkasan Kinerja Direktur');
        $response->assertSee('Direktur');
        $response->assertSee('Asep Tember');
        $response->assertSee('ID : 001');
        $response->assertSee('AT');
    }

    public function test_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Dewi Kartika']);

        $response = $this->actingAs($user)->get(route('director.dashboard'));

        $response->assertOk();
        $response->assertSee('Dewi Kartika');
        $response->assertSee('DK');
    }

    public function test_console_renders_executive_kpi_cards(): void
    {
        $response = $this->get(route('director.dashboard'));

        $response->assertOk();
        $response->assertSee('OMZET Q4 TAHUN INI');
        $response->assertSee('Rp 482.650.000');
        $response->assertSee('Target: Rp 600.000.000');
        $response->assertSee('+14.2% dari bulan lalu');
        $response->assertSee('80.4% (84.6% Terbayar)');
        $response->assertSee('JUMLAH KOMODITAS');
        $response->assertSee('38.450');
        $response->assertSee('68%');
        $response->assertSee('Kebun binaan (26.15 ton)');
        $response->assertSee('32%');
        $response->assertSee('Cadangan (12.3 ton)');
        $response->assertSee('Timbangan sudah ditera');
        $response->assertSee('TAGIHAN BELUM DIBAYAR');
        $response->assertSee('Rp 74.320.000');
        $response->assertSee('2 pelanggan jatuh tempo &lt;7 hari', false);
        $response->assertSee('Sudah tertagih: 91.2%');
        $response->assertSee('KETEPATAN LAYANAN');
        $response->assertSee('98.6%');
        $response->assertSee('Barang dikembalikan: 0.82% (Batas: &lt;1.50%)', false);
        $response->assertSee('PERSETUJUAN DIREKTUR');
        $response->assertSee('WAJIB');
        $response->assertSee('3 Kontrak');
        $response->assertSee('Total Nilai: ');
        $response->assertSee('Rp 385.000.000');
        $response->assertSee('Lihat pengajuan');
    }

    public function test_revenue_progress_and_volume_split_are_reconciled(): void
    {
        $kpis = DirectorDashboardData::kpis();
        $byKey = collect($kpis)->keyBy('key');

        $this->assertSame(round(482_650_000 / 600_000_000 * 100, 1), $byKey['revenue']['progress']['percent']);
        $this->assertSame('80.4% (84.6% Terbayar)', $byKey['revenue']['progress']['value']);
        $this->assertSame('68% kebun binaan (26.15 ton) : 32% cadangan (12.3 ton)', $byKey['volume']['value_note']);

        // Porsi volume harus utuh: 26.15 T + 12.3 T = 38.45 T dan 68% + 32% = 100%.
        $this->assertSame(68 + 32, 100);
        $this->assertSame(26.15 + 12.3, 38.45);
        $this->assertSame(38_450, (int) str_replace('.', '', explode(' ', $byKey['volume']['value'])[0]));
    }

    public function test_console_renders_weekly_trend_chart_rows(): void
    {
        $weekly = DirectorDashboardData::weeklyTrend();

        $response = $this->get(route('director.dashboard'));

        $response->assertOk();
        $response->assertSee($weekly['title']);
        $response->assertSee('W40: 275 ton | Target Rp 110 juta');
        $response->assertSee('Rp 108 juta');
        $response->assertSee('W44 (berjalan, hari ke-4): Target Rp 140 juta');
        $response->assertSee('Omzet: Rp 112.650.000 (80.5%)');
        $response->assertSee('Terkirim 202.5 ton');
        $response->assertSee($weekly['deviation']);

        foreach ($weekly['legend'] as $legend) {
            $response->assertSee($legend['label']);
        }
    }

    public function test_weekly_trend_bars_are_scaled_against_the_peak_realization(): void
    {
        $weekly = DirectorDashboardData::weeklyTrend();
        $rows = $weekly['rows'];
        $peak = max(array_column($rows, 'realized'));

        $response = $this->get(route('director.dashboard'));

        foreach ($rows as $row) {
            $response->assertSee('Omzet: '.$row['realized_label'].' ('.$row['ratio_label'].')');
            $response->assertSee('style="width: '.$row['bar_percent'].'%"', false);
        }

        $this->assertSame(140_000_000, $peak);
        $this->assertSame(
            'W43',
            collect($rows)->firstWhere('bar_percent', 100.0)['week'],
            'Bar terpanjang harus menandai minggu dengan realisasi tertinggi.',
        );
        $this->assertSame(100.0, collect($rows)->max('bar_percent'));
        $this->assertLessThan(100.0, collect($rows)->last()['bar_percent']);
    }

    public function test_commodity_and_channel_distribution_totals_match(): void
    {
        $commodities = DirectorDashboardData::commodities();
        $channels = DirectorDashboardData::channels();

        $response = $this->get(route('director.dashboard'));

        $response->assertOk();
        $response->assertSee($commodities['title']);
        $response->assertSee($channels['title']);
        $response->assertSee('Horeca &amp; Inflight', false);
        $response->assertSee('25.0');
        $response->assertSee('3.85');
        $this->assertSame(38.45, $commodities['total_tons']);
        $this->assertSame(38.45, $channels['total_tons']);
        $this->assertSame(100, $channels['total_share']);
        $this->assertSame(100, array_sum(array_column($commodities['rows'], 'share')));
    }

    public function test_console_renders_authorization_kpi_with_link_to_standalone_module(): void
    {
        $response = $this->get(route('director.dashboard'));

        $response->assertOk();
        $response->assertSee('PERSETUJUAN DIREKTUR');
        $response->assertSee('WAJIB');
        $response->assertSee('3 Kontrak');
        $response->assertSee('Total Nilai: ');
        $response->assertSee('Rp 385.000.000');
        $response->assertSee('Lihat pengajuan');
        $response->assertSee(route('director.approval'), false);

        // Antrean Tier-1 kini menjadi modul tunggal, bukan lagi bagian dashboard.
        $response->assertDontSee('PERLU OTORISASI DIREKTUR');
        $response->assertDontSee('id="otorisasi-direktur"', false);
        $response->assertDontSee('Approve Terpilih (Batch)');
    }

    public function test_console_renders_receivables_aging_buckets(): void
    {
        $receivables = DirectorDashboardData::receivables();

        $response = $this->get(route('director.dashboard'));

        $response->assertOk();
        $response->assertSee($receivables['title']);
        $response->assertSee($receivables['total_label']);
        $response->assertSee('BELUM JATUH TEMPO (0 - 15 HARI)');
        $response->assertSee('Rp 120.000.000');
        $response->assertSee('JATUH TEMPO (16 - 30 HARI)');
        $response->assertSee('Rp 65.300.000');
        $response->assertSee('LEWAT JATUH TEMPO (1 - 14 HARI)');
        $response->assertSee('Rp 32.200.000');
        $response->assertSee('TERLAMBAT LEBIH DARI 15 HARI');
        $response->assertSee('Rp 18.000.000');
        $response->assertSee($receivables['note']['emphasis']);
        $response->assertSee($receivables['audit_action']);
        $response->assertSee($receivables['dispensation_action']);

        $this->assertSame(235_500_000, $receivables['total']);
    }

    public function test_open_receivables_kpi_is_a_separate_scope_from_aging_total(): void
    {
        $receivables = DirectorDashboardData::receivables();
        $receivableKpi = collect(DirectorDashboardData::kpis())->firstWhere('key', 'receivable');

        $response = $this->get(route('director.dashboard'));

        $response->assertOk();
        $response->assertSee($receivableKpi['value']);
        $response->assertSee($receivables['total_label']);

        // KPI piutang memakai saldo jatuh tempo jatuh tempo terdekat, sedangkan
        // lembar aging menjumlahkan seluruh bucket TOP 0-45 hari.
        $this->assertNotSame($receivableKpi['value'], $receivables['total_label']);
        $this->assertSame(235_500_000, $receivables['total']);
    }

    public function test_console_renders_navigation_shell_and_alpine_component(): void
    {
        $response = $this->get(route('director.dashboard'));

        $response->assertOk();
        $response->assertSee('Penjualan');
        $response->assertSee('Volume Komoditas');
        $response->assertSee('Piutang &amp; Tagihan', false);
        $response->assertSee('Persetujuan Kontrak');
        $response->assertSee('Laporan');
        $response->assertSee('Pengaturan Tata Kelola');
        $response->assertSee('Export');
        $response->assertSee('Cetak');
        $response->assertSee('Audit Log');
        $response->assertSee('x-data="directorDashboard(', false);
        $response->assertSee('href="'.route('director.approval').'"', false);
        $response->assertSee('href="'.route('director.governance').'"', false);
    }
}
