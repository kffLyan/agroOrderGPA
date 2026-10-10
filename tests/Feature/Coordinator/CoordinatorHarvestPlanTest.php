<?php

namespace Tests\Feature\Coordinator;

use App\Models\User;
use App\Support\CoordinatorHarvestPlanData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CoordinatorHarvestPlanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Data Alpine ditulis sebagai literal JSON di dalam `x-data`, sehingga nilai
     * yang hanya hidup di payload dicocokkan lewat ekspresi `Js::from()`.
     */
    private function assertJsPayload(TestResponse $response, mixed $payload): void
    {
        $response->assertSee((string) Js::from($payload), false);
    }

    public function test_guest_can_preview_harvest_plan_console_with_demo_operator(): void
    {
        $response = $this->get(route('coordinator.harvest'));

        $response->assertOk();
        $response->assertSee('Rencana Panen & Persiapan Pesanan');
        $response->assertSee('Koordinator Lapangan');
        $response->assertSee('Agus Tusan');
        $response->assertSee('ID : 002');
    }

    public function test_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Andi Nugroho']);

        $response = $this->actingAs($user)->get(route('coordinator.harvest'));

        $response->assertOk();
        $response->assertSee('Andi Nugroho');
        $response->assertSee('AN');
    }

    public function test_sidebar_keeps_rencana_panen_navigation_active(): void
    {
        $response = $this->get(route('coordinator.harvest'));

        $response->assertOk();
        $response->assertSee('aria-current="page"', false);
        $response->assertSee(route('coordinator.harvest'), false);
        $this->assertSame(
            1,
            substr_count($response->getContent(), 'aria-current="page"'),
            'Hanya satu item sidebar yang boleh aktif.',
        );
    }

    public function test_console_renders_rule_04_and_09_mandate_with_four_stages(): void
    {
        $mandate = CoordinatorHarvestPlanData::mandate();

        $this->assertCount(4, $mandate['stages']);

        $response = $this->get(route('coordinator.harvest'));

        $response->assertOk();
        $response->assertSee($mandate['title']);
        $response->assertSee($mandate['badge']);

        foreach ($mandate['stages'] as $stage) {
            $response->assertSee($stage['stage']);
            $response->assertSee($stage['title']);
            $response->assertSee($stage['body']);
            $response->assertSee($stage['sop']);
            $response->assertSee($stage['tag']);
        }
    }

    public function test_console_renders_four_harvest_plan_kpi_cards(): void
    {
        $response = $this->get(route('coordinator.harvest'));

        $response->assertOk();
        $response->assertSee('TOTAL PO DALAM PACKING');
        $response->assertSee('Batch Aktif');
        $response->assertSee('Target Hari Ini: 1.840,00 kg');
        $response->assertSee('SELESAI SORTIR & PACKING');
        $response->assertSee('Selesai (71%)');
        $response->assertSee('1.290 kg menuju antrean timbang');
        $response->assertSee('KETERSEDIAAN KRAT STERIL');
        $response->assertSee('/ 350 Krat');
        $response->assertSee('Staging Bay A');
        $response->assertSee('SUHU RUANG PRE-COOLING');
        $response->assertSee("+3.8\u{00B0}C");
        $response->assertSee("ISO 22000 Standard (+2\u{00B0}C ~ +5\u{00B0}C)");
        $response->assertSee('NORMAL');
    }

    public function test_console_renders_harvest_plan_queue_with_stage_and_afkir_markers(): void
    {
        $queue = CoordinatorHarvestPlanData::queue();

        $this->assertCount(4, $queue['rows']);

        $response = $this->get(route('coordinator.harvest'));

        $response->assertOk();
        $response->assertSee($queue['title']);
        $response->assertSee($queue['subtitle']);
        $response->assertSee($queue['badge']);
        $response->assertSee($queue['footer_left']);
        $response->assertSee($queue['footer_right']);

        foreach ($queue['columns'] as $column) {
            $response->assertSee($column);
        }

        foreach ($queue['rows'] as $row) {
            $response->assertSee('#'.$row['po']);
            $response->assertSee($row['bay'].' // '.$row['station']);
            $response->assertSee($row['buyer']);
            $response->assertSee($queue['stages'][$row['stage']]['chip']);
            $response->assertSee($row['krat_done'].'/'.$row['krat_total'].' Krat');
            $response->assertSee('Est: '.number_format($row['est_kg'], 2, '.', ',').' kg');

            $this->assertJsPayload($response, $row['po']);
        }

        $response->assertSee('Selada Romaine [Grade A]');
        $response->assertSee('Afkir 1.2% (Aman)');
    }

    public function test_queue_estimates_reconcile_with_daily_weight_target(): void
    {
        $queue = CoordinatorHarvestPlanData::queue();

        $estimated = array_sum(array_column($queue['rows'], 'est_kg'));

        $this->assertSame(1840.0, $estimated, 'Empat batch packing harus menutup target harian 1.840,00 KG.');
        $this->assertSame($queue['weight_target'], $estimated);
        $this->assertSame(7, $queue['total_batches']);
        $this->assertSame(5, $queue['done_batches']);
        $this->assertLessThan(
            $queue['total_batches'],
            $queue['done_batches'],
            'Batch selesai tidak boleh melebihi total batch packing.',
        );
    }

    public function test_console_renders_active_workstation_detail(): void
    {
        $station = CoordinatorHarvestPlanData::station();
        $active = CoordinatorHarvestPlanData::queue()['rows'][0];

        $response = $this->get(route('coordinator.harvest'));

        $response->assertOk();
        $response->assertSee($station['eyebrow']);
        $response->assertSee('WORKSTATION MEJA #02', false);
        $response->assertSee($active['buyer']);
        $response->assertSee('Komoditas: Selada Romaine [Grade A]');
        $response->assertSee('Target: '.$active['krat_total'].' Krat / 800.00 kg Gross Target');

        foreach ($station['steps'] as $step) {
            $response->assertSee($step['title']);
            $response->assertSee($step['chip']);

            foreach ($step['notes'] as $note) {
                $response->assertSee($note);
            }
        }

        $response->assertSee($station['barcode_prefix'].'-0042-', false);
        $response->assertSee('Bebas residu jamur/tanah');
        $response->assertSee('Disinfektan klorin 50ppm');
        $response->assertSee('PALLET #01');
        $response->assertSee('PALLET #02');
        $response->assertSee('20 Krat (Tier 4x5)');
        $response->assertSee($station['qc_note']['label']);
        $response->assertSee($station['qc_note']['text']);
    }

    public function test_console_renders_workstation_actions_and_transfer_cta(): void
    {
        $station = CoordinatorHarvestPlanData::station();

        $response = $this->get(route('coordinator.harvest'));

        $response->assertOk();

        foreach ($station['buttons'] as $button) {
            $response->assertSee($button['label']);
            $this->assertJsPayload($response, $button['key']);
        }

        $response->assertSee($station['cta']['label']);
        $this->assertJsPayload($response, 'transfer');
    }

    public function test_console_renders_qc_sampling_log_with_validated_net_totals(): void
    {
        $qc = CoordinatorHarvestPlanData::qc();

        $this->assertCount(3, $qc['samples']);

        $response = $this->get(route('coordinator.harvest'));

        $response->assertOk();
        $response->assertSee($qc['title']);
        $response->assertSee($qc['subtitle']);
        $response->assertSee($qc['avg_label']);
        $response->assertSee($qc['avg_value']);
        $response->assertSee($qc['avg_note']);
        $response->assertSee($qc['inspector']);
        $response->assertSee($qc['sensor_note']);

        foreach ($qc['samples'] as $sample) {
            $response->assertSee($sample['krat']);
            $response->assertSee($sample['status']);
            $response->assertSee(number_format($sample['gross'], 2, '.', ',').' kg');
            $response->assertSee(number_format($sample['tara'], 2, '.', ',').' kg');
            $response->assertSee(number_format($sample['trim'], 2, '.', ',').' kg ('.number_format($sample['trim_percent'], 2, '.', ',').'%)');
            $response->assertSee(number_format($sample['gross'] - $sample['tara'] - $sample['trim'], 2, '.', ',').' kg');
        }
    }

    public function test_qc_samples_reconcile_with_sop_tolerance(): void
    {
        $qc = CoordinatorHarvestPlanData::qc();

        foreach ($qc['samples'] as $sample) {
            $this->assertEqualsWithDelta(
                $sample['net'],
                $sample['gross'] - $sample['tara'] - $sample['trim'],
                0.0001,
                'Netto bersih harus sama dengan bruto dikurangi tera dan afkir untuk '.$sample['krat'],
            );

            $base = $sample['gross'] - $sample['tara'];
            $expectedPercent = floor(($sample['trim'] / $base) * 10000) / 100;

            $this->assertEqualsWithDelta($expectedPercent, $sample['trim_percent'], 0.0001);
            $this->assertLessThanOrEqual($qc['tolerance'], $sample['trim_percent']);
            $this->assertGreaterThan(0, $sample['net']);
        }

        $average = round(
            array_sum(array_column($qc['samples'], 'trim_percent')) / count($qc['samples']),
            2,
        );

        $this->assertSame(0.89, $average);
        $this->assertSame($qc['avg_value'], number_format($average, 2, '.', '').'%');
        $this->assertLessThanOrEqual($qc['tolerance'], $average);
    }

    public function test_alpine_payload_exposes_rows_stages_station_and_qc_context(): void
    {
        $harvestPlan = CoordinatorHarvestPlanData::for();
        $queue = $harvestPlan['queue'];

        $response = $this->get(route('coordinator.harvest'));

        $response->assertOk();
        $this->assertJsPayload($response, $queue['rows']);
        $this->assertJsPayload($response, $queue['stages']);
        $this->assertJsPayload($response, $harvestPlan['station']);
        $this->assertJsPayload($response, $harvestPlan['qc']);

        $response->assertSee('coordinatorHarvestPlan(', false);
        $this->assertJsPayload($response, ['totalBatches' => 7, 'baseDone' => 4, 'baseWeighKg' => 240]);
    }
}
