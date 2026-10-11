<?php

namespace Tests\Feature\Armada;

use App\Models\User;
use App\Support\ArmadaTasksData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ArmadaTasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_preview_task_list_with_demo_operator(): void
    {
        $response = $this->get(route('armada.tasks'));

        $response->assertOk();
        $response->assertSee('AGROORDER GPA');
        $response->assertSee('Armada Logistik - [D 8888 ABC]', false);
        $response->assertSee('Daftar Tugas Armada');
    }

    public function test_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Slamet Prasetyo']);

        $response = $this->actingAs($user)->get(route('armada.tasks'));

        $response->assertOk();
        $response->assertSee('Slamet Prasetyo');
        $response->assertSee('SP');
    }

    public function test_console_renders_assignment_and_dock_deadline(): void
    {
        $response = $this->get(route('armada.tasks'));

        $response->assertOk();
        $response->assertSee('SUPIR BERTUGAS');
        $response->assertSee('Slamet Prasetyo');
        $response->assertSee('DVR-GPA-2024-08');
        $response->assertSee('Unit CDD');
        $response->assertSee('#03');
        $response->assertSee('SIAP');
        $response->assertSee('BONGKAR');
        $response->assertSee('BATAS: 08:30 WIB', false);
    }

    public function test_console_renders_chiller_and_capacity_telemetry(): void
    {
        $response = $this->get(route('armada.tasks'));

        $response->assertOk();
        $response->assertSee('SUHU CHILLER');
        $response->assertSee('+3.8', false);
        $response->assertSee('OPTIMAL (COLD');
        $response->assertSee('TOTAL MUATAN');
        $response->assertSee('1.986');
        $response->assertSee('/3.000 kg', false);
        $response->assertSee('UTILISASI: 66.2%', false);
    }

    public function test_console_renders_manifest_card_with_traffic_radar(): void
    {
        $response = $this->get(route('armada.tasks'));

        $response->assertOk();
        $response->assertSee('MANIFEST #TRP-2502-09', false);
        $response->assertSee('2 TITIK DROP');
        $response->assertSee('TOTAL BERAT');
        $response->assertSee('1.986,5 kg', false);
        $response->assertSee('JARAK TOTAL');
        $response->assertSee('64,2 km', false);
        $response->assertSee('KORIDOR RUTE');
        $response->assertSee('JORR 2 &#10132; BSD', false);
        $response->assertSee('RADAR LALU LINTAS TOL:');
        $response->assertSee('GT Ciracas padat lancar (antrean 150m).');
    }

    public function test_console_renders_four_queue_filters_with_all_active(): void
    {
        $response = $this->get(route('armada.tasks'));

        $response->assertOk();

        foreach (ArmadaTasksData::filters() as $filter) {
            $response->assertSee($filter['label'].' ('.$filter['count'].')', false);
        }

        $response->assertSee('Antrean Titik Bongkar');
        $response->assertSee('PRD V12 COMPLIANT');
    }

    public function test_console_renders_first_drop_as_active_on_road(): void
    {
        $response = $this->get(route('armada.tasks'));

        $response->assertOk();
        $response->assertSee('DROP KE-1');
        $response->assertSee('ON ROAD');
        $response->assertSee('ETA');
        $response->assertSee('06:45 WIB');
        $response->assertSee('PRIORITAS A1');
        $response->assertSee('SJ-20250225-0042');
        $response->assertSee('PT Kuliner Prima Nusantara');
        $response->assertSee('Central Kitchen Ciracas Hub');
        $response->assertSee('RINCIAN MUATAN (75 KRAT/BOX)', false);
        $response->assertSee('NETTO: 1.150,0 KG', false);
        $response->assertSee('1. Ayam Karkas Segar');
        $response->assertSee('850,0 kg', false);
        $response->assertSee('2. Selada Romaine Hidroponik');
        $response->assertSee('300,0 kg', false);
        $response->assertSee('JENDELA TERIMA:');
        $response->assertSee('06:00 - 07:15 WIB (TERPENUHI)', false);
        $response->assertSee('PROSES POD DROP #1', false);
        $response->assertSee('CEK RUTE', false);
    }

    public function test_console_renders_second_drop_as_locked_queue(): void
    {
        $response = $this->get(route('armada.tasks'));

        $response->assertOk();
        $response->assertSee('DROP KE-2');
        $response->assertSee('ANTREAN');
        $response->assertSee('Target');
        $response->assertSee('08:00 WIB');
        $response->assertSee('STANDAR B');
        $response->assertSee('SJ-20250225-0043');
        $response->assertSee('CV Boga Lestari Mandiri');
        $response->assertSee('Gudang Dapur Serpong BSD');
        $response->assertSee('RINCIAN MUATAN (42 KARTON)', false);
        $response->assertSee('NETTO: 836,5 KG', false);
        $response->assertSee('1. Daging Ayam Parting Segar');
        $response->assertSee('600,0 kg', false);
        $response->assertSee('2. Bawang Merah Brebes Super');
        $response->assertSee('236,5 kg', false);
        $response->assertSee('STATUS');
        $response->assertSee('PROTOKOL:');
        $response->assertSee('DOKUMEN TERKUNCI S/D DROP #1');
        $response->assertSee('POD TERKUNCI', false);
        $response->assertSee('JALUR ALT.', false);
    }

    public function test_console_renders_emergency_support_and_audit_history(): void
    {
        $response = $this->get(route('armada.tasks'));

        $response->assertOk();
        $response->assertSee('BANTUAN DARURAT DISPATCH');
        $response->assertSee('(021) 884-9021', false);
        $response->assertSee('HUBUNGI');
        $response->assertSee('AUDIT PENGIRIMAN KEMARIN (100% SAH):');
        $response->assertSee('&bull; Superindo Daan Mogot (1.420 kg)', false);
        $response->assertSee('SUKSES [PoD-912]', false);
        $response->assertSee('&bull; Transmart Cempaka Putih (980 kg)', false);
        $response->assertSee('SUKSES [PoD-913]', false);
        $response->assertSee('AGRO-COMMAND MOBILE v4.2');
        $response->assertSee('GPA COMPLIANCE SECTION 6.4, 11 &amp; 12', false);
    }

    public function test_console_marks_tasks_navigation_as_active(): void
    {
        $response = $this->get(route('armada.tasks'));

        $response->assertOk();
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('href="'.route('armada.pod').'"', false);

        $active = Str::before(
            Str::after($response->getContent(), 'aria-current="page"'),
            '</span>',
        );

        $this->assertStringContainsString('Tugas', $active);
    }

    public function test_console_links_back_to_pod_console(): void
    {
        $pod = $this->get(route('armada.pod'));
        $tasks = $this->get(route('armada.tasks'));

        $pod->assertOk();
        $tasks->assertOk();
        $tasks->assertSee('href="'.route('armada.pod').'"', false);
    }

    public function test_console_wires_alpine_task_component(): void
    {
        $response = $this->get(route('armada.tasks'));

        $response->assertOk();
        $response->assertSee('armadaTasks(', false);
        $response->assertSee("filter === 'all' || filter === '", false);
        $response->assertSee('selectFilter(', false);
        $response->assertSee('checkRoute(', false);
        $response->assertSee('processPod(', false);
        $response->assertSee('callSupport()', false);
        $response->assertSee('aria-pressed=', false);
    }

    public function test_console_locks_second_drop_pod_action(): void
    {
        $response = $this->get(route('armada.tasks'));

        $response->assertOk();
        $response->assertSee('cursor-not-allowed bg-surface-pill', false);
        $response->assertSee('disabled', false);
    }
}
