<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use App\Support\SecretaryInventoryData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecretaryInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_preview_inventory_console_with_demo_operator(): void
    {
        $response = $this->get(route('secretary.inventory'));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSee('Sekretaris');
        $response->assertSee('ID : 007');
    }

    public function test_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Siti Rahmawati']);

        $response = $this->actingAs($user)->get(route('secretary.inventory'));

        $response->assertOk();
        $response->assertSee('Siti Rahmawati');
        $response->assertSee('SR');
    }

    public function test_console_shows_heading_and_toolbar_actions(): void
    {
        $response = $this->get(route('secretary.inventory'));

        $response->assertOk();
        $response->assertSee('Manajemen Multi-Source Inventory &amp; Available- to-Promise (ATP)', false);
        $response->assertSee('Anti Zero-Overselling Guard Aktif');
        $response->assertSee('Sinkronisasi Data Panen Koordinator');
        $response->assertSee('Ekspor Neraca Stok CSV');
        $response->assertSee('+ Alokasi Buffer Manual');
    }

    public function test_console_renders_rule_engine_safeguards(): void
    {
        $response = $this->get(route('secretary.inventory'));

        $response->assertOk();
        $response->assertSee('GPA Logistics Rule Engine // Automation Safeguards');
        $response->assertSee('Status: 100% Operational');
        $response->assertSee('Rule 02: Hard Lock Kuota PO Terkonfirmasi');
        $response->assertSee('Strict');
        $response->assertSee('garansi zero stock-out');
        $response->assertSee('Rule 03: Auto-Switching ke Buffer Mitra Luar');
        $response->assertSee('Auto &lt;20%', false);
        $response->assertSee('di bawah 20% demand PO terbuka', false);
    }

    public function test_console_renders_supply_and_atp_metric_cards(): void
    {
        $response = $this->get(route('secretary.inventory'));

        $response->assertOk();
        $response->assertSee('Total Pasokan Tersedia');
        $response->assertSee('3.360');
        $response->assertSee('1.480 kg');
        $response->assertSee('1.880 kg');
        $response->assertSee('Komposisi Pasokan');
        $response->assertSee('Binaan:');
        $response->assertSee('2.620 kg');
        $response->assertSee('740 kg');
        $response->assertSee('Alokasi Terkunci PO');
        $response->assertSee('2.450');
        $response->assertSee('18 Pesanan Aktif Terikat');
        $response->assertSee('72.9% Kapasitas');
        $response->assertSee('Sisa Kuota Bebas (ATP)');
        $response->assertSee('Ready to Promise');
        $response->assertSee('Non-Reserved');
    }

    public function test_console_renders_operational_filters_and_bulk_lock_action(): void
    {
        $response = $this->get(route('secretary.inventory'));

        $response->assertOk();
        $response->assertSee('Komoditas Inti');
        $response->assertSee('Semua Komoditas (5 Standar)');
        $response->assertSee('Sumber Pasokan');
        $response->assertSee('Semua Sumber (Multi-Source)');
        $response->assertSee('Status Kuota &amp; ATP', false);
        $response->assertSee('Semua Status Operasional');
        $response->assertSee('Hanya Tampilkan Kuota &lt; 20%', false);
        $response->assertSee('Kunci Kuota Massal');
    }

    public function test_ledger_lists_five_commodities_with_lock_and_atp_columns(): void
    {
        $response = $this->get(route('secretary.inventory'));

        $response->assertOk();
        $response->assertSee('Neraca Pasokan Multi-Sumber Per Komoditas Inti');
        $response->assertSee('5 Komoditas Standar GPA');
        $response->assertSee('Mutasi Terakhir: Hari Ini 11:24 WIB');
        $response->assertSee('Sumber Petani Binaan');
        $response->assertSee('Sumber Buffer Luar');
        $response->assertSee('Terkunci PO B2B (Rule 02)');
        $response->assertSee('Sisa Kuota Bebas (ATP)');
        $response->assertSee('Status &amp; Aksi Admin', false);

        $response->assertSee('Selada Romaine');
        $response->assertSee('SKU: VEG-ROM-002');
        $response->assertSee('640 kg');
        $response->assertSee('240 kg');

        $response->assertSee('Tomat Beef Super');
        $response->assertSee('SKU: TOM-BEEF-014');
        $response->assertSee('Aktif Injeksi 35%');

        $response->assertSee('Brokoli Highland');
        $response->assertSee('SKU: BRK-SUP-007');

        $response->assertSee('Stroberi Ciwidey');
        $response->assertSee('Buffer Hampir Habis');
        $response->assertSee('Defisit Ringan');
        $response->assertSee('Req Tambahan');

        $response->assertSee('Kol Putih Organik');
        $response->assertSee('SKU: KOL-PUT-003');
        $response->assertSee('Kuota Aman');

        $response->assertSee('Total Kuota Bebas (ATP):');
        $response->assertSee('Pagination: Locked to Single Operational Ledger');
    }

    public function test_ledger_totals_reconcile_with_metric_cards(): void
    {
        $rows = SecretaryInventoryData::rows();
        $total = array_sum(array_map(fn ($row) => (int) $row['total_value'], $rows));
        $locked = array_sum(array_map(fn ($row) => (int) $row['locked_value'], $rows));
        $atp = array_sum(array_map(fn ($row) => (int) $row['atp_value'], $rows));
        $binawan = array_sum(array_map(fn ($row) => (int) $row['binawan_value'], $rows));
        $buffer = array_sum(array_map(fn ($row) => (int) $row['buffer_value'], $rows));

        $this->assertCount(5, $rows);
        $this->assertSame(3360, $total);
        $this->assertSame(2620, $binawan);
        $this->assertSame(740, $buffer);
        $this->assertSame(2450, $locked);
        $this->assertSame(910, $atp);
        $this->assertSame($total, $locked + $atp);
    }

    public function test_console_mounts_alpine_component_with_filter_wiring(): void
    {
        $response = $this->get(route('secretary.inventory'));

        $response->assertOk();
        $response->assertSee('secretaryInventory(', false);
        $response->assertSee('matchesFilter(', false);
        $response->assertSee('visibleCount()', false);
        $response->assertSee('atpFooter()', false);
        $response->assertSee('criticalOnly', false);
        $response->assertSee('x-model="commodity"', false);
        $response->assertSee('x-model="source"', false);
        $response->assertSee('x-model="status"', false);
    }

    public function test_console_renders_realtime_mutation_log_and_readiness_checks(): void
    {
        $response = $this->get(route('secretary.inventory'));

        $response->assertOk();
        $response->assertSee('Log Mutasi &amp; Eksekusi Penguncian Stok Realtime', false);
        $response->assertSee('Stream: Live Feed');
        $response->assertSee('Hard Lock: 180 kg Selada Romaine');
        $response->assertSee('PO-B2B-8821');
        $response->assertSee('Auto-Switch: Injeksi Buffer 85 kg Tomat Beef');
        $response->assertSee('Batch-PK-092');
        $response->assertSee('Lihat Audit Trail Lengkap');

        $response->assertSee('Kesiapan DO Batch &amp; Cold-Chain', false);
        $response->assertSee('18/18 PO Siap');
        $response->assertSee('Validasi Timbangan Hub Lembang');
        $response->assertSee('Kalibrasi Digital: Akurasi ±0.05 kg');
        $response->assertSee('Suhu Cold-Chain Chiller (2°C - 6°C)');
        $response->assertSee('Sensor Realtime: Saat ini 3.8°C (Stabil)');
        $response->assertSee('Otorisasi Surat Jalan Sekretariat');
        $response->assertSee('Lanjut ke Cetak Surat Jalan Massal');
    }

    public function test_navigation_marks_inventory_module_as_active(): void
    {
        $response = $this->get(route('secretary.inventory'));

        $response->assertOk();
        $response->assertSee('href="'.route('secretary.inventory').'"', false);
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('Verifikasi Pesanan');
    }
}
