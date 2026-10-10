<?php

namespace Tests\Feature\Armada;

use App\Models\User;
use App\Support\ArmadaPodData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArmadaPodTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_preview_pod_console_with_demo_operator(): void
    {
        $response = $this->get(route('armada.pod'));

        $response->assertOk();
        $response->assertSee('AGROORDER GPA');
        $response->assertSee('Armada Logistik');
        $response->assertSee('D 8888 ABC');
        $response->assertSee('AF');
    }

    public function test_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Asep Firmansyah']);

        $response = $this->actingAs($user)->get(route('armada.pod'));

        $response->assertOk();
        $response->assertSee('Asep Firmansyah');
        $response->assertSee('AF');
    }

    public function test_console_renders_target_delivery_summary(): void
    {
        $response = $this->get(route('armada.pod'));

        $response->assertOk();
        $response->assertSee('TARGET PENYERAHAN #SJ-GPA-202610-0001');
        $response->assertSee('DOKUMEN AKTIF');
        $response->assertSee('Katering Berkah Mandiri');
        $response->assertSee('PT Kuliner Prima Nusantara');
        $response->assertSee('Ciracas Docking C-2');
        $response->assertSee('Tajur - Kab. Bogor');
        $response->assertSee('795');
        $response->assertSee('KG Bersih');
        $response->assertSee('Hortikultura');
        $response->assertSee('Pak Hendra');
        $response->assertSee('Dock Head');
    }

public function test_console_renders_three_mandatory_capture_steps(): void
    {
        $response = $this->get(route('armada.pod'));

        $response->assertOk();
        $response->assertSee('Foto Fisik Surat Jalan');
        $response->assertSee('(Cap Basah)');
        $response->assertSee('WAJIB CAP DOCK');
        $response->assertSee('VIEWFINDER RATIO 3:4 OCR READY');
        $response->assertSee('ISO AUTO // RES 1080x1440');
        $response->assertSee('HIGH CONTRAST');
        $response->assertSee('READY TO CAPTURE');
        $response->assertSee('Ambil Foto Surat Jalan');
        $response->assertSee('(Geo-Lock)');
        $response->assertSee('SENSOR TELEMETRI');
        $response->assertSee('GEO: -6.6124, 106.8142 (Ciracas)');
        $response->assertSee('RAD: 12M OK');
        $response->assertSee('07:34:12 WIB');
        $response->assertSee('WIDE LENS (0.5X)');
        $response->assertSee('DOCK BONGKAR C-2');
        $response->assertSee('OPTICAL GRID: LOCKED');
        $response->assertSee('AMBIENT LUX: 420 lx');
        $response->assertSee('Tanda Tangan Digital');
        $response->assertSee('CANVAS SENTUH ELEKTRONIK');
        $response->assertSee('Hendra Kurniawan');
    }

public function test_console_renders_pre_upload_checklist(): void
    {
        $response = $this->get(route('armada.pod'));

        $response->assertOk();
        $response->assertSee('VERIFIKASI PRA-UNGGAH:');
        $response->assertSee('Cap stempel basah penerima terbaca jelas');
        $response->assertSee('Tanda tangan PIC penerima ada');
    }

    public function test_console_renders_condition_options_with_clear_option_selected(): void
    {
        $response = $this->get(route('armada.pod'));

        $response->assertOk();
        $response->assertSee('Status Kondisi Muatan &amp; Retur', false);
        $response->assertSee('Barang Diterima Utuh Tanpa Retur (795 KG)');
        $response->assertSee('SEALED');
        $response->assertSee('Ada Selisih / Retur Lapangan');
        $response->assertSee('DISCREPANCY');
        $response->assertSee('kompensasi kuota');

        $response->assertSee('name="pod-condition" value="clear"', false);
        $response->assertSee('name="pod-condition" value="discrepancy"', false);
    }

    public function test_console_renders_lock_pod_action_and_hub_contact(): void
    {
        $response = $this->get(route('armada.pod'));

        $response->assertOk();
        $response->assertSee('VALIDASI &amp; SELESAIKAN PENGIRIMAN', false);
        $response->assertSee('(LOCK POD)');
        $response->assertSee('Otomatis mengunci status Selesai di Pusat &amp; menerbitkan Invoice B2B.', false);
        $response->assertSee('Kendala bongkar muat dock?');
        $response->assertSee('Hubungi Hub Bogor');
        $response->assertSee('(0811-9988-77)');
    }

    public function test_console_renders_bottom_navigation_with_pod_active(): void
    {
        $response = $this->get(route('armada.pod'));

        $response->assertOk();
        $response->assertSee('Navigasi armada');

        foreach (ArmadaPodData::navigation() as $item) {
            $response->assertSee($item['label']);
        }

        $response->assertSee('aria-current="page"', false);
        $response->assertSee('href="'.route('armada.tasks').'"', false);
    }

    public function test_console_wires_alpine_component_with_step_payload(): void
    {
        $response = $this->get(route('armada.pod'));

        $response->assertOk();
        $response->assertSee('x-data="armadaPod(', false);
        $response->assertSee('openMenu(', false);
        $response->assertSee('capture(\'manifest\')', false);
        $response->assertSee('capture(\'dock\')', false);
        $response->assertSee('selectCondition(', false);
        $response->assertSee('lockPod()', false);
    }

    public function test_console_exposes_mobile_shell_metrics(): void
    {
        $response = $this->get(route('armada.pod'));

        $response->assertOk();
        $response->assertSee('viewport-fit=cover', false);
        $response->assertSee('h-14 items-center gap-2.5 bg-brand', false);
        $response->assertSee('max-w-[512px]', false);
        $response->assertSee('bottom-0 z-40', false);
    }

    public function test_console_requires_the_mandatory_steps_before_locking_pod(): void
    {
        $response = $this->get(route('armada.pod'));

        $response->assertOk();
        $response->assertSee('canLock()', false);
        $response->assertSee('blockers().join', false);
        $response->assertSee('aria-label="', false);
    }
}
