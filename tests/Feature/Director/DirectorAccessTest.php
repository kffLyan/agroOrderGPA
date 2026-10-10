<?php

namespace Tests\Feature\Director;

use App\Models\User;
use App\Support\DirectorAccessData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Js;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class DirectorAccessTest extends TestCase
{
    use RefreshDatabase;

    private function assertJsPayload(TestResponse $response, mixed $payload): void
    {
        $response->assertSee((string) Js::from($payload), false);
    }

    public function test_guest_can_preview_access_console(): void
    {
        $response = $this->get(route('director.access'));

        $response->assertOk();
        $response->assertSee('Kelola Akun Pengguna &amp; RBAC // Matriks Otorisasi', false);
        $response->assertSee('Direktur');
        $response->assertSee('Asep Tember');
    }

    public function test_module_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Dewi Kartika']);

        $response = $this->actingAs($user)->get(route('director.access'));

        $response->assertOk();
        $response->assertSee('Dewi Kartika');
        $response->assertSee('DK');
    }

    public function test_console_renders_four_identity_metrics(): void
    {
        $metrics = DirectorAccessData::metrics();

        $response = $this->get(route('director.access'));

        $response->assertOk();
        $this->assertCount(4, $metrics);

        foreach ($metrics as $metric) {
            $response->assertSee($metric['eyebrow']);
            $response->assertSee($metric['value'], false);
            $response->assertSee($metric['note']);

            foreach ($metric['facts'] as $fact) {
                $response->assertSee($fact['text'], false);
            }
        }

        $this->assertSame('48', $metrics[0]['value']);
        $this->assertSame('14 NODE LOGIN', $metrics[1]['value']);
        $this->assertSame('100%', $metrics[2]['value']);
        $this->assertSame('0 INSIDEN', $metrics[3]['value']);
    }

    public function test_session_breakdown_reconciles_with_active_sessions(): void
    {
        $sessions = DirectorAccessData::metrics()[1];

        $this->assertSame('14 NODE LOGIN', $sessions['value']);
        $this->assertSame(DirectorAccessData::ACTIVE_SESSIONS, 14);
        $this->assertSame('2 Dir | 4 Adm', $sessions['facts'][0]['text']);
        $this->assertSame('3 Koord | 5 Supir', $sessions['facts'][1]['text']);
        $this->assertSame(2 + 4 + 3 + 5, DirectorAccessData::ACTIVE_SESSIONS);
    }

    public function test_rbac_matrix_renders_five_roles_and_six_modules(): void
    {
        $matrix = DirectorAccessData::matrix();

        $response = $this->get(route('director.access'));

        $response->assertOk();
        $response->assertSee($matrix['title']);
        $response->assertSee($matrix['action']);
        $this->assertCount(6, $matrix['columns']);
        $this->assertCount(6, $matrix['rows']);

        foreach ($matrix['columns'] as $column) {
            $response->assertSee($column['label']);
            $response->assertSee($column['rule']);
        }

        foreach ($matrix['rows'] as $row) {
            $response->assertSee($row['module']);
            $response->assertSee($row['detail']);
            $this->assertCount(5, $row['cells']);

            foreach ($row['cells'] as $cell) {
                $response->assertSee($cell['label']);
            }
        }
    }

    public function test_contract_approval_row_is_reserved_for_director_only(): void
    {
        $matrix = DirectorAccessData::matrix();
        $approval = collect($matrix['rows'])->firstWhere('module', 'Approval Kontrak & Kunci Audit');

        $this->assertTrue($approval['highlight']);
        $this->assertSame('SOLE APPROVER / LOCK', $approval['cells'][4]['label']);
        $this->assertSame('NONE (BLOCKED)', $approval['cells'][0]['label']);
        $this->assertSame('NONE (BLOCKED)', $approval['cells'][2]['label']);
        $this->assertSame('NONE (BLOCKED)', $approval['cells'][3]['label']);
        $this->assertSame('Draft / Review', $approval['cells'][1]['label']);
    }

    public function test_user_directory_renders_identities_and_alpine_payload(): void
    {
        $directory = DirectorAccessData::directory();

        $response = $this->get(route('director.access'));

        $response->assertOk();
        $response->assertSee($directory['title']);
        $response->assertSee($directory['search_placeholder'], false);
        $response->assertSee($directory['shown_label']);
        $response->assertSee($directory['ledger_note']);
        $response->assertSee($directory['page_label']);
        $response->assertSee($directory['prev_label']);
        $response->assertSee($directory['next_label']);
        $this->assertJsPayload($response, $directory['rows']);

        foreach ($directory['columns'] as $column) {
            $response->assertSee($column);
        }

        foreach ($directory['rows'] as $row) {
            $response->assertSee($row['badge'], false);
            $response->assertSee($row['name']);
            $response->assertSee($row['code'], false);
            $response->assertSee($row['nik'], false);
            $response->assertSee($row['hub']);
            $response->assertSee($row['mfa']);
            $response->assertSee($row['login']);
            $response->assertSee($row['login_meta']);

            foreach ($row['actions'] as $rowAction) {
                $response->assertSee($rowAction['label']);
            }
        }

        $this->assertSame(DirectorAccessData::USER_PAGE_SIZE, count($directory['rows']));
        $this->assertStringContainsString('dari 48 entri', $directory['shown_label']);
    }

    public function test_directory_roles_cover_five_isolated_authorities(): void
    {
        $keys = array_column(DirectorAccessData::directory()['roles'], 'key');
        $roleKeys = array_column(DirectorAccessData::directoryRows(), 'role_key');

        $response = $this->get(route('director.access'));

        $response->assertOk();
        $this->assertSame(['all', 'director', 'admin', 'coordinator', 'driver', 'client'], $keys);
        $this->assertSame(array_slice($keys, 1), array_values(array_unique($roleKeys)));

        foreach ($keys as $key) {
            $response->assertSee($key === 'all' ? 'Semua Role (5)' : 'x-data="directorAccess(', false);
        }
    }

    public function test_console_exposes_header_actions_and_alpine_wiring(): void
    {
        $header = DirectorAccessData::header();

        $response = $this->get(route('director.access'));

        $response->assertOk();
        $response->assertSee($header['title']);
        $response->assertSee($header['subtitle']);

        foreach ($header['actions'] as $action) {
            $response->assertSee($action['label']);
        }

        $response->assertSee('x-data="directorAccess(', false);
        $response->assertSee('exportMatrix()', false);
        $response->assertSee('regenerateToken()', false);
        $response->assertSee('createUser()', false);
        $response->assertSee('syncRbacPolicy()', false);
        $response->assertSee('x-model="userSearch"', false);
        $response->assertSee('matchesUser(', false);
        $response->assertSee('roleClass(', false);
        $this->assertSame(0, DirectorAccessData::ACCESS_INCIDENTS);
        $this->assertSame('100%', DirectorAccessData::TWO_FA_COVERAGE);
    }

    public function test_access_console_is_linked_from_sidebar_and_governance_page(): void
    {
        $response = $this->get(route('director.access'));

        $response->assertOk();
        $response->assertSee('href="'.route('director.access').'"', false);

        $this->get(route('director.governance'))
            ->assertOk()
            ->assertSee('href="'.route('director.access').'"', false);
    }

    public function test_access_console_lives_on_its_own_route(): void
    {
        $this->assertNotSame(route('director.governance'), route('director.access'));
        $this->assertStringContainsString('kelola-akun-rbac', route('director.access'));

        $this->get(route('director.governance'))
            ->assertOk()
            ->assertDontSee('Matriks Hak Akses &amp; Wewenang 5 Peran', false);
    }
}
