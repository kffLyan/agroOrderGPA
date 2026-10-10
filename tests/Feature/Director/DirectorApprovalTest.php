<?php

namespace Tests\Feature\Director;

use App\Models\User;
use App\Support\DirectorApprovalData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class DirectorApprovalTest extends TestCase
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

    public function test_guest_can_preview_contract_approval_module(): void
    {
        $response = $this->get(route('director.approval'));

        $response->assertOk();
        $response->assertSee('Persetujuan Kontrak // Otorisasi Tier-1', false);
        $response->assertSee('Direktur');
        $response->assertSee('Asep Tember');
        $response->assertSee('ID : 001');
    }

    public function test_module_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Dewi Kartika']);

        $response = $this->actingAs($user)->get(route('director.approval'));

        $response->assertOk();
        $response->assertSee('Dewi Kartika');
        $response->assertSee('DK');
    }

    public function test_module_renders_authorization_queue_with_reconciled_total(): void
    {
        $queue = DirectorApprovalData::queue();

        $response = $this->get(route('director.approval'));

        $response->assertOk();
        $response->assertSee('PERLU OTORISASI DIREKTUR');
        $response->assertSee('CLEARANCE: DIRUT ONLY');
        $response->assertSee($queue['title']);
        $response->assertSee('HSM RSA-4096 Terhubung');
        $response->assertSee($queue['total_note']);

        foreach ($queue['columns'] as $column) {
            $response->assertSee($column);
        }

        foreach ($queue['filters'] as $filter) {
            $response->assertSee($filter['label'].' ('.$filter['count'].')');
        }

        $this->assertSame(385_000_000, $queue['total_value']);
    }

    public function test_module_renders_kpi_cards_derived_from_the_same_rows(): void
    {
        $cards = DirectorApprovalData::cards();
        $queue = DirectorApprovalData::queue();
        $byKey = collect($cards)->keyBy('key');

        $response = $this->get(route('director.approval'));

        $response->assertOk();
        $this->assertSame(count($queue['rows']).' Pengajuan', $byKey['pending']['value']);
        $this->assertSame($queue['total_value_label'], $byKey['value']['value']);
        $this->assertSame('14:32', $byKey['session']['value']);

        // Kartu exception hanya menghitung baris yang benar-benar keluar plafon.
        $this->assertSame(
            count(array_filter($queue['rows'], static fn (array $row): bool => $row['exceeds_plafon'])).' Pengajuan',
            $byKey['exception']['value'],
        );

        foreach ($cards as $card) {
            $response->assertSee($card['label']);
            $response->assertSee($card['value'], false);
        }
    }

    public function test_rows_expose_tier_one_guardrails(): void
    {
        $queue = DirectorApprovalData::queue();

        $response = $this->get(route('director.approval'));

        $response->assertOk();
        $this->assertJsPayload($response, $queue['rows']);

        foreach ($queue['rows'] as $row) {
            $response->assertSee($row['contract'], false);
            $response->assertSee($row['client'], false);
            $response->assertSee($row['exception'], false);
            $response->assertSee($row['margin_flag'], false);
            $response->assertSee($row['approve_label']);

            if ($row['filter_key'] === 'discount') {
                $this->assertGreaterThan($row['discount_ceiling'], $row['discount_percent']);
                $this->assertSame('Setujui Kontrak', $row['approve_label']);
                $this->assertTrue($row['exceeds_plafon']);
            }

            if ($row['filter_key'] === 'top') {
                $this->assertGreaterThan($row['top_default'], $row['top_days']);
                $this->assertTrue($row['exceeds_plafon']);
            }

            if ($row['filter_key'] === 'annual') {
                $this->assertSame('TTD Digital (BSrE)', $row['approve_label']);
                $this->assertFalse($row['exceeds_plafon']);
            }

            if ($row['margin_delta'] !== null) {
                $this->assertGreaterThanOrEqual(0, $row['margin_delta']);
                $this->assertGreaterThanOrEqual($row['margin_minimum'], $row['margin_value']);
            }
        }
    }

    public function test_module_owns_batch_and_search_controls(): void
    {
        $queue = DirectorApprovalData::queue();

        $response = $this->get(route('director.approval'));

        $response->assertOk();
        $response->assertSee('x-data="directorApproval(', false);
        $response->assertSee($queue['batch_approve']);
        $response->assertSee($queue['batch_select_all']);
        $response->assertSee($queue['search_placeholder']);
        $response->assertSee($queue['audit_note']);
        $response->assertSee('Cetak Antrian Otorisasi');

        // Antrean Tier-1 tidak lagi menduplikasi diri di dashboard eksekutif.
        $this->actingAs(User::factory()->create())
            ->get(route('director.dashboard'))
            ->assertDontSee('Approve Terpilih (Batch)');
    }

    public function test_navigation_highlights_contract_approval_module(): void
    {
        $response = $this->get(route('director.approval'));

        $response->assertOk();
        $response->assertSee('href="'.route('director.approval').'"', false);
        $response->assertSee('Persetujuan Kontrak');
    }
}
