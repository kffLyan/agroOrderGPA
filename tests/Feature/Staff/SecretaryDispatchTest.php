<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use App\Support\SecretaryDispatchData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecretaryDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_preview_dispatch_console_with_demo_operator(): void
    {
        $response = $this->get(route('secretary.dispatch'));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSee('Sekretaris');
        $response->assertSee('ID : 007');
    }

    public function test_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Siti Rahmawati']);

        $response = $this->actingAs($user)->get(route('secretary.dispatch'));

        $response->assertOk();
        $response->assertSee('Siti Rahmawati');
        $response->assertSee('SR');
    }

    public function test_console_shows_heading_and_dock_connection_badge(): void
    {
        $response = $this->get(route('secretary.dispatch'));

        $response->assertOk();
        $response->assertSee('Penerbitan Dokumen Resmi Surat Jalan &amp;', false);
        $response->assertSee('Penugasan Armada Logistik');
        $response->assertSee('Timbangan Digital Dock #01 &amp; #02 Terhubung', false);
    }

    public function test_console_renders_dispatch_metric_cards(): void
    {
        $response = $this->get(route('secretary.dispatch'));

        $response->assertOk();
        $response->assertSee('Surat Jalan Siap Terbit');
        $response->assertSee('4 Dokumen');
        $response->assertSee('Dock Tera Valid');
        $response->assertSee('Dalam Pengiriman Supir');
        $response->assertSee('8 Armada');
        $response->assertSee('Telemetri Aktif');
        $response->assertSee('Menunggu Verifikasi POD');
        $response->assertSee('Pengiriman Tiba');
        $response->assertSee('Dokumen Dropoff');
        $response->assertSee('Selesai Hari Ini');
        $response->assertSee('11 Transaksi');
        $response->assertSee('Ledger Tertutup');
    }

    public function test_queue_lists_three_documents_with_ready_and_pending_states(): void
    {
        $response = $this->get(route('secretary.dispatch'));

        $response->assertOk();
        $response->assertSee('Antrean Penerbitan Dokumen SJ');
        $response->assertSee('Semua Gudang (SUB-04)');

        $response->assertSee('Identitas PO &amp; Klien', false);
        $response->assertSee('Estimasi Order');
        $response->assertSee('Netto Riil Sah (Timbangan)');
        $response->assertSee('Status Validasi');
        $response->assertSee('Armada &amp; Supir', false);
        $response->assertSee('Otorisasi Surat Jalan');

        $response->assertSee('ORD-GPA-202410-092');
        $response->assertSee('PT Kuliner Prima');
        $response->assertSee('450.0 kg');
        $response->assertSee('447.2 kg');
        $response->assertSee('-2.8 kg (-0.62%)');
        $response->assertSee('Sah Tera Gudang #02');
        $response->assertSee('Tera: Ir. Bambang Sutrisno');
        $response->assertSee('Isuzu Elf Box');
        $response->assertSee('B 9421 TX');
        $response->assertSee('Joko Widodo');

        $response->assertSee('ORD-GPA-202410-096');
        $response->assertSee('CV Boga Lestari Mandiri');
        $response->assertSee('1.192.5 kg');
        $response->assertSee('Sah Tera Gudang #01');
        $response->assertSee('Hino Dutro Reefer');

        $response->assertSee('ORD-GPA-202410-098');
        $response->assertSee('Belum Selesai Timbang');
        $response->assertSee('IoT Sensor Pending Lock');
        $response->assertSee('Proses Timbang (Dock #02 Sibuk)');
        $response->assertSee('Belum Dialokasikan');
        $response->assertSee('Kunci Estimasi Ditolak');

        $response->assertSee('Terbitkan Surat Jalan');
        $response->assertSee(route('prints.surat-jalan'), false);
    }

    public function test_queue_netto_totals_reconcile_with_document_preview(): void
    {
        $rows = SecretaryDispatchData::rows();
        $parse = function (string $value): float {
            $clean = str_replace(['kg', ' '], '', $value);
            $sign = str_starts_with($clean, '-') ? -1 : 1;
            $clean = ltrim($clean, '+-');

            if (! str_contains($clean, '.')) {
                return $sign * (float) $clean;
            }

            $parts = explode('.', $clean);
            $fraction = (float) ('.'.array_pop($parts));
            $whole = (float) (implode('', $parts) ?: '0');

            return $sign * ($whole + $fraction);
        };

        $this->assertCount(3, $rows);

        foreach ($rows as $row) {
            if ($row['state'] !== 'ready') {
                $this->assertNull($row['netto']);

                continue;
            }

            $estimate = $parse($row['estimate']);
            $netto = $parse($row['netto']);
            $delta = $parse($row['document']['delta']);

            $this->assertEqualsWithDelta($estimate - $netto, abs($delta), 0.001);
            $this->assertEqualsWithDelta($netto, $parse($row['document']['netto']), 0.001);
            $this->assertEqualsWithDelta($delta, $parse(strtok($row['deviation'], '(')), 0.001);

            $lineNetto = array_sum(array_map(
                fn ($line) => $parse($line['netto']),
                $row['document']['lines']
            ));

            $this->assertEqualsWithDelta($netto, $lineNetto, 0.001);
        }
    }

    public function test_console_mounts_alpine_component_with_release_flow(): void
    {
        $response = $this->get(route('secretary.dispatch'));

        $response->assertOk();
        $response->assertSee('secretaryDispatch(', false);
        $response->assertSee('matchesFilter(', false);
        $response->assertSee('readyCount()', false);
        $response->assertSee('pendingCount()', false);
        $response->assertSee('issuedCount()', false);
        $response->assertSee('confirmRelease()', false);
        $response->assertSee('saveDraft()', false);
        $response->assertSee('x-model', false);
    }

    public function test_console_renders_rule_05_release_confirmation_bar(): void
    {
        $response = $this->get(route('secretary.dispatch'));

        $response->assertOk();
        $response->assertSee('Rilis dan Selesaikan Surat Jalan Ini?');
        $response->assertSee('Simpan Draft');
        $response->assertSee('Konfirmasi &amp; Terbitkan SJ Resmi', false);
    }

    public function test_navigation_marks_dispatch_module_as_active(): void
    {
        $response = $this->get(route('secretary.dispatch'));

        $response->assertOk();
        $response->assertSee('href="'.route('secretary.dispatch').'"', false);
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('Stok');
        $response->assertSee('Verifikasi Pesanan');
    }
}
