<?php

namespace Tests\Feature\Coordinator;

use App\Models\User;
use App\Support\CoordinatorDispatchData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CoordinatorDispatchTest extends TestCase
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

    public function test_guest_can_preview_dispatch_console_with_demo_operator(): void
    {
        $response = $this->get(route('coordinator.dispatch'));

        $response->assertOk();
        $response->assertSee('Penerbitan Surat Jalan & Dispatch');
        $response->assertSee('Surat Jalan & Dispatch Armada Logistik');
        $response->assertSee('Koordinator Lapangan');
        $response->assertSee('Agus Tusan');
        $response->assertSee('ID : 002');
    }

    public function test_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Andi Nugroho']);

        $response = $this->actingAs($user)->get(route('coordinator.dispatch'));

        $response->assertOk();
        $response->assertSee('Andi Nugroho');
        $response->assertSee('AN');
    }

    public function test_sidebar_marks_surat_jalan_navigation_as_active(): void
    {
        $response = $this->get(route('coordinator.dispatch'));

        $response->assertOk();
        $response->assertSee('aria-current="page"', false);
        $response->assertSee(route('coordinator.dispatch'), false);
    }

    public function test_console_renders_header_actions_and_rule_05_banner(): void
    {
        $header = CoordinatorDispatchData::header();
        $sop = CoordinatorDispatchData::sop();

        $response = $this->get(route('coordinator.dispatch'));

        $response->assertOk();
        $response->assertSee($header['eyebrow']);
        $response->assertSee($header['title_before']);
        $response->assertSee($header['title_after']);
        $response->assertSee($header['subtitle']);

        foreach ($header['actions'] as $action) {
            $response->assertSee($action['label']);
        }

        $response->assertSee($sop['title']);
        $response->assertSee($sop['badge']);
        $response->assertSee($sop['body']);
        $response->assertSee($sop['cert_prefix']);
        $response->assertSee($sop['cert_value']);
        $response->assertSee('Lockout Code '.$sop['lockout_code']);
    }

    public function test_console_renders_four_dispatch_kpi_cards(): void
    {
        $response = $this->get(route('coordinator.dispatch'));

        $response->assertOk();
        $response->assertSee('SURAT JALAN SIAP DISPATCH');
        $response->assertSee('Dokumen Sah');
        $response->assertSee('Tonase Netto Sah:');
        $response->assertSee('1.585,00 KG');
        $response->assertSee('ARMADA STANDBY DI DOCK');
        $response->assertSee('Unit Ready');
        $response->assertSee('Blind Van');
        $response->assertSee('Engkel Box');
        $response->assertSee('Pickup');
        $response->assertSee('KEBERANGKATAN PERTAMA');
        $response->assertSee('04:30');
        $response->assertSee('WIB');
        $response->assertSee('SLA DAWN DELIVERY');
        $response->assertSee('STATUS TERA & CETAK');
        $response->assertSee('100%');
        $response->assertSee('Sah Validated');
        $response->assertSee('ONLINE 203 DPI');
    }

    public function test_console_renders_locked_print_queue_with_net_evidence(): void
    {
        $queue = CoordinatorDispatchData::queue();

        $this->assertCount(2, $queue['items']);

        $response = $this->get(route('coordinator.dispatch'));

        $response->assertOk();
        $response->assertSee($queue['title']);
        $response->assertSee($queue['meta']);

        foreach ($queue['items'] as $item) {
            $response->assertSee($item['po']);
            $response->assertSee($item['customer']);
            $response->assertSee($item['status_chip']);
            $response->assertSee($item['driver']);
            $response->assertSee($item['driver_id']);
            $response->assertSee($item['vehicle']);
            $response->assertSee($item['plate']);
            $response->assertSee($item['commodity']);
            $response->assertSee($item['dock']);
            $response->assertSee('LOCK STAMP: '.$item['lock_stamp']);
            $response->assertSee($item['issue_label']);
            $response->assertSee(number_format($item['net'], 2, '.', ',').' KG');

            $this->assertJsPayload($response, $item['sj']);
        }

        $response->assertSee('(4)');
        $response->assertSee(route('prints.surat-jalan'), false);
    }

    public function test_queue_net_reconciles_with_gross_minus_tara_and_manifest_tonage(): void
    {
        $queue = CoordinatorDispatchData::queue();
        $rows = CoordinatorDispatchData::manifest()['rows'];

        foreach ($queue['items'] as $item) {
            $this->assertSame(
                $item['net'],
                $item['gross'] - $item['tara'],
                'Netto harus sama dengan gross dikurangi tara untuk '.$item['sj'],
            );
            $this->assertGreaterThan(0, $item['net']);
        }

        $manifestTonase = array_sum(array_column($rows, 'net'));

        $this->assertSame(1585.0, $manifestTonase);
        $this->assertSame(795.0, $rows[0]['net']);
        $this->assertSame(442.5, $rows[1]['net']);
        $this->assertSame(347.5, $rows[2]['net']);
    }

    public function test_console_renders_manifest_table_with_three_documents(): void
    {
        $manifest = CoordinatorDispatchData::manifest();

        $this->assertCount(3, $manifest['rows']);

        $response = $this->get(route('coordinator.dispatch'));

        $response->assertOk();
        $response->assertSee($manifest['title']);
        $response->assertSee($manifest['subtitle']);
        $response->assertSee($manifest['filter_label']);
        $response->assertSee($manifest['filter_default']);

        foreach ($manifest['columns'] as $column) {
            $response->assertSee($column);
        }

        foreach ($manifest['filters'] as $filter) {
            $response->assertSee($filter['label']);
            $this->assertJsPayload($response, $filter['key']);
        }

        foreach ($manifest['rows'] as $row) {
            $response->assertSee($row['id']);
            $response->assertSee('REF: '.$row['ref']);
            $response->assertSee($row['driver']);
            $response->assertSee($row['plate'].' ('.$row['vehicle'].')');
            $response->assertSee(number_format($row['net'], 2, '.', ',').' KG');
            $response->assertSee($row['time']);
            $response->assertSee($row['slot']);
            $response->assertSee($row['copies']);
            $response->assertSee($row['qr']);
            $response->assertSee($row['dock']);
            $response->assertSee($row['action_label']);

            $this->assertJsPayload($response, $row['id']);
        }

        $response->assertSee('RANGKAP DIBAWA');
        $response->assertSee('DEPARTED ON-TIME');
        $response->assertSee('Lacak GPS Supir');
    }

    public function test_console_renders_batch_dispatch_operations(): void
    {
        $batch = CoordinatorDispatchData::batch();

        $response = $this->get(route('coordinator.dispatch'));

        $response->assertOk();
        $response->assertSee($batch['title']);
        $response->assertSee($batch['subtitle']);

        foreach ($batch['actions'] as $action) {
            $response->assertSee($action['label']);
            $this->assertJsPayload($response, $action['key']);
        }
    }

    public function test_console_mounts_coordinator_dispatch_component_with_payload(): void
    {
        $dispatch = CoordinatorDispatchData::for();
        $readyDocs = collect($dispatch['kpis'])->firstWhere('key', 'ready_docs')['value'] ?? 4;

        $response = $this->get(route('coordinator.dispatch'));

        $response->assertOk();
        $response->assertSee('coordinatorDispatch(', false);

        $this->assertJsPayload($response, $dispatch['queue']['items']);
        $this->assertJsPayload($response, $dispatch['manifest']['rows']);
        $this->assertJsPayload($response, $dispatch['manifest']['filters']);
        $this->assertJsPayload($response, $readyDocs);
    }

    public function test_dispatch_route_does_not_disturb_previous_coordinator_modules(): void
    {
        $dashboard = $this->get(route('coordinator.dashboard'));
        $dashboard->assertOk();
        $dashboard->assertSee('1.840');
        $dashboard->assertSee('1.920');

        $stock = $this->get(route('coordinator.stock'));
        $stock->assertOk();
        $stock->assertSee('14.850');
        $stock->assertSee('8.900');

        $weighing = $this->get(route('coordinator.weighing'));
        $weighing->assertOk();
        $weighing->assertSee('795');
    }
}
