<?php

namespace Tests\Feature\Director;

use App\Models\User;
use App\Support\DirectorReceivablesData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class DirectorReceivablesTest extends TestCase
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

    public function test_guest_can_preview_receivables_with_demo_operator(): void
    {
        $response = $this->get(route('director.receivables'));

        $response->assertOk();
        $response->assertSee('Direktur');
        $response->assertSee('Asep Tember');
    }

    public function test_receivables_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Dewi Kartika']);

        $response = $this->actingAs($user)->get(route('director.receivables'));

        $response->assertOk();
        $response->assertSee('Dewi Kartika');
    }

    public function test_page_renders_header_and_period(): void
    {
        $header = DirectorReceivablesData::header();

        $response = $this->get(route('director.receivables'));

        $response->assertOk();
        $response->assertSee($header['title']);
        $response->assertSee($header['period']);
        $response->assertSee('PERIODE: OKTOBER 2026');
    }

    public function test_page_renders_kpi_cards_aging_and_ledger(): void
    {
        $receivables = DirectorReceivablesData::for();

        $response = $this->get(route('director.receivables'));

        $response->assertOk();
        $response->assertSee('TOTAL PIUTANG B2B');
        $response->assertSee('NORMAL / AMAN');
        $response->assertSee('FOLLOW-UP SEKRE');
        $response->assertSee('AUTO-FREEZE AKTIF');
        $response->assertSee($receivables['aging']['title']);
        $response->assertSee($receivables['ledger']['title']);
        $response->assertSee('Nilai Tagihan Tertutang');
    }

    public function test_page_renders_freeze_policy_and_verification_queue(): void
    {
        $response = $this->get(route('director.receivables'));

        $response->assertOk();
        $response->assertSee('RULE 15.3: PEMBEKUAN PO OTOMATIS');
        $response->assertSee('RULE 15.6: CEILING PLAFON KREDIT');
        $response->assertSee('STATUS: ENFORCED');
        $response->assertSee('Protokol Verifikasi (Rule 11)');
        $response->assertSee('3 Faktur');
        $response->assertSee('Rp 78.400.000');
    }

    public function test_ledger_rows_are_rendered_with_client_and_invoice_codes(): void
    {
        $response = $this->get(route('director.receivables'));

        $response->assertOk();
        $response->assertSee('PT Aerofood ACS');
        $response->assertSee('Royal Ambarrukmo Hotel');
        $response->assertSee('PT Boga Rasa Kulina Prima');
        $response->assertSee('PT Segar Makmur Ritelindo');
        $response->assertSee('B2B-ACS-091');
        $response->assertSee('B2B-SMR-019');
    }

    public function test_total_receivable_ties_aging_stages_and_ledger_rows(): void
    {
        $aging = DirectorReceivablesData::aging();
        $ledger = DirectorReceivablesData::ledger();

        $agingTotal = array_sum(array_column($aging['stages'], 'value'));
        $ledgerTotal = array_sum(array_column($ledger['rows'], 'outstanding'));

        $this->assertSame(DirectorReceivablesData::TOTAL_RECEIVABLE, $agingTotal);
        $this->assertSame(DirectorReceivablesData::TOTAL_RECEIVABLE, $ledgerTotal);
        $this->assertSame($agingTotal, $ledgerTotal);
    }

    public function test_aging_share_labels_sum_to_one_hundred_percent(): void
    {
        $stages = DirectorReceivablesData::aging()['stages'];
        $share = array_sum(array_column($stages, 'share'));

        $this->assertEqualsWithDelta(100.0, $share, 0.01);
        foreach ($stages as $stage) {
            $this->assertSame(
                $stage['value_label'],
                'Rp '.number_format($stage['value'], 0, '.', '.')
            );
        }
    }

    public function test_kpi_cards_are_derived_from_aging_stages(): void
    {
        $aging = DirectorReceivablesData::aging();
        $stages = $aging['stages'];
        $cards = DirectorReceivablesData::kpiCards();
        $byKey = array_column($cards, null, 'key');

        $this->assertCount(4, $cards);
        $this->assertSame('Rp 248.500.000', $byKey['total']['value']);
        $this->assertSame(
            'Rp '.number_format($stages[0]['value'] + $stages[1]['value'], 0, '.', '.'),
            $byKey['lancar']['value']
        );
        $this->assertSame('Rp '.number_format($stages[2]['value'], 0, '.', '.'), $byKey['warning']['value']);
        $this->assertSame('Rp '.number_format($stages[3]['value'], 0, '.', '.'), $byKey['kritis']['value']);
    }

    public function test_collectibility_excludes_the_locked_critical_bucket(): void
    {
        $aging = DirectorReceivablesData::aging();
        $critical = $aging['stages'][3]['value'];

        $this->assertSame(
            DirectorReceivablesData::TOTAL_RECEIVABLE - $critical,
            $aging['collectible']
        );
        $this->assertSame(
            'Kolektibilitas Portofolio: 92.8%',
            $aging['collectibility_label']
        );
    }

    public function test_aging_stage_three_absorbs_the_design_residual(): void
    {
        $stages = DirectorReceivablesData::aging()['stages'];

        $this->assertSame(45_200_000, $stages[2]['value'], 'Pita warning harus menutup seluruh total piutang.');
        $this->assertSame(18.2, $stages[2]['share']);
        $this->assertSame(18_000_000, $stages[3]['value']);
    }

    public function test_ledger_days_remaining_is_anchored_to_the_as_of_date(): void
    {
        $rows = array_column(DirectorReceivablesData::ledger()['rows'], null, 'client_code');

        $this->assertSame(22, $rows['B2B-ACS-091']['days_remaining']);
        $this->assertSame(-3, $rows['B2B-RAH-042']['days_remaining']);
        $this->assertSame(15, $rows['B2B-BRK-108']['days_remaining']);
        $this->assertSame(-12, $rows['B2B-SMR-019']['days_remaining']);
    }

    public function test_overdue_status_follows_the_freeze_threshold(): void
    {
        $rows = array_column(DirectorReceivablesData::ledger()['rows'], null, 'client_code');

        $this->assertSame(['value' => 'LANCAR', 'tone' => 'success'], [
            'value' => $rows['B2B-ACS-091']['status_label'],
            'tone' => $rows['B2B-ACS-091']['status_tone'],
        ]);
        $this->assertSame('LANCAR', $rows['B2B-BRK-108']['status_label']);

        $this->assertSame(
            'WARNING TEMPO',
            $rows['B2B-RAH-042']['status_label'],
            'Keterlambatan 3 hari masih di bawah ambang Rule 15.3.'
        );
        $this->assertSame('warning', $rows['B2B-RAH-042']['status_tone']);

        $this->assertSame(
            'AUTO-FREEZE PO',
            $rows['B2B-SMR-019']['status_label'],
            'Keterlambatan 12 hari melampaui ambang 7 hari.'
        );
        $this->assertSame('danger', $rows['B2B-SMR-019']['status_tone']);
        $this->assertSame(DirectorReceivablesData::FREEZE_THRESHOLD_DAYS, 7);
    }

    public function test_royal_due_date_resolves_the_h_minus_three_claim(): void
    {
        $rows = array_column(DirectorReceivablesData::ledger()['rows'], null, 'client_code');
        $royal = $rows['B2B-RAH-042'];

        $this->assertSame('2026-10-21', $royal['due_date']->toDateString());
        $this->assertSame('21 Okt 2026', $royal['due_label']);
        $this->assertStringContainsString('OVERDUE 3 HARI', $royal['days_label']);
    }

    public function test_due_labels_use_indonesian_month_names(): void
    {
        $rows = array_column(DirectorReceivablesData::ledger()['rows'], null, 'client_code');

        $this->assertSame('15 Nov 2026', $rows['B2B-ACS-091']['due_label']);
        $this->assertSame('08 Nov 2026', $rows['B2B-BRK-108']['due_label']);
        $this->assertSame('12 Okt 2026', $rows['B2B-SMR-019']['due_label']);
    }

    public function test_plafon_utilisation_is_derived_and_capped_at_one_hundred_percent(): void
    {
        $rows = array_column(DirectorReceivablesData::ledger()['rows'], null, 'client_code');

        foreach ($rows as $code => $row) {
            $this->assertSame(
                (int) round($row['exposure'] / $row['limit'] * 100),
                (int) $row['utilisation_label'],
                "Utilisasi {$code} harus mengikuti eksposur dibagi limit."
            );
            $this->assertSame(
                $row['limit_label'],
                'Rp '.intdiv($row['exposure'], 1_000_000).'M / '.intdiv($row['limit'], 1_000_000).'M'
            );
            $this->assertLessThanOrEqual(
                DirectorReceivablesData::PLAFON_CAP,
                $row['utilisation_width'],
                "Bar utilisasi {$code} tidak boleh melewati plafon."
            );
        }

        $this->assertSame('63%', $rows['B2B-ACS-091']['utilisation_note']);
        $this->assertSame('60%', $rows['B2B-RAH-042']['utilisation_note']);
        $this->assertSame('63%', $rows['B2B-BRK-108']['utilisation_note']);
    }

    public function test_only_segar_makmur_breaches_the_plafon_and_stays_locked(): void
    {
        $rows = DirectorReceivablesData::ledger()['rows'];
        $breaching = array_values(array_filter($rows, fn (array $row): bool => $row['over_limit']));
        $locked = array_values(array_filter($rows, fn (array $row): bool => $row['locked']));

        $this->assertCount(1, $breaching);
        $this->assertCount(1, $locked);
        $this->assertSame('B2B-SMR-019', $breaching[0]['client_code']);
        $this->assertSame('B2B-SMR-019', $locked[0]['client_code']);
        $this->assertSame('105% (OVER)', $breaching[0]['utilisation_note']);
        $this->assertSame(105.0, $breaching[0]['utilisation']);
        $this->assertContains('Restrukturisasi Tagihan', $breaching[0]['actions']);
        $this->assertContains('Lepas Freeze', $breaching[0]['actions']);
    }

    public function test_unlocked_clients_offer_a_plafon_lock_action(): void
    {
        $rows = DirectorReceivablesData::ledger()['rows'];

        foreach ($rows as $row) {
            if ($row['locked']) {
                $this->assertNotContains('Kunci Plafon', $row['actions']);
            } else {
                $this->assertContains('Kunci Plafon', $row['actions']);
            }
        }

        $this->assertSame(
            ['Kirim Reminder WA', 'Kunci Plafon'],
            array_column($rows, 'actions', 'client_code')['B2B-RAH-042']
        );
    }

    public function test_verification_queue_matches_the_clearing_baseline(): void
    {
        $verification = DirectorReceivablesData::verification();

        $this->assertSame(3, DirectorReceivablesData::PENDING_INVOICES);
        $this->assertSame(78_400_000, DirectorReceivablesData::PENDING_CLEARING);
        $this->assertSame('3 Faktur', $verification['queue_value']);
        $this->assertSame('Rp 78.400.000', $verification['clearing_value']);
        $this->assertStringContainsString('1x24 JAM', $verification['sla']);
    }

    public function test_policy_declares_both_enforced_rules(): void
    {
        $policy = DirectorReceivablesData::policy();

        $this->assertCount(2, $policy['rules']);
        foreach ($policy['rules'] as $rule) {
            $this->assertSame('STATUS: ENFORCED', $rule['status_label']);
        }

        $this->assertStringContainsString('7 HARI', $policy['rules'][0]['threshold_label']);
        $this->assertSame('LIMIT CAP: 100.0% PLAFON', $policy['rules'][1]['threshold_label']);
        $this->assertSame('SISTEM AKTIF', $policy['chip']);
    }

    public function test_ledger_pagination_metadata_matches_active_contracts(): void
    {
        $ledger = DirectorReceivablesData::ledger();

        $this->assertSame(DirectorReceivablesData::ACTIVE_CONTRACTS, 18);
        $this->assertSame(4, $ledger['visible_rows']);
        $this->assertSame(18, $ledger['active_contracts']);
        $this->assertSame('Menampilkan 4 dari 18 Klien Kontrak Aktif B2B', $ledger['shown_label']);
        $this->assertSame('Halaman 1 / 5', $ledger['page_label']);
    }

    public function test_ledger_columns_cover_the_audit_trail(): void
    {
        $columns = DirectorReceivablesData::ledger()['columns'];

        $this->assertCount(7, $columns);
        $this->assertSame('Klien Korporat B2B', $columns[0]);
        $this->assertContains('Surat Jalan (SJ) Terlampir', $columns);
        $this->assertContains('Nilai Tagihan Tertutang', $columns);
        $this->assertContains('Termin (TOP) & Jatuh Tempo', $columns);
        $this->assertContains('Status AR', $columns);
        $this->assertContains('Utilisasi Plafon Kredit', $columns);
        $this->assertContains('Tindakan Direksi', $columns);
    }

    public function test_alpine_component_is_mounted_with_reconciled_payload(): void
    {
        $receivables = DirectorReceivablesData::for();

        $response = $this->get(route('director.receivables'));

        $response->assertOk();
        $response->assertSee('x-data="directorReceivables(', false);
        $this->assertJsPayload($response, $receivables['ledger']['rows']);
        $this->assertJsPayload($response, $receivables['policy']);
        $this->assertJsPayload($response, $receivables['verification']);
    }

    public function test_client_side_filter_controls_are_wired_to_the_ledger(): void
    {
        $response = $this->get(route('director.receivables'));

        $response->assertOk();
        $response->assertSee('Cari Klien / No. SJ...', false);
        $response->assertSee('x-model="query"', false);
        $response->assertSee("setTopFilter('14')", false);
        $response->assertSee("setTopFilter('30')", false);
        $response->assertSee("setTopFilter('45')", false);
        $response->assertSee('x-text="shownLabel()"', false);
    }

    public function test_ledger_rows_expose_a_row_visibility_hook(): void
    {
        $rows = DirectorReceivablesData::ledger()['rows'];

        $response = $this->get(route('director.receivables'));

        $response->assertOk();
        foreach (array_keys($rows) as $index) {
            $response->assertSee('x-show="rowVisible('.$index.')"', false);
        }
    }

    public function test_navigation_marks_receivables_as_the_active_module(): void
    {
        $response = $this->get(route('director.receivables'));

        $response->assertOk();
        $response->assertSee(route('director.receivables'), false);
        $response->assertSee('Piutang &amp; Tagihan', false);
        $this->assertSame(
            1,
            substr_count((string) $response->getContent(), 'aria-current="page"'),
            'Hanya modul Piutang & Tagihan yang boleh ditandai aktif pada halaman ini.',
        );
    }
}
