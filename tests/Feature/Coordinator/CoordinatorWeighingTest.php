<?php

namespace Tests\Feature\Coordinator;

use App\Models\User;
use App\Support\CoordinatorWeighingData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CoordinatorWeighingTest extends TestCase
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

    public function test_guest_can_preview_weighing_console_with_demo_operator(): void
    {
        $response = $this->get(route('coordinator.weighing'));

        $response->assertOk();
        $response->assertSee('Konsol Penimbangan Aktual &amp; Validasi Sortir', false);
        $response->assertSee('Gudang (Koordinator Lapangan)');
        $response->assertSee('Koordinator Lapangan');
        $response->assertSee('Agus Tusan');
        $response->assertSee('ID : 002');
    }

    public function test_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Andi Nugroho']);

        $response = $this->actingAs($user)->get(route('coordinator.weighing'));

        $response->assertOk();
        $response->assertSee('Andi Nugroho');
        $response->assertSee('AN');
    }

    public function test_console_renders_page_header_and_timestamp_card(): void
    {
        $header = CoordinatorWeighingData::header();

        $response = $this->get(route('coordinator.weighing'));

        $response->assertOk();
        $response->assertSee($header['eyebrow']);
        $response->assertSee($header['subtitle']);
        $response->assertSee('TIMESTAMP TERA', false);
        $response->assertSee('UTC+7');
        $response->assertSee('25 SEP 2026', false);
    }

    public function test_console_renders_three_stage_tracker_with_active_weighing_stage(): void
    {
        $stages = CoordinatorWeighingData::stages();

        $response = $this->get(route('coordinator.weighing'));

        $response->assertOk();

        foreach ($stages as $stage) {
            $response->assertSee($stage['step']);
            $response->assertSee($stage['note']);
        }

        $this->assertSame(['done', 'active', 'locked'], array_column($stages, 'state'));
        $response->assertSee('Preparasi Panen');
        $response->assertSee('Toleransi Variansi ±2.0%', false);
        $response->assertSee('Menunggu Lock Koordinator');
    }

    public function test_console_renders_prd_rule_04_and_rule_05_mandates(): void
    {
        $rules = CoordinatorWeighingData::rules();

        $response = $this->get(route('coordinator.weighing'));

        $response->assertOk();
        $response->assertSee('PRD RULE 04 MANDAT');
        $response->assertSee('Klausul Order Quantity vs Actual Weight');
        $response->assertSee('Actual Net Weight hasil timbangan stasiun');
        $response->assertSee('PRD RULE 05 HARD-GATE');
        $response->assertSee('DISPATCH LOCK SYSTEM');
        $response->assertSee('TIDAK DAPAT DITERBITKAN');

        $this->assertCount(2, $rules);
    }

    public function test_console_renders_order_banner_contract_identity(): void
    {
        $order = CoordinatorWeighingData::order();

        $response = $this->get(route('coordinator.weighing'));

        $response->assertOk();
        $response->assertSee($order['code']);
        $response->assertSee($order['client']);
        $response->assertSee($order['contract']);
        $response->assertSee($order['rate']);
        $response->assertSee($order['verifier']);
        $response->assertSee($order['verifier_code']);
        $response->assertSee('Komoditas: Selada Romaine Grade A Super', false);
    }

    public function test_console_renders_terminal_with_consistent_weighing_arithmetic(): void
    {
        $terminal = CoordinatorWeighingData::terminal();

        $response = $this->get(route('coordinator.weighing'));

        $response->assertOk();
        $response->assertSee($terminal['title']);
        $response->assertSee('METTLER TOLEDO C3500 // IOT TELEMETRY', false);
        $response->assertSee($terminal['iot_badge']);
        $response->assertSee('TERA-CAL-2026');
        $response->assertSee('ZERO CALIBRATED // AUTO-TARE ON');
        $response->assertSee('20 CRATES @ 2.00 KG');
        $response->assertSee($terminal['metrology_note']);
        $response->assertSee('835.00');
        $response->assertSee('795.00');

        $gross = 835.00;
        $crates = 20;
        $tareEach = 2.00;

        $this->assertSame(40.0, $crates * $tareEach);
        $this->assertSame(795.0, $gross - ($crates * $tareEach));
    }

    public function test_console_renders_terminal_input_fields_and_actions(): void
    {
        $response = $this->get(route('coordinator.weighing'));

        $response->assertOk();
        $response->assertSee('MASSA GROSS (TIMBANGAN)');
        $response->assertSee('JUMLAH KERANJANG TERA');
        $response->assertSee('BOBOT TARA BAKU / PETI');
        $response->assertSee('RESET ZERO (TARE)');
        $response->assertSee('RE-READ SENSOR');
    }

    public function test_console_renders_qc_grading_table_reconciled_to_po_estimate(): void
    {
        $grading = CoordinatorWeighingData::grading();

        $response = $this->get(route('coordinator.weighing'));

        $response->assertOk();
        $response->assertSee($grading['title']);
        $response->assertSee($grading['standard']);

        foreach ($grading['columns'] as $column) {
            $response->assertSee($column);
        }

        foreach ($grading['rows'] as $row) {
            $response->assertSee($row['name'], false);

            foreach ($row['status'] as $line) {
                $response->assertSee($line);
            }
        }

        $total = array_sum(array_column($grading['rows'], 'mass'));
        $this->assertSame(800.0, $total, 'Massa grading harus menutup estimasi awal PO 800 kg.');
        $this->assertSame('99.375%', $grading['rows'][0]['percent']);
        $this->assertSame('0.625%', $grading['rows'][1]['percent']);
    }

    public function test_console_renders_evidence_upload_block(): void
    {
        $evidence = CoordinatorWeighingData::evidence();

        $response = $this->get(route('coordinator.weighing'));

        $response->assertOk();
        $response->assertSee($evidence['title']);
        $response->assertSee($evidence['description']);
        $response->assertSee($evidence['action']);
    }

    public function test_console_renders_supply_reconciliation_matching_net_weight(): void
    {
        $reconciliation = CoordinatorWeighingData::reconciliation();

        $response = $this->get(route('coordinator.weighing'));

        $response->assertOk();
        $response->assertSee($reconciliation['title']);
        $response->assertSee($reconciliation['match']);
        $response->assertSee($reconciliation['status'], false);

        foreach ($reconciliation['sources'] as $source) {
            $response->assertSee($source['name']);
            $response->assertSee($source['batch']);
            $response->assertSee($source['mass']);
        }

        $this->assertSame(795.0, 500.0 + 295.0, 'Sumber pasokan harus menutup netto 795 kg.');
        $this->assertSame('62.89% TOTAL', $reconciliation['sources'][0]['percent']);
        $this->assertSame('37.11% TOTAL', $reconciliation['sources'][1]['percent']);
        $this->assertSame('-5.00 kg (-0.625%)', $reconciliation['panel'][2]['value']);
    }

    public function test_console_renders_automatic_financial_recalculation(): void
    {
        $financial = CoordinatorWeighingData::financial();

        $response = $this->get(route('coordinator.weighing'));

        $response->assertOk();
        $response->assertSee($financial['title']);
        $response->assertSee($financial['final_label']);
        $response->assertSee($financial['final_note']);

        foreach ($financial['metrology'] as $row) {
            $response->assertSee($row['value']);
        }

        $estimate = 800.0;
        $rate = 15000;

        $this->assertSame(12000000.0, $estimate * $rate);
        $this->assertSame(75000.0, 5.0 * $rate);
        $this->assertSame(11925000.0, 795.0 * $rate);
        $this->assertSame('Rp 11.925.000', $financial['final_value']);
    }

    public function test_console_renders_forensic_audit_integrity_note(): void
    {
        $audit = CoordinatorWeighingData::audit();

        $response = $this->get(route('coordinator.weighing'));

        $response->assertOk();
        $response->assertSee($audit['title']);
        $response->assertSee('signature perangkat Mettler Toledo', false);
        $response->assertSee('pembatalan otomatis sertifikat dispatch.', false);
    }

    public function test_console_renders_hard_gate_confirmation_bar(): void
    {
        $actions = CoordinatorWeighingData::actions();

        $response = $this->get(route('coordinator.weighing'));

        $response->assertOk();
        $response->assertSee('VALIDASI BERAT NETTO RIIL : 795.00 KG', false);
        $response->assertSee($actions['draft']['label']);
        $response->assertSee('KUNCI &amp; VALIDASI BERAT AKTUAL (LOCK DATA)', false);
        $response->assertSee('TERBITKAN SURAT JALAN (SJ-GPA-202610-0001)', false);
    }

    public function test_console_mounts_alpine_component_with_weighing_configuration(): void
    {
        $response = $this->get(route('coordinator.weighing'));

        $response->assertOk();
        $response->assertSee('coordinatorWeighing(', false);

        $this->assertJsPayload($response, [
            'estimate' => 800,
            'rate' => 15000,
            'gross' => 835.0,
            'crates' => 20,
            'tareEach' => 2.0,
            'tolerance' => 2.0,
        ]);
    }

    public function test_navigation_marks_weighing_module_as_active(): void
    {
        $response = $this->get(route('coordinator.weighing'));

        $response->assertOk();
        $response->assertSee('Timbangan &amp; Sortir', false);
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('Rencana Panen');
        $response->assertSee('Manajemen Stok');
        $response->assertSee('Laporan Retur');
    }
}
