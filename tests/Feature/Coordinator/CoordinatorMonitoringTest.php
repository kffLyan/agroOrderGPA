<?php

namespace Tests\Feature\Coordinator;

use App\Models\User;
use App\Support\CoordinatorMonitoringData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CoordinatorMonitoringTest extends TestCase
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

    public function test_guest_can_preview_monitoring_console_with_demo_operator(): void
    {
        $response = $this->get(route('coordinator.monitoring'));

        $response->assertOk();
        $response->assertSee('Monitoring &amp; Validasi Proof of Delivery (PoD)', false);
        $response->assertSee('Koordinator Lapangan');
        $response->assertSee('Agus Tusan');
        $response->assertSee('ID : 002');
    }

    public function test_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Andi Nugroho']);

        $response = $this->actingAs($user)->get(route('coordinator.monitoring'));

        $response->assertOk();
        $response->assertSee('Andi Nugroho');
        $response->assertSee('AN');
    }

    public function test_sidebar_activates_monitoring_navigation(): void
    {
        $response = $this->get(route('coordinator.monitoring'));

        $response->assertOk();
        $this->assertMatchesRegularExpression(
            '/<a href="'.preg_quote(route('coordinator.monitoring'), '/').'"[^>]*aria-current="page"/',
            $response->getContent(),
        );
    }

    public function test_console_renders_rule_06_and_12_hard_gate_mandate(): void
    {
        $mandate = CoordinatorMonitoringData::mandate();

        $response = $this->get(route('coordinator.monitoring'));

        $response->assertOk();
        $response->assertSee($mandate['title']);
        $response->assertSee($mandate['badge']);
        $response->assertSee($mandate['protocol_label']);
        $response->assertSee($mandate['protocol_value']);

        $this->assertStringContainsString('dilarang ditutup menjadi "Selesai" tanpa bukti PoD', $mandate['body']);
        $this->assertStringContainsString('stempel basah penerima', $mandate['body']);
        $this->assertStringContainsString('BA Retur Seketika', $mandate['body']);

        $this->assertSame(
            $mandate['body'],
            $mandate['body_lead'].' '.$mandate['body_emphasis'].' '.$mandate['body_tail'],
        );

        $response->assertSee($mandate['body_lead']);
        $response->assertSee($mandate['body_emphasis']);
        $response->assertSee($mandate['body_tail']);
        $response->assertSee('<strong class="font-semibold text-ink">&quot;Selesai&quot;</strong>', false);
    }

    public function test_console_renders_pod_verification_dossier_as_monitoring_work_queue(): void
    {
        $response = $this->get(route('coordinator.monitoring'));

        $response->assertOk();
        $response->assertSee('Verifikasi Berkas PoD Masuk');
        $response->assertSee('VALIDASI MUTLAK PROOF OF DELIVERY (POD)');
    }

    public function test_console_renders_four_monitoring_kpi_cards(): void
    {
        $kpis = CoordinatorMonitoringData::kpis();

        $this->assertCount(4, $kpis);

        $response = $this->get(route('coordinator.monitoring'));

        $response->assertOk();

        foreach ($kpis as $kpi) {
            $response->assertSee($kpi['label']);
            $response->assertSee($kpi['foot_left']);
            $response->assertSee($kpi['foot_right']);
        }

        $response->assertSee('ARMADA AKTIF');
        $response->assertSee('Armada Lapangan');
        $response->assertSee('GPS 100% ONLINE');
        $response->assertSee('CHILLER TELEMETRY LIVE');
        $response->assertSee('TITIK DROPOFF SELESAI');
        $response->assertSee('3 / 5');
        $response->assertSee('Titik (60%)');
        $response->assertSee('PoD VALID: 3 BERKAS');
        $response->assertSee('PROGRESS OK');
        $response->assertSee('DALAM PERJALANAN');
        $response->assertSee('Armada On-Route');
        $response->assertSee('1 Menuju, 1 Unloading');
        $response->assertSee('ETA ON-SCHEDULE');
        $response->assertSee('KASUS RETUR / SELISIH');
        $response->assertSee('Kasus Butuh Verifikasi');
        $response->assertSee('D-8821-AB: -10 KG MEMAR');
        $response->assertSee('BA WAJIB');
    }

    public function test_console_renders_dispatch_log_with_three_shipments(): void
    {
        $dispatch = CoordinatorMonitoringData::dispatch();

        $this->assertCount(3, $dispatch['rows']);

        $response = $this->get(route('coordinator.monitoring'));

        $response->assertOk();
        $response->assertSee($dispatch['title']);
        $response->assertSee($dispatch['subtitle']);
        $response->assertSee($dispatch['badge']);
        $response->assertSee($dispatch['footer_left']);
        $response->assertSee($dispatch['footer_right']);

        foreach ($dispatch['columns'] as $column) {
            $response->assertSee($column);
        }

        foreach ($dispatch['rows'] as $row) {
            $response->assertSee($row['sj']);
            $response->assertSee($row['driver']);
            $response->assertSee($row['vehicle']);
            $response->assertSee($row['arrival']);
            $response->assertSee($row['customer']);
            $response->assertSee($row['status']);

            $this->assertJsPayload($response, $row['sj']);

            foreach ($row['actions'] as $action) {
                $response->assertSee($action['label']);
                $this->assertJsPayload($response, $action['key']);
            }
        }

        $response->assertSee('795 kg');
        $response->assertSee('Selada Romaine');
        $response->assertSee('442.5 kg');
        $response->assertSee('Baby Bok Choy &amp; Kale', false);
        $response->assertSee('240 kg');
        $response->assertSee('Manifest: 250 kg');
        $response->assertSee('manifestStrike(', false);
        $this->assertMatchesRegularExpression(
            '/class="[^"]*line-through[^"]*"[^>]*>Manifest: 250 kg/',
            $response->getContent(),
        );
        $response->assertSee('Selisih: 10 kg afkir basah');
        $response->assertSee('Periksa PoD');
        $response->assertSee('Unduh Bukti');
        $response->assertSee('Buka Lap. Retur');
        $response->assertSee('Detail BA');
    }

    public function test_dispatch_rows_reconcile_muatan_and_retur_case(): void
    {
        $dispatch = CoordinatorMonitoringData::dispatch();
        $rows = $dispatch['rows'];

        $this->assertSame(442.5, $rows[1]['cargo_kg']);
        $this->assertSame('POD-DOC-SUB4-0809-ACS', $rows[1]['dossier_ref']);
        $this->assertNull($rows[0]['dossier_ref'], 'Perjalanan unloading belum boleh punya berkas PoD.');
        $this->assertNull($rows[2]['dossier_ref'], 'Selisih timbangan wajib lewat BA Retur, bukan PoD langsung.');

        $this->assertEqualsWithDelta(
            10.0,
            $rows[2]['manifest_kg'] - $rows[2]['cargo_kg'],
            0.0001,
            'Selisih SJ-GPA-2025-0814 harus 10 KG sesuai kasus retur D-8821-AB.',
        );

        $this->assertGreaterThan(0, $dispatch['dropoff_base']);
        $this->assertGreaterThan(
            $dispatch['dropoff_total'] - $dispatch['dropoff_base'],
            $dispatch['dropoff_base'],
            'Mayor titik dropoff harus menutup sebagian besar titik hari ini.',
        );
        $this->assertSame(60, (int) round(($dispatch['dropoff_base'] / $dispatch['dropoff_total']) * 100));
    }

    public function test_console_renders_pod_dossier_for_default_shipment(): void
    {
        $dossier = CoordinatorMonitoringData::dossier();
        $dispatch = CoordinatorMonitoringData::dispatch();
        $entry = $dossier['entries'][$dispatch['default_sj']];

        $this->assertSame('SJ-GPA-2025-0809', $dispatch['default_sj']);

        $response = $this->get(route('coordinator.monitoring'));

        $response->assertOk();
        $response->assertSee($dossier['title']);
        $response->assertSee($dossier['subtitle']);
        $response->assertSee($dossier['badge']);
        $response->assertSee($dossier['reference_label']);
        $response->assertSee($dossier['sj_label']);
        $response->assertSee($dossier['checks_label']);
        $response->assertSee($dossier['notes_label']);
        $response->assertSee($entry['ref']);
        $response->assertSee($entry['sj']);
        $response->assertSee($entry['notes']);
        $response->assertSee($dossier['empty_title']);
        $response->assertSee($dossier['empty_body']);

        foreach ($entry['exhibits'] as $exhibit) {
            $response->assertSee($exhibit['code']);
            $response->assertSee($exhibit['meta']);
            $response->assertSee($exhibit['caption']);
            $response->assertSee($exhibit['status']);
        }

        $response->assertSee('CAP STEMPEL: BASAH QC ACS');
        $response->assertSee('TERBACA');
        $response->assertSee("+3.8\u{00B0}C");
        $response->assertSee('GEO: -6.1265, 106.6542');
        $response->assertSee('TOL: 12M');
    }

    public function test_mandatory_checks_expose_weight_and_gps_thresholds(): void
    {
        $dossier = CoordinatorMonitoringData::dossier();
        $dispatch = CoordinatorMonitoringData::dispatch();
        $entry = $dossier['entries'][$dispatch['default_sj']];

        $response = $this->get(route('coordinator.monitoring'));

        $this->assertCount(4, $dossier['checks']);
        $response->assertSee('Ttd Asli Penerima (Bu Siska)');
        $response->assertSee('Stempel Cap Basah PT/Instansi');
        $response->assertSee('Bobot Netto Pas (442.5 kg)');
        $response->assertSee('Radius GPS &lt; 20m Dari Dock', false);

        $this->assertSame(442.5, $entry['net_kg']);
        $this->assertSame($entry['net_kg'], $dispatch['rows'][1]['cargo_kg'], 'Bobot PoD harus sama dengan muatan surat jalan.');
        $this->assertLessThanOrEqual($entry['gps_tolerance_m'], $entry['gps_radius_m']);
        $this->assertSame(20.0, $entry['gps_tolerance_m']);
        $this->assertTrue($entry['checks']['signature']);
        $this->assertTrue($entry['checks']['stamp']);
    }

    public function test_console_renders_decision_gate_cta_and_audit_actions(): void
    {
        $dossier = CoordinatorMonitoringData::dossier();

        $response = $this->get(route('coordinator.monitoring'));

        $response->assertOk();
        $response->assertSee($dossier['decision_label']);
        $response->assertSee($dossier['cta']['label']);
        $this->assertJsPayload($response, $dossier['cta']['key']);
        $response->assertSee('dossierAction(', false);
        $response->assertSee('canValidate()', false);

        foreach ($dossier['decision'] as $decision) {
            $response->assertSee($decision['label']);
            $this->assertJsPayload($response, $decision['key']);
        }

        foreach ($dossier['buttons'] as $button) {
            $response->assertSee($button['label']);
            $this->assertJsPayload($response, $button['key']);
        }

        $response->assertSee('APPROVED (VALID)');
        $response->assertSee('REJECT (DITOLAK)');
        $response->assertSee('SIMPAN DRAFT');
        $response->assertSee('ESKALASI TIM AUDIT');
    }

    public function test_alpine_payload_exposes_rows_dossier_and_dropoff_context(): void
    {
        $monitoring = CoordinatorMonitoringData::for();
        $dispatch = $monitoring['dispatch'];

        $response = $this->get(route('coordinator.monitoring'));

        $response->assertOk();
        $this->assertJsPayload($response, $dispatch['rows']);
        $this->assertJsPayload($response, $monitoring['dossier']);
        $this->assertJsPayload($response, [
            'defaultSj' => 'SJ-GPA-2025-0809',
            'dropoffBase' => 3,
            'dropoffTotal' => 5,
        ]);

        $response->assertSee('coordinatorMonitoring(', false);
        $this->assertJsPayload($response, $dispatch['default_sj']);
    }
}
