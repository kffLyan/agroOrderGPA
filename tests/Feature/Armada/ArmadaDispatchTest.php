<?php

namespace Tests\Feature\Armada;

use App\Models\User;
use App\Support\ArmadaDispatchData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ArmadaDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_preview_dispatch_letter_with_demo_operator(): void
    {
        $response = $this->get(route('armada.dispatch'));

        $response->assertOk();
        $response->assertSee('AGROORDER GPA');
        $response->assertSee('Armada Logistik - [D 8888 ABC]', false);
        $response->assertSee('Surat Jalan Digital & Rincian<br>Muatan Supir', false);
        $response->assertSee('Verifikasi kesesuaian fisik muatan & segel timbangan tera<br>sebelum keberangkatan.', false);
    }

    public function test_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Joko Prasetyo']);

        $response = $this->actingAs($user)->get(route('armada.dispatch'));

        $response->assertOk();
        $response->assertSee('Joko Prasetyo');
        $response->assertSee('JP');
    }

    public function test_console_renders_two_stop_route_summary(): void
    {
        $response = $this->get(route('armada.dispatch'));

        $response->assertOk();
        $response->assertSee('RUTE PENGANTARAN (2 STOPS)', false);
        $response->assertSee('MULTI-DROP HARIAN', false);
        $response->assertSee('STOP #1');
        $response->assertSee('SIAP BERANGKAT');
        $response->assertSee('SJ-202610-0001', false);
        $response->assertSee('PT Kuliner Prima Nusantara');
        $response->assertSee('Muatan Sah:', false);
        $response->assertSee('795.0 kg', false);
        $response->assertSee('STOP #2');
        $response->assertSee('MENUNGGU RUTE #1', false);
        $response->assertSee('SJ-202610-0002', false);
        $response->assertSee('CV Boga Lestari Mandiri');
        $response->assertSee('Muatan Est:', false);
        $response->assertSee('836.5 kg', false);
    }

    public function test_console_renders_letter_header_and_recipient_chain(): void
    {
        $response = $this->get(route('armada.dispatch'));

        $response->assertOk();
        $response->assertSee('TERVALIDASI DISPATCH DOCK-03', false);
        $response->assertSee('SJ-GPA-202610-0001', false);
        $response->assertSee('#ORD-GPA-202610-0042', false);
        $response->assertSee('WAKTU DISPATCH', false);
        $response->assertSee('24 Okt 2026', false);
        $response->assertSee('05:42 WIB');
        $response->assertSee('PENGIRIM LOGISTIK', false);
        $response->assertSee('PT Agro Pasti Ada');
        $response->assertSee('Sentral Transhipment Hub Bogor - Dock 03', false);
        $response->assertSee('DOKUMEN PENERIMA (DESTINASI #1)', false);
        $response->assertSee('Central Kitchen Ciracas Hub, Jl. Raya Bogor KM<br>28, Jakarta Timur', false);
    }

    public function test_console_renders_receiver_pic_contact_actions(): void
    {
        $response = $this->get(route('armada.dispatch'));

        $response->assertOk();
        $response->assertSee('PIC GUDANG PENERIMA', false);
        $response->assertSee('Pak Hendra Gunawan');
        $response->assertSee('0812-3456-7890', false);
        $response->assertSee('Hubungi PIC', false);
        $response->assertSee('Buka Navigasi<br>Peta', false);
        $response->assertSee('callPic()', false);
        $response->assertSee('openMap()', false);
    }

    public function test_console_renders_metrology_stamp_and_cargo_breakdown(): void
    {
        $response = $this->get(route('armada.dispatch'));

        $response->assertOk();
        $response->assertSee('PRD RULE 04 &amp; 05 METROLOGY SAH', false);
        $response->assertSee('Stempel Metrologi Tera Disperindag Sah', false);
        $response->assertSee('SK Tera No. 510/PKTN/ML/XI/2024 &bull; Tera Sah<br>Digital Transhipment', false);
        $response->assertSee('SKU: VEG-ROM-01', false);
        $response->assertSee('Selada Romaine Super');
        $response->assertSee('Grade A Horeca &bull; 25 Krat Berlubang<br>Steril', false);
        $response->assertSee('SKU: VEG-TMT-04', false);
        $response->assertSee('Tomat Beef Dataran Tinggi');
        $response->assertSee('Grade A &bull; 15 Krat', false);
        $response->assertSee('PASSED <2%', false);
        $response->assertSee('Est PO');
        $response->assertSee('Netto Sah');
        $response->assertSee('Deviasi Susut');
        $response->assertSee('500.0 kg', false);
        $response->assertSee('497.0 kg', false);
        $response->assertSee('-3.0 kg', false);
        $response->assertSee('(-0.60%)', false);
        $response->assertSee('300.0 kg', false);
        $response->assertSee('298.0 kg', false);
        $response->assertSee('-2.0 kg', false);
        $response->assertSee('(-0.67%)', false);
        $response->assertSee('Wadah: 25 Krat', false);
        $response->assertSee('Tara: 50.0 kg', false);
        $response->assertSee('Wadah: 15 Krat', false);
        $response->assertSee('Tara: 30.0 kg', false);
    }

    public function test_console_renders_weight_totals_with_cold_chain_note(): void
    {
        $response = $this->get(route('armada.dispatch'));

        $response->assertOk();
        $response->assertSee('TOTAL AKUMULASI TIMBANG SAH (STOP #1)', false);
        $response->assertSee('Est Awal', false);
        $response->assertSee('800.0 kg', false);
        $response->assertSee('Total Netto Sah', false);
        $response->assertSee('Deviasi Sah', false);
        $response->assertSee('-5.0 kg', false);
        $response->assertSee('(-0.63%)', false);
        $response->assertSee('Wadah: 40 Krat (80.0<br>kg Tara)', false);
        $response->assertSee('+3.8', false);
        $response->assertSee('C (Optimal<br>Sayuran Daun)', false);
    }

    public function test_console_renders_gate_pass_token_seal_and_departure_actions(): void
    {
        $response = $this->get(route('armada.dispatch'));

        $response->assertOk();
        $response->assertSee('GATE PASS SECURITY DOCK', false);
        $response->assertSee('EXP:', false);
        $response->assertSee('45:00', false);
        $response->assertSee('TOKEN: GPA-795-B9284-SEC', false);
        $response->assertSee('Tunjukkan ke pos security gerbang sentral<br>hub &amp; dock penerima.', false);
        $response->assertSee('AGRO-PASS', false);
        $response->assertSee('STATUS SEGEL KONTAINER', false);
        $response->assertSee('Segel Digital Aktif (Terkunci Otomatis)', false);
        $response->assertSee('MULAI PENGIRIMAN (ON-ROUTE)', false);
        $response->assertSee('Status GPS Logbook &amp; Reefer Tracker Aktif<br>Terkoneksi', false);
        $response->assertSee('Lapor Kendala / Selisih Muatan', false);
        $response->assertSee('startRoute()', false);
        $response->assertSee('reportIssue()', false);
    }

    public function test_console_marks_dispatch_navigation_as_active_and_links_siblings(): void
    {
        $response = $this->get(route('armada.dispatch'));

        $response->assertOk();
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('href="'.route('armada.tasks').'"', false);
        $response->assertSee('href="'.route('armada.pod').'"', false);
        $response->assertSee('href="'.route('armada.status').'"', false);

        $active = Str::before(
            Str::after($response->getContent(), 'aria-current="page"'),
            '</span>',
        );

        $this->assertStringContainsString('Surat Jalan', $active);
    }

    public function test_dispatch_data_keeps_manifest_and_navigation_in_sync(): void
    {
        $dispatch = ArmadaDispatchData::for();

        $this->assertSame('SJ-GPA-202610-0001', ArmadaDispatchData::SJ_NUMBER);
        $this->assertSame(ArmadaDispatchData::SJ_NUMBER, $dispatch['letter']['number']);
        $this->assertCount(2, $dispatch['route']['stops']);
        $this->assertCount(2, $dispatch['cargo']['items']);
        $this->assertSame(45, $dispatch['gatePass']['expiry_minutes']);
        $this->assertSame('light', $dispatch['header']['tone']);
        $this->assertSame('dispatch', $dispatch['navigation'][1]['key']);
    }
}
