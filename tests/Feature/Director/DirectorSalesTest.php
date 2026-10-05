<?php

namespace Tests\Feature\Director;

use App\Models\User;
use App\Support\DirectorSalesData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class DirectorSalesTest extends TestCase
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

    public function test_guest_can_preview_sales_console_with_demo_operator(): void
    {
        $response = $this->get(route('director.sales'));

        $response->assertOk();
        $response->assertSee('Analisis Revenue Eksekutif');
        $response->assertSee('Monitoring Penjualan &amp; Analisis Revenue Eksekutif', false);
        $response->assertSee('Direktur');
        $response->assertSee('Asep Tember');
        $response->assertSee('ID : 001');
        $response->assertSee('AT');
    }

    public function test_sales_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Dewi Kartika']);

        $response = $this->actingAs($user)->get(route('director.sales'));

        $response->assertOk();
        $response->assertSee('Dewi Kartika');
        $response->assertSee('DK');
    }

    public function test_page_header_exposes_scope_tabs_and_recap_actions(): void
    {
        $header = DirectorSalesData::header();
        $scopes = DirectorSalesData::kpiScopes();

        $response = $this->get(route('director.sales'));

        $response->assertOk();
        $response->assertSee($header['title']);
        $response->assertSee($header['subtitle']);
        $response->assertSee($header['export_label']);
        $response->assertSee($header['print_label']);

        foreach ($scopes as $scope) {
            $response->assertSee($scope['period']);
            $response->assertSee($scope['tab']);
            $response->assertSee($scope['hint']);
        }

        $this->assertSame('WTD (Mingguan)', $scopes['WTD']['tab']);
        $this->assertSame('YTD Konsolidasi', $scopes['YTD']['tab']);
    }

    public function test_kpi_band_renders_four_reconciled_cards_per_scope(): void
    {
        $scopes = DirectorSalesData::kpiScopes();

        $response = $this->get(route('director.sales'));

        $response->assertOk();
        $this->assertSame(['MTD', 'WTD', 'YTD'], array_keys($scopes));

        foreach ($scopes as $scope) {
            $this->assertCount(4, $scope['cards']);

            foreach ($scope['cards'] as $card) {
                $response->assertSee($card['value']);
                $response->assertSee($card['badge']);
                $response->assertSee('style="width: '.max(2, min(100, $card['progress']['percent'])).'%"', false);
            }
        }

        $mtd = collect($scopes['MTD']['cards'])->keyBy('key');
        $this->assertSame('Rp 482.650.000', $mtd['revenue']['value']);
        $this->assertSame(80.4, $mtd['revenue']['progress']['percent']);
        $this->assertSame(86.7, $mtd['billing']['progress']['percent']);
        $this->assertSame('21.8%', $mtd['margin']['value']);
        $this->assertSame('Terpenuhi', $mtd['settled']['value']);
        $this->assertSame('Masuk', $mtd['billing']['value']);

        $this->assertSame(round(482_650_000 / 600_000_000 * 100, 1), $mtd['revenue']['progress']['percent']);
        $this->assertSame(round(482_650_000 / 556_970_000 * 100, 1), $mtd['billing']['progress']['percent']);
        $this->assertSame(round(21.8 / 30 * 100, 1), $mtd['margin']['progress']['percent']);
    }

    public function test_kpi_settlement_card_reports_physical_volume_from_portfolio(): void
    {
        $portfolio = DirectorSalesData::portfolio();
        $settled = collect(DirectorSalesData::kpiScopes()['MTD']['cards'])->firstWhere('key', 'settled');

        $response = $this->get(route('director.sales'));

        $response->assertOk();
        $this->assertSame(
            $portfolio['total_volume_label'],
            $settled['footer'][1]['text'],
            'Kartu (SETTLED) harus melaporkan volume fisik yang sama dengan total tabel portofolio.',
        );
        $response->assertSee($portfolio['total_volume_label']);
    }

    public function test_weekly_chart_reconciles_with_monthly_revenue(): void
    {
        $trend = DirectorSalesData::weeklyTrend();

        $response = $this->get(route('director.sales'));

        $response->assertOk();
        $response->assertSee($trend['title']);
        $response->assertSee($trend['subtitle']);
        $response->assertSee($trend['ceiling_label']);
        $response->assertSee($trend['bep_label']);
        $response->assertSee($trend['peak_label']);
        $response->assertSee($trend['footer']);

        foreach ($trend['rows'] as $row) {
            $response->assertSee($row['week']);
            $response->assertSee($row['label']);
            $response->assertSee($row['value_label']);
            $response->assertSee('style="height: '.$row['share'].'%"', false);
        }

        foreach ($trend['legend'] as $item) {
            $response->assertSee($item['label']);
        }

        // Lima minggu MTD harus sama dengan omzet konsolidasi modul.
        $this->assertSame(DirectorSalesData::REVENUE, $trend['total']);
        $this->assertSame(120_000_000, $trend['ceiling']);
        $this->assertSame(54.2, $trend['bep_percent']);

        $this->assertSame(
            'W43',
            collect($trend['rows'])->firstWhere('is_peak', true)['week'],
            'Bar hijau harus menandai minggu dengan realisasi tertinggi.',
        );
        $this->assertSame(
            'W44',
            collect($trend['rows'])->firstWhere('status', 'running')['week'],
        );

        foreach ($trend['rows'] as $row) {
            $this->assertSame(round($row['value'] / $trend['ceiling'] * 100, 1), $row['share']);
            $this->assertLessThanOrEqual(100, $row['share']);
        }
    }

    public function test_channel_composition_sums_to_total_revenue(): void
    {
        $channels = DirectorSalesData::channels();

        $response = $this->get(route('director.sales'));

        $response->assertOk();
        $response->assertSee($channels['badge']);
        $response->assertSee($channels['title']);
        $response->assertSee($channels['subtitle']);
        $response->assertSee($channels['total_label']);

        foreach ($channels['rows'] as $row) {
            $response->assertSee($row['name']);
            $response->assertSee($row['value_label']);
            $response->assertSee($row['share_label']);
            $response->assertSee('style="width: '.$row['share'].'%"', false);
        }

        $this->assertSame(DirectorSalesData::REVENUE, $channels['total']);
        $this->assertSame(100, $channels['total_share']);

        foreach ($channels['rows'] as $row) {
            $this->assertSame((int) round($row['value'] / $channels['total'] * 100, 0), $row['share']);
        }
    }

    public function test_portfolio_table_renders_columns_rows_and_reconciled_totals(): void
    {
        $portfolio = DirectorSalesData::portfolio();

        $response = $this->get(route('director.sales'));

        $response->assertOk();
        $response->assertSee($portfolio['badge']);
        $response->assertSee($portfolio['rule']);
        $response->assertSee($portfolio['title']);
        $response->assertSee($portfolio['subtitle']);
        $response->assertSee($portfolio['footer']);
        $response->assertSee($portfolio['verified']);

        foreach ($portfolio['columns'] as $column) {
            $response->assertSee($column);
        }

        foreach ($portfolio['rows'] as $row) {
            $response->assertSee($row['name']);
            $response->assertSee($row['sku']);
            $response->assertSee($row['volume_label']);
            $response->assertSee($row['list_price_label']);
            $response->assertSee($row['price_label']);
            $response->assertSee($row['revenue_label']);
            $response->assertSee($row['gross_label']);
            $response->assertSee($row['margin_label']);
            $response->assertSee($row['share_label']);
            $response->assertSee($row['status']);
        }

        $response->assertSee($portfolio['total_volume_label']);
        $response->assertSee($portfolio['total_revenue_label']);
        $response->assertSee($portfolio['total_gross_label']);
        $response->assertSee($portfolio['total_margin_label']);
        $response->assertSee($portfolio['total_share_label']);
    }

    public function test_portfolio_totals_match_the_sum_of_every_row(): void
    {
        $portfolio = DirectorSalesData::portfolio();
        $rows = $portfolio['rows'];

        $revenueTotal = array_sum(array_column($rows, 'revenue'));
        $grossTotal = array_sum(array_column($rows, 'gross'));
        $volumeTotal = array_sum(array_column($rows, 'volume'));

        $this->assertSame(DirectorSalesData::REVENUE, $revenueTotal);
        $this->assertSame($revenueTotal, $portfolio['total_revenue']);
        $this->assertSame(DirectorSalesData::GROSS, $grossTotal);
        $this->assertSame($grossTotal, $portfolio['total_gross']);
        $this->assertSame($volumeTotal, $portfolio['total_volume']);
        $this->assertSame(round($grossTotal / $revenueTotal * 100, 1), $portfolio['total_margin']);
        $this->assertSame(21.8, $portfolio['total_margin']);
        $this->assertSame(100.0, $portfolio['total_share']);
        $this->assertSame(
            100.0,
            round(array_sum(array_column($rows, 'share')), 1),
            'Kontribusi omzet lima komoditas harus utuh 100.0%.',
        );
    }

    public function test_portfolio_rows_respect_margin_floor_and_volume_identity(): void
    {
        $portfolio = DirectorSalesData::portfolio();

        foreach ($portfolio['rows'] as $row) {
            $this->assertGreaterThanOrEqual(DirectorSalesData::MARGIN_FLOOR, $row['margin'], $row['sku'].' di bawah margin floor.');
            $this->assertSame(
                round($row['gross'] / $row['revenue'] * 100, 1),
                $row['margin'],
                'Margin baris harus diturunkan dari gross profit dan omzetnya.',
            );
            $this->assertSame((int) round($row['revenue'] / $row['price']), $row['volume']);
            $this->assertGreaterThan(0, $row['volume']);
        }

        $statuses = array_unique(array_column($portfolio['rows'], 'status'));

        foreach (['PRIMA', 'NOMINAL', 'STABIL', 'TIGHT'] as $expected) {
            $this->assertContains($expected, $statuses);
        }

        $this->assertSame(
            'TIGHT',
            collect($portfolio['rows'])->sortBy('margin')->first()['status'],
            'Komoditas dengan margin terendah harus berstatus TIGHT.',
        );
    }

    public function test_settlement_methods_reconcile_with_cash_inflow(): void
    {
        $settlement = DirectorSalesData::settlement();

        $response = $this->get(route('director.sales'));

        $response->assertOk();
        $response->assertSee($settlement['badge']);
        $response->assertSee($settlement['title']);
        $response->assertSee($settlement['subtitle']);
        $response->assertSee($settlement['total_label']);
        $response->assertSee($settlement['seal']['title']);
        $response->assertSee($settlement['seal']['note']);
        $response->assertSee($settlement['seal']['chip']);
        $response->assertSee($settlement['seal']['meta']);

        foreach ($settlement['rows'] as $row) {
            $response->assertSee($row['name']);
            $response->assertSee($row['value_label']);
            $response->assertSee($row['share_label']);
            $response->assertSee($row['note']);
        }

        $this->assertSame(DirectorSalesData::REVENUE, $settlement['total']);
        $this->assertSame(100, $settlement['total_share']);

        foreach ($settlement['rows'] as $row) {
            $this->assertSame((int) round($row['value'] / $settlement['total'] * 100, 0), $row['share']);
        }
    }

    public function test_every_section_shares_one_revenue_baseline(): void
    {
        $scopes = DirectorSalesData::kpiScopes();
        $trend = DirectorSalesData::weeklyTrend();
        $channels = DirectorSalesData::channels();
        $portfolio = DirectorSalesData::portfolio();
        $settlement = DirectorSalesData::settlement();

        $mtdRevenue = collect($scopes['MTD']['cards'])->firstWhere('key', 'revenue')['value'];
        $mtdGross = collect($scopes['MTD']['cards'])->firstWhere('key', 'margin')['footer'][1]['text'];

        $this->assertSame('Rp '.number_format(DirectorSalesData::REVENUE, 0, '.', '.'), $mtdRevenue);
        $this->assertSame(DirectorSalesData::REVENUE, $trend['total']);
        $this->assertSame(DirectorSalesData::REVENUE, $channels['total']);
        $this->assertSame(DirectorSalesData::REVENUE, $portfolio['total_revenue']);
        $this->assertSame(DirectorSalesData::REVENUE, $settlement['total']);
        $this->assertSame(
            'Rp '.number_format(DirectorSalesData::GROSS, 0, '.', '.'),
            $mtdGross,
            'Laba kotor pada kartu RATA-RATA harus sama dengan total tabel portofolio.',
        );

        $response = $this->get(route('director.sales'));

        $response->assertOk();
        $response->assertSee('Konsolidasi 3 Kanal: Rp 482.650.000');
        $response->assertSee('TOTAL CASH INFLOW: Rp 482.650.000');
    }

    public function test_navigation_marks_sales_as_the_active_module(): void
    {
        $response = $this->get(route('director.sales'));

        $response->assertOk();
        $response->assertSee(route('director.sales'), false);
        $response->assertSee('Penjualan');
        $response->assertSee('Dashboard');
        $this->assertSame(
            1,
            substr_count((string) $response->getContent(), 'aria-current="page"'),
            'Hanya modul Penjualan yang boleh ditandai aktif pada halaman ini.',
        );
    }

    public function test_alpine_component_is_mounted_with_reconciled_payload(): void
    {
        $trend = DirectorSalesData::weeklyTrend();
        $portfolio = DirectorSalesData::portfolio();

        $response = $this->get(route('director.sales'));

        $response->assertOk();
        $response->assertSee('x-data="directorSales(', false);
        $this->assertJsPayload($response, ['MTD', 'WTD', 'YTD']);
        $this->assertJsPayload($response, $trend['rows']);
        $this->assertJsPayload($response, $portfolio['rows']);
    }
}
