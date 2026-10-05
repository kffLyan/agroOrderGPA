<?php

namespace Tests\Feature\Director;

use App\Models\User;
use App\Support\DirectorReportData;
use App\Support\DirectorSalesData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectorReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Judul tabel pada design mengandung "&" yang di-escape Blade, sehingga
     * klaim tampilan harus dicocokkan dalam bentuk hasil escape.
     */
    private function viewTitle(string $title): string
    {
        return e($title);
    }

    public function test_guest_can_preview_report_with_demo_operator(): void
    {
        $response = $this->get(route('director.report'));

        $response->assertOk();
        $response->assertSee('Direktur');
        $response->assertSee('Asep Tember');
    }

    public function test_report_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Dewi Kartika']);

        $response = $this->actingAs($user)->get(route('director.report'));

        $response->assertOk();
        $response->assertSee('Dewi Kartika');
    }

    public function test_laporan_page_renders_locked_archive(): void
    {
        $response = $this->get(route('director.report'));

        $response->assertOk();
        $response->assertSee('Laporan Penjualan Eksekutif');
        $response->assertSee('IMMUTABLE ARCHIVE');
        $response->assertSee('AUDIT TRAIL LOG TERMINAL');
    }

    public function test_lock_banner_states_archive_identity(): void
    {
        $response = $this->get(route('director.report'));

        $response->assertOk();
        $response->assertSee(DirectorReportData::PERIOD);
        $response->assertSee(DirectorReportData::LOCKED_AT);
        $response->assertSee(DirectorReportData::SIGNATORY);
        $response->assertSee(DirectorReportData::HASH);
        $response->assertSee(DirectorReportData::CERTIFICATE);
    }

    public function test_report_renders_both_reconciliation_tables(): void
    {
        $response = $this->get(route('director.report'));

        $response->assertOk();
        $response->assertSee($this->viewTitle(DirectorReportData::commodities()['title']), false);
        $response->assertSee($this->viewTitle(DirectorReportData::clients()['title']), false);
        $response->assertSee('TOTAL KONSOLIDASI (5 KOMODITAS)');
        $response->assertSee('TOTAL REKAPITULASI B2B (Q4 2026)');
    }

    public function test_report_revenue_matches_shared_baseline(): void
    {
        $commodities = DirectorReportData::commodities();

        $this->assertSame(DirectorSalesData::REVENUE, DirectorReportData::REVENUE);
        $this->assertSame(DirectorReportData::REVENUE, $commodities['total_revenue']);
    }

    public function test_commodity_rows_reconcile_po_against_tera_sah(): void
    {
        $commodities = DirectorReportData::commodities();

        foreach ($commodities['rows'] as $row) {
            $this->assertSame(
                $row['po_kg'] - $row['tera_kg'],
                $row['shrinkage_kg'],
                "Deviasi {$row['name']} harus sama dengan selisih PO dan tera sah."
            );
            $this->assertSame(
                (int) round($row['tera_kg'] * $row['price'], -3),
                $row['omzet'],
                "Omzet {$row['name']} harus mengikuti tera sah dikali harga."
            );
            $this->assertLessThan(
                DirectorReportData::TOLERANCE_CEILING,
                abs($row['deviation']),
                "Deviasi {$row['name']} harus di bawah plafon toleransi."
            );
        }
    }

    public function test_commodity_totals_match_design_totals(): void
    {
        $commodities = DirectorReportData::commodities();

        $this->assertSame(38_860, $commodities['total_po_kg']);
        $this->assertSame(38_450, $commodities['total_tera_kg']);
        $this->assertSame(410, $commodities['total_shrinkage_kg']);
        $this->assertSame(38_860 - 38_450, $commodities['total_shrinkage_kg']);
        $this->assertSame(25.2, $commodities['total_margin']);
    }

    public function test_client_rows_settle_gross_into_inflow_and_receivable(): void
    {
        $clients = DirectorReportData::clients();

        foreach ($clients['rows'] as $row) {
            $this->assertSame(
                $row['settled'] + $row['receivable'],
                $row['gross'],
                "Sisa piutang {$row['name']} harus melengkapi inflow terhadap tagihan bruto."
            );
            $this->assertGreaterThanOrEqual(0, $row['receivable'], "Sisa piutang {$row['name']} tidak boleh negatif.");
        }
    }

    public function test_client_totals_match_directive_cash_position(): void
    {
        $clients = DirectorReportData::clients();

        $this->assertSame(84, $clients['total_po_count']);
        $this->assertSame(38_450, $clients['total_volume_kg']);
        $this->assertSame(DirectorReportData::REVENUE, $clients['total_gross']);
        $this->assertSame(DirectorReportData::CASH_INFLOW, $clients['total_settled']);
        $this->assertSame(DirectorReportData::RECEIVABLE, $clients['total_receivable']);
    }

    public function test_client_volume_ties_back_to_commodity_tera_sah(): void
    {
        $commodities = DirectorReportData::commodities();
        $clients = DirectorReportData::clients();

        $this->assertSame($commodities['total_tera_kg'], $clients['total_volume_kg']);
        $this->assertSame($commodities['total_revenue'], $clients['total_gross']);
    }

    public function test_cash_inflow_and_receivable_close_the_ledger(): void
    {
        $this->assertSame(
            DirectorReportData::REVENUE,
            DirectorReportData::CASH_INFLOW + DirectorReportData::RECEIVABLE
        );
    }

    public function test_deviation_claim_stays_under_tolerance_ceiling(): void
    {
        $percent = round(DirectorReportData::SHRINKAGE_CLAIM / DirectorReportData::REVENUE * 100, 2);

        $this->assertSame(1.53, $percent);
        $this->assertLessThan(DirectorReportData::TOLERANCE_CEILING, $percent);
    }

    public function test_deviation_claim_splits_into_shrinkage_and_returns(): void
    {
        $this->assertSame(
            DirectorReportData::SHRINKAGE_CLAIM,
            DirectorReportData::SHRINKAGE_VALUE + DirectorReportData::RETURN_VALUE
        );
    }

    public function test_audit_trail_ends_with_lock_engaged(): void
    {
        $entries = DirectorReportData::auditTrail()['entries'];
        $last = end($entries);

        $this->assertCount(4, $entries);
        $this->assertSame('> STATUS_LOCK_ENGAGED:', $last['tag']);
        $this->assertTrue($last['highlighted']);
        $this->assertSame('Write-access dicabut permanen.', $last['trailing']);
        $this->assertStringContainsString(
            '2026-10-24 23:59:59',
            $last['timestamp']
        );
    }

    public function test_filters_are_declared_frozen(): void
    {
        $filters = DirectorReportData::filters();

        $this->assertCount(3, $filters['items']);
        $this->assertSame(DirectorReportData::PERIOD_SHORT, $filters['items'][0]['value']);
        $this->assertSame('[TERKUNCI / LOCKED]', $filters['items'][0]['chip']);
    }

    public function test_kpi_cards_expose_four_locked_indicators(): void
    {
        $cards = DirectorReportData::kpiCards();

        $this->assertCount(4, $cards);
        $this->assertSame(
            ['revenue', 'volume', 'inflow', 'deviation'],
            array_column($cards, 'key')
        );
    }

    public function test_supply_ratio_splits_farmer_and_buffer(): void
    {
        $volume = DirectorReportData::commodities()['total_tera_kg'] / 1000;

        $this->assertEqualsWithDelta(38.45, $volume, 0.001);
        $this->assertEqualsWithDelta(
            $volume,
            DirectorReportData::FARMER_VOLUME + DirectorReportData::BUFFER_VOLUME,
            0.001,
            'Jumlah pasokan dan buffer harus menutup volume tera sah.'
        );
        $this->assertEqualsWithDelta(68.0, (DirectorReportData::FARMER_VOLUME / $volume) * 100, 0.5);
        $this->assertEqualsWithDelta(32.0, (DirectorReportData::BUFFER_VOLUME / $volume) * 100, 0.5);
    }
}
