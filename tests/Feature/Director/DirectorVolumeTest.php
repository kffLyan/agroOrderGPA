<?php

namespace Tests\Feature\Director;

use App\Models\User;
use App\Support\DirectorVolumeData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class DirectorVolumeTest extends TestCase
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

    public function test_guest_can_preview_volume_console_with_demo_operator(): void
    {
        $response = $this->get(route('director.volume'));

        $response->assertOk();
        $response->assertSee('Direktur');
        $response->assertSee('Asep Tember');
        $response->assertSee('ID : 001');
    }

    public function test_volume_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Dewi Kartika']);

        $response = $this->actingAs($user)->get(route('director.volume'));

        $response->assertOk();
        $response->assertSee('Dewi Kartika');
        $response->assertSee('DK');
    }

    public function test_page_header_exposes_range_badge_and_reconciliation_action(): void
    {
        $header = DirectorVolumeData::header();

        $response = $this->get(route('director.volume'));

        $response->assertOk();
        $response->assertSee('Monitoring Volume Komoditas, Stok Panen');
        $response->assertSee('&amp; Kapasitas Pasokan', false);
        $response->assertSee($header['subtitle']);
        $response->assertSee($header['range_label']);
        $response->assertSee($header['range_value']);
        $response->assertSee($header['action_label']);
        $response->assertSee($header['refresh_label']);
    }

    public function test_kpi_band_renders_four_reconciled_cards(): void
    {
        $cards = DirectorVolumeData::kpiCards();

        $response = $this->get(route('director.volume'));

        $response->assertOk();
        $this->assertCount(4, $cards);

        foreach ($cards as $card) {
            $response->assertSee($card['label']);
            $response->assertSee($card['footer']['label']);
            $response->assertSee($card['footer']['chip']);

            foreach ($card['value_parts'] as $part) {
                $response->assertSee($part['text']);
            }

            foreach ($card['notes'] as $note) {
                $response->assertSee($note['text']);
            }
        }

        $byKey = collect($cards)->keyBy('key');

        $this->assertSame('482.65', $byKey['volume']['value']);
        $this->assertSame('114.9%', $byKey['volume']['footer']['chip']);
        $this->assertSame(
            ['82%', 'Binaan', ' : 18%', 'Buffer'],
            array_column($byKey['sourcing']['value_parts'], 'text'),
            'Nilai kartu pasokan dirakit dari bagian terpisah, bukan string utuh.',
        );
        $this->assertSame('82% : 18%', $byKey['sourcing']['value']);
        $this->assertSame('0.7%', $byKey['shrinkage']['value']);
        $this->assertSame('11.20', $byKey['safety']['value']);
    }

    public function test_commodity_cards_render_code_name_grade_and_status(): void
    {
        $commodities = DirectorVolumeData::commodities();

        $response = $this->get(route('director.volume'));

        $response->assertOk();
        $response->assertSee($commodities['title']);
        $response->assertSee($commodities['subtitle']);
        $this->assertCount(5, $commodities['rows']);

        foreach ($commodities['rows'] as $row) {
            $response->assertSee($row['code']);
            $response->assertSee($row['name']);
            $response->assertSee($row['grade']);
            $response->assertSee($row['value_label']);
            $response->assertSee($row['farmer_label']);
            $response->assertSee($row['buffer_label']);
            $response->assertSee($row['headroom_label']);
            $response->assertSee($row['share_label']);
            $response->assertSee($row['status']);
        }
    }

    public function test_commodity_rows_reconcile_with_the_module_volume_baseline(): void
    {
        $commodities = DirectorVolumeData::commodities();
        $rows = $commodities['rows'];

        $this->assertSame(
            DirectorVolumeData::TOTAL_VOLUME,
            round(array_sum(array_column($rows, 'value')), 2),
            'Total lima kartu komoditas harus sama dengan baseline modul.',
        );
        $this->assertSame(DirectorVolumeData::TOTAL_VOLUME, $commodities['total']);
        $this->assertSame(DirectorVolumeData::FARMER_VOLUME, $commodities['farmer_total']);
        $this->assertSame(DirectorVolumeData::BUFFER_VOLUME, $commodities['buffer_total']);

        foreach ($rows as $row) {
            $this->assertSame(
                $row['value'],
                round($row['farmer'] + $row['buffer'], 2),
                $row['code'].' harus terbagi penuh antara dua sumber pasokan.',
            );
            $this->assertSame(
                round($row['value'] / $commodities['total'] * 100, 1),
                $row['share_percent'],
            );
            $this->assertSame(
                round($row['buffer'] / $row['value'] * 100, 1),
                $row['buffer_percent'],
            );
            $this->assertGreaterThan(0, $row['headroom']);
        }

        $this->assertSame(82.0, $commodities['farmer_percent']);
        $this->assertSame(18.0, $commodities['buffer_percent']);
        $this->assertSame(
            100,
            array_sum(array_column($rows, 'share')),
            'Porsi alokasi lima komoditas harus utuh 100%.',
        );
    }

    public function test_commodity_rows_flag_tight_headroom_in_strawberry(): void
    {
        $rows = DirectorVolumeData::commodities()['rows'];

        $tightest = collect($rows)->sortBy('headroom')->first();

        $this->assertSame('KMD-04', $tightest['code']);
        $this->assertSame('KETAT (HIGH DEMAND)', $tightest['status']);
        $this->assertSame('danger', $tightest['headroom_tone']);

        $statuses = array_unique(array_column($rows, 'status'));

        foreach (['PASOKAN STABIL', 'KUOTA TERBATAS', 'KETAT (HIGH DEMAND)', 'MELIMPAH (SAFE)'] as $expected) {
            $this->assertContains($expected, $statuses);
        }
    }

    public function test_weekly_chart_segments_sum_to_each_week_total(): void
    {
        $trend = DirectorVolumeData::weeklyTrend();

        $response = $this->get(route('director.volume'));

        $response->assertOk();
        $response->assertSee($trend['title']);
        $response->assertSee($trend['subtitle']);
        $response->assertSee($trend['peak_label']);
        $response->assertSee($trend['metrology']);
        $response->assertSee($trend['confidence_label']);
        $this->assertCount(8, $trend['rows']);

        foreach ($trend['legend'] as $item) {
            $response->assertSee($item['label']);
        }

        foreach ($trend['rows'] as $row) {
            $response->assertSee($row['week']);
            $response->assertSee($row['label']);
            $response->assertSee($row['total_label']);
            $response->assertSee('style="height: '.$row['bar_percent'].'%"', false);

            $segmentTotal = round(array_sum(array_column($row['segments'], 'tons')), 2);

            $this->assertSame(
                $row['total'],
                $segmentTotal,
                $row['week'].' harus tersusun dari demand, yield, dan reserve yang utuh.',
            );
            $this->assertCount(3, $row['segments']);
            $this->assertSame(
                $row['bar_percent'],
                round($row['total'] / $trend['max'] * 100, 1),
            );
            $this->assertLessThanOrEqual(100, $row['bar_percent']);
        }
    }

    public function test_weekly_chart_marks_running_and_forecast_weeks(): void
    {
        $trend = DirectorVolumeData::weeklyTrend();

        $this->assertSame(
            'W44',
            collect($trend['rows'])->firstWhere('status', 'running')['week'],
            'Minggu berjalan harus ditandai pada W44 sesuai periode Oktober 2026.',
        );
        $this->assertSame(
            'W44',
            collect($trend['rows'])->firstWhere('is_peak', true)['week'],
            'Bar tertinggi harus menandai minggu dengan volume tertinggi.',
        );

        $forecast = collect($trend['rows'])->where('status', 'forecast');

        $this->assertCount(3, $forecast);
        $this->assertSame(
            round($forecast->sum('total'), 1),
            $trend['forecast_total'],
        );
        $this->assertSame(
            round(collect($trend['rows'])->where('status', '!=', 'forecast')->sum('total'), 1),
            $trend['actual_total'],
        );

        foreach ($forecast as $row) {
            $this->assertStringStartsWith('~', $row['total_label']);
            $this->assertStringContainsString('(EST)', $row['label']);
        }
    }

    public function test_allocation_matrix_totals_match_the_sum_of_partner_commitments(): void
    {
        $allocations = DirectorVolumeData::allocations();

        $response = $this->get(route('director.volume'));

        $response->assertOk();
        $response->assertSee($allocations['badge']);
        $response->assertSee($allocations['title']);
        $response->assertSee($allocations['subtitle']);
        $response->assertSee($allocations['total_label']);
        $response->assertSee($allocations['total_value_label']);
        $response->assertSee($allocations['serap_label']);
        $this->assertCount(4, $allocations['rows']);

        foreach ($allocations['rows'] as $row) {
            $response->assertSee($row['name']);
            $response->assertSee($row['sentra']);
            $response->assertSee($row['commitment_label']);
            $response->assertSee($row['priority']);
        }

        $this->assertSame(
            $allocations['total'],
            round(array_sum(array_column($allocations['rows'], 'commitment')), 2),
        );
        $this->assertSame(
            $allocations['serap_percent'],
            round($allocations['total'] / $allocations['capacity'] * 100, 1),
        );
        $this->assertSame(
            $allocations['serap_percent'].'% SERAP',
            $allocations['serap_label'],
            'Label serap harus diturunkan dari rasio komitmen terhadap kapasitas.',
        );
        $this->assertLessThanOrEqual(100, $allocations['serap_percent']);
    }

    public function test_every_section_shares_one_volume_baseline(): void
    {
        $cards = DirectorVolumeData::kpiCards();
        $commodities = DirectorVolumeData::commodities();
        $reconciliation = DirectorVolumeData::reconciliation();

        $volumeCard = collect($cards)->firstWhere('key', 'volume');
        $sourcingCard = collect($cards)->firstWhere('key', 'sourcing');

        $this->assertSame(DirectorVolumeData::TOTAL_VOLUME, $commodities['total']);
        $this->assertSame(DirectorVolumeData::TOTAL_VOLUME, $reconciliation['total']);
        $this->assertSame(
            DirectorVolumeData::FARMER_VOLUME + DirectorVolumeData::BUFFER_VOLUME,
            DirectorVolumeData::TOTAL_VOLUME,
            'Porsi supplying harus menutup seluruh volume MTD tanpa selisih.',
        );

        $this->assertSame(DirectorVolumeData::FARMER_VOLUME, $commodities['farmer_total']);
        $this->assertSame(DirectorVolumeData::BUFFER_VOLUME, $commodities['buffer_total']);
        $this->assertSame(DirectorVolumeData::FARMER_SHARE, $commodities['farmer_percent']);
        $this->assertSame(DirectorVolumeData::BUFFER_SHARE, $commodities['buffer_percent']);

        $this->assertSame(
            round(DirectorVolumeData::TOTAL_VOLUME / DirectorVolumeData::QUARTER_TARGET * 100, 1),
            $reconciliation['target_percent'],
        );
        $this->assertSame(114.9, $reconciliation['target_percent']);
        $this->assertSame(
            $reconciliation['target_percent'].'%',
            $volumeCard['footer']['chip'],
            'Capaian target pada kartu volume harus berasal dari baseline yang sama.',
        );

        $this->assertContains(
            'Binaan: '.DirectorVolumeData::FARMER_VOLUME.' T',
            array_column($sourcingCard['notes'], 'text'),
        );
        $this->assertContains(
            'Buffer: '.DirectorVolumeData::BUFFER_VOLUME.' T',
            array_column($sourcingCard['notes'], 'text'),
        );

        $this->assertLessThan(DirectorVolumeData::SHRINKAGE_CEILING, DirectorVolumeData::SHRINKAGE);
    }

    public function test_reconciliation_labels_reuse_the_shared_tonase_format(): void
    {
        $reconciliation = DirectorVolumeData::reconciliation();

        $this->assertSame('482.65 Ton', $reconciliation['volume_label']);
        $this->assertSame('395.77 Ton', $reconciliation['farmer_label']);
        $this->assertSame('86.88 Ton', $reconciliation['buffer_label']);
    }

    public function test_navigation_marks_volume_as_the_active_module(): void
    {
        $response = $this->get(route('director.volume'));

        $response->assertOk();
        $response->assertSee(route('director.volume'), false);
        $response->assertSee('Volume Komoditas');
        $response->assertSee('Penjualan');
        $response->assertSee('Dashboard');
        $this->assertSame(
            1,
            substr_count((string) $response->getContent(), 'aria-current="page"'),
            'Hanya modul Volume Komoditas yang boleh ditandai aktif pada halaman ini.',
        );
    }

    public function test_alpine_component_is_mounted_with_reconciled_payload(): void
    {
        $commodities = DirectorVolumeData::commodities();
        $trend = DirectorVolumeData::weeklyTrend();
        $allocations = DirectorVolumeData::allocations();

        $response = $this->get(route('director.volume'));

        $response->assertOk();
        $response->assertSee('x-data="directorVolume(', false);
        $this->assertJsPayload($response, $commodities['rows']);
        $this->assertJsPayload($response, $trend['rows']);
        $this->assertJsPayload($response, $allocations['rows']);
    }
}
