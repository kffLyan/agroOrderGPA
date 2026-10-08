<?php

namespace Tests\Feature\Armada;

use App\Models\User;
use App\Support\ArmadaStatusData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ArmadaStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_preview_status_console_with_demo_operator(): void
    {
        $response = $this->get(route('armada.status'));

        $response->assertOk();
        $response->assertSee('AGROORDER GPA');
        $response->assertSee('Armada Logistik - [D 8888 ABC]', false);
        $response->assertSee('STATUS TELEMETRI KENDARAAN');
    }

    public function test_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Joko Prasetyo']);

        $response = $this->actingAs($user)->get(route('armada.status'));

        $response->assertOk();
        $response->assertSee('Joko Prasetyo');
        $response->assertSee('JP');
    }

    public function test_console_renders_operational_telemetry_banner(): void
    {
        $response = $this->get(route('armada.status'));

        $response->assertOk();
        $response->assertSee('OPERASIONAL');
        $response->assertSee('AKTIF');
        $response->assertSee('LIVE SYNC');
        $response->assertSee('syncLabel()', false);
    }

    public function test_console_renders_driver_and_unit_identity(): void
    {
        $response = $this->get(route('armada.status'));

        $response->assertOk();
        $response->assertSee('BAGIAN 01 // DATA PENGEMUDI &amp; UNIT', false);
        $response->assertSee('KODE: DRV-');
        $response->assertSee('SUPIR-GPA-08', false);
        $response->assertSee('PANGKALAN: STA HUB BOGOR-04', false);
        $response->assertSee('SIM B1: EXP 2028-11', false);
        $response->assertSee('Engkel CDD');
        $response->assertSee('Reefer');
        $response->assertSee('Unit Tag #03', false);
        $response->assertSee('B 9284 TDA', false);
        $response->assertSee('Validasi Dishub DKI', false);
    }

    public function test_console_renders_iot_sensor_readings(): void
    {
        $response = $this->get(route('armada.status'));

        $response->assertOk();
        $response->assertSee('BAGIAN 02 // TELEMETRI IOT TERKONEKSI', false);
        $response->assertSee('SUHU CHILLER BOX (KOMPARTEMEN SAYURAN)', false);
        $response->assertSee('NORMAL (TARGET OK)', false);
        $response->assertSee('+2.0', false);
        $response->assertSee('1.986 / 3.000 kg', false);
        $response->assertSee('(66.2%)', false);
        $response->assertSee('78% Solar Dex', false);
        $response->assertSee('Odo: 48.210 KM Total', false);
        $response->assertSee('ONLINE 4G', false);
        $response->assertSee('11 Satelit &amp;bull; Latensi', false);
        $response->assertSee('38ms', false);
    }

    public function test_console_renders_pre_trip_audit_and_mechanic_signoff(): void
    {
        $response = $this->get(route('armada.status'));

        $response->assertOk();
        $response->assertSee('BAGIAN 03 // AUDIT PRE-TRIP', false);
        $response->assertSee('5/5 LOLOS');
        $response->assertSee('VERIFIKASI');
        $response->assertSee('Suhu Chiller Box Pre-cooling normal');
        $response->assertSee('Tekanan Ban (75 PSI) &amp; Rem', false);
        $response->assertSee('Dokumen KIR &amp; STNK Asli Aktif', false);
        $response->assertSee('APAR (Tabung 3kg) &amp; Kotak P3K', false);
        $response->assertSee('Kebersihan Box Bebas Residu &amp; Bau', false);
        $response->assertSee('Agus Suprapto (NPP: MK-441)', false);
        $response->assertSee('SIGN: 05:30');
    }

    public function test_console_renders_shift_performance_summary(): void
    {
        $response = $this->get(route('armada.status'));

        $response->assertOk();
        $response->assertSee('BAGIAN 04 // REKAP KINERJA', false);
        $response->assertSee('Shift Pagi &bull; R-01 (Bogor-Jkt)', false);
        $response->assertSee('TITIK PENGIRIMAN');
        $response->assertSee('2 Titik', false);
        $response->assertSee('TOTAL TONASE');
        $response->assertSee('1.986,5 kg', false);
        $response->assertSee('100% SLA', false);
        $response->assertSee('RETUR LAPANGAN');
        $response->assertSee('1 Kasus (10 kg)', false);
        $response->assertSee('BA-04 kompensasi');
    }

    public function test_console_renders_support_hotline_and_shift_closure(): void
    {
        $response = $this->get(route('armada.status'));

        $response->assertOk();
        $response->assertSee('BAGIAN 05 // DUKUNGAN OPERASIONAL', false);
        $response->assertSee('HOTLINE');
        $response->assertSee('24/7', false);
        $response->assertSee('LAPOR KENDALA KENDARAAN', false);
        $response->assertSee('Ext-402 (Hotline: 021-884-9021)', false);
        $response->assertSee('DOC_V3', false);
        $response->assertSee('SELESAIKAN SHIFT &amp; TUTUP', false);
        $response->assertSee('TUGAS HARIAN', false);
        $response->assertSee('cursor-not-allowed bg-surface-pill', false);
        $response->assertSee('closeShift()', false);
    }

    public function test_console_marks_fleet_navigation_as_active_and_links_siblings(): void
    {
        $response = $this->get(route('armada.status'));

        $response->assertOk();
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('href="'.route('armada.tasks').'"', false);
        $response->assertSee('href="'.route('armada.pod').'"', false);

        $active = Str::before(
            Str::after($response->getContent(), 'aria-current="page"'),
            '</span>',
        );

        $this->assertStringContainsString('Armada', $active);
    }

    public function test_status_data_reuses_shared_operator_and_navigation(): void
    {
        $status = ArmadaStatusData::for();

        $this->assertSame(ArmadaStatusData::ARMADA_PLATE, $status['header']['plate']);
        $this->assertSame('light', $status['header']['tone']);
        $this->assertSame('DRV-08', ArmadaStatusData::DRIVER_CODE);
        $this->assertSame('DRV-08', 'DRV-'.$status['driver']['code']);
        $this->assertSame('SUPIR-GPA-08', $status['driver']['unit_code']);
        $this->assertSame('Shift Pagi &bull; R-01 (Bogor-Jkt)', $status['performance']['subtitle']);
        $this->assertCount(5, $status['navigation']);
        $this->assertSame('fleet', $status['navigation'][3]['key']);
    }
}
