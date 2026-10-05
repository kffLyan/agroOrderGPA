<?php

namespace Tests\Feature\Director;

use App\Models\User;
use App\Support\DirectorApprovalData;
use App\Support\DirectorGovernanceData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class DirectorGovernanceTest extends TestCase
{
    use RefreshDatabase;

    private function assertJsPayload(TestResponse $response, mixed $payload): void
    {
        $response->assertSee((string) Js::from($payload), false);
    }

    public function test_guest_can_preview_governance_console(): void
    {
        $response = $this->get(route('director.governance'));

        $response->assertOk();
        $response->assertSee('Pengaturan Tata Kelola // Kebijakan &amp; Audit Trail', false);
        $response->assertSee('Direktur');
        $response->assertSee('Asep Tember');
    }

    public function test_module_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Dewi Kartika']);

        $response = $this->actingAs($user)->get(route('director.governance'));

        $response->assertOk();
        $response->assertSee('Dewi Kartika');
        $response->assertSee('DK');
    }

    public function test_console_renders_four_compliance_metrics(): void
    {
        $metrics = DirectorGovernanceData::metrics();

        $response = $this->get(route('director.governance'));

        $response->assertOk();
        $this->assertCount(4, $metrics);

        foreach ($metrics as $metric) {
            $response->assertSee($metric['eyebrow']);
            $response->assertSee($metric['value'], false);
            $response->assertSee($metric['note']);
            $response->assertSee($metric['foot_value']);
        }
    }

    public function test_credit_ceiling_metric_reconciles_with_utilisation(): void
    {
        $metrics = collect(DirectorGovernanceData::metrics())->keyBy('key');

        $response = $this->get(route('director.governance'));

        $response->assertOk();
        $response->assertSee($metrics['credit']['value'], false);
        $response->assertSee($metrics['credit']['foot_value'], false);
        $response->assertSee('style="width: '.$metrics['credit']['bar_width'].'%"', false);

        $this->assertSame('Rp 2.500.000.000', $metrics['credit']['value']);
        $this->assertSame('47.4', $metrics['credit']['bar_width']);
        $this->assertSame(
            round(DirectorGovernanceData::CREDIT_USED / DirectorGovernanceData::CREDIT_CEILING * 100, 1),
            (float) $metrics['credit']['bar_width'],
        );
    }

    public function test_rule_engine_renders_five_statutory_parameters(): void
    {
        $parameters = DirectorGovernanceData::parameters();

        $response = $this->get(route('director.governance'));

        $response->assertOk();
        $response->assertSee($parameters['title']);
        $response->assertSee($parameters['subtitle']);
        $response->assertSee($parameters['action']);
        $this->assertCount(5, $parameters['cards']);

        foreach ($parameters['cards'] as $card) {
            $response->assertSee($card['rule']);
            $response->assertSee($card['status']);
            $response->assertSee($card['title']);
            $response->assertSee($card['config_id']);

            foreach ($card['facts'] as $fact) {
                $response->assertSee($fact['label']);
                $response->assertSee(e($fact['value']), false);
            }
        }
    }

    public function test_audit_trail_renders_chained_ledger_rows(): void
    {
        $audit = DirectorGovernanceData::audit();

        $response = $this->get(route('director.governance'));

        $response->assertOk();
        $response->assertSee($audit['title']);
        $response->assertSee($audit['ledger_status']);
        $response->assertSee($audit['shown_label']);
        $this->assertJsPayload($response, $audit['rows']);

        foreach ($audit['columns'] as $column) {
            $response->assertSee($column);
        }

        foreach ($audit['rows'] as $row) {
            $response->assertSee($row['date']);
            $response->assertSee($row['actor']);
            $response->assertSee($row['action']);
            $response->assertSee($row['document'], false);
            $response->assertSee($row['hash'], false);
            $response->assertSee($row['block'], false);
        }

        $this->assertSame(DirectorGovernanceData::AUDIT_PAGE_SIZE, count($audit['rows']));
        $this->assertCount(1, array_filter($audit['rows'], static fn (array $row): bool => $row['blocked']));
    }

    public function test_critical_controls_render_freeze_and_succession_protocols(): void
    {
        $controls = DirectorGovernanceData::controls();

        $response = $this->get(route('director.governance'));

        $response->assertOk();
        $this->assertCount(2, $controls);

        foreach ($controls as $control) {
            $response->assertSee($control['protocol']);
            $response->assertSee($control['title']);
            $response->assertSee($control['action']);

            foreach ($control['facts'] as $fact) {
                $response->assertSee($fact['label']);
                $response->assertSee(e($fact['value']), false);
            }
        }

        $response->assertSee('Aktifkan Master Freeze');
        $response->assertSee('Atur Plt Direksi');
    }

    public function test_approval_metric_stays_consistent_with_contract_approval_module(): void
    {
        $queue = DirectorApprovalData::queue();
        $approvalMetric = collect(DirectorGovernanceData::metrics())->firstWhere('key', 'approval');

        $response = $this->get(route('director.governance'));

        $response->assertOk();
        $response->assertSee($approvalMetric['value'], false);
        $this->assertSame(
            DirectorGovernanceData::APPROVED_CONTRACTS.' Kontrak Disetujui',
            $approvalMetric['value'],
        );
        $this->assertSame(count($queue['rows']), DirectorGovernanceData::APPROVED_CONTRACTS);
    }

    public function test_module_exposes_search_filter_and_alpine_component(): void
    {
        $response = $this->get(route('director.governance'));

        $response->assertOk();
        $response->assertSee('x-data="directorGovernance(', false);
        $response->assertSee('Export Full Audit Log (.CSV)');
        $response->assertSee('Uji Integritas Kriptografi');
        $response->assertSee('Kunci Sistem Periode');
        $response->assertSee('Cari Dokumen / Hash...', false);
        $response->assertSee('href="'.route('director.governance').'"', false);
    }
}