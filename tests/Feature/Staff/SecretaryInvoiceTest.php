<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use App\Support\SecretaryInvoiceData;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecretaryInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_preview_invoicing_console_with_demo_operator(): void
    {
        $response = $this->get(route('secretary.invoicing'));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSee('Sekretaris');
        $response->assertSee('ID : 007');
    }

    public function test_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Siti Rahmawati']);

        $response = $this->actingAs($user)->get(route('secretary.invoicing'));

        $response->assertOk();
        $response->assertSee('Siti Rahmawati');
        $response->assertSee('SR');
    }

    public function test_console_shows_heading_and_invoice_actions(): void
    {
        $response = $this->get(route('secretary.invoicing'));

        $response->assertOk();
        $response->assertSee('Manajemen Faktur Penagihan &amp;', false);
        $response->assertSee('Monitoring Tempo (TOP) Klien B2B');
        $response->assertSee('Ekspor CSV/Ledger');
        $response->assertSee('+ Faktur Manual Baru');
    }

    public function test_console_renders_receivable_metric_cards(): void
    {
        $response = $this->get(route('secretary.invoicing'));

        $response->assertOk();
        $response->assertSee('Total Piutang Berjalan (AR)');
        $response->assertSee('Rp 248.500.000');
        $response->assertSee('18 Klien Kontrak B2B Aktif');
        $response->assertSee('100% Termonitor');

        $response->assertSee('Faktur Tempo (&lt; 7 Hari)', false);
        $response->assertSee('Rp 45.200.000');
        $response->assertSee('5 Faktur Butuh Follow-up Sekre');
        $response->assertSee('Perhatian Segera');

        $response->assertSee('Faktur Overdue (&gt; Jatuh Tempo)', false);
        $response->assertSee('Rp 18.000.000');
        $response->assertSee('1 Klien Auto-Blocked');
        $response->assertSee('Auto-Freeze Aktif');

        $response->assertSee('Faktur Terbayar Bulan Ini');
        $response->assertSee('Rp 185.300.000');
        $response->assertSee('74.6% TOP Cleared');
        $response->assertSee('Status: Lancar');
    }

    public function test_console_renders_multi_sj_generator_panel(): void
    {
        $response = $this->get(route('secretary.invoicing'));

        $response->assertOk();
        $response->assertSee('Generator Faktur Konsolidasi Surat Jalan', false);
        $response->assertSee('Pilih Klien');
        $response->assertSee('Limit Plafon Kredit:');
        $response->assertSee('SISA:');
        $response->assertSee('Terfilter Otomatis oleh GPA-Engine');
    }

    public function test_generator_lists_three_eligible_surat_jalan_documents(): void
    {
        $response = $this->get(route('secretary.invoicing'));

        $response->assertOk();
        $response->assertSee('Surat Jalan Siap Difakturkan', false);
        $response->assertSee('3 Dokumen Valid');

        $response->assertSee('No. Surat Jalan');
        $response->assertSee('Tanggal Kirim');
        $response->assertSee('Komoditas &amp; Bobot Netto Timbangan', false);
        $response->assertSee('Verifikasi Penerima / POD');
        $response->assertSee('Nilai Tagihan Netto');

        $response->assertSee('SJ-0102');
        $response->assertSee('22 Okt 2024');
        $response->assertSee('Sayur Segar Grade-A');
        $response->assertSee('840 kg');
        $response->assertSee('Diterima Chef Hendra (Cap Sah)');
        $response->assertSee('Rp 21.400.000');

        $response->assertSee('SJ-0108');
        $response->assertSee('Bumbu Olahan &amp; Unggas Segar', false);
        $response->assertSee('620 kg');
        $response->assertSee('Timbangan Sesuai Faktur (QC Approved)');
        $response->assertSee('Rp 48.300.000');

        $response->assertSee('SJ-0115');
        $response->assertSee('Buah Tropis Pilihan Export');
        $response->assertSee('510 kg');
        $response->assertSee('Cap Basah Gudang Ok (Sdr. Rian)');
        $response->assertSee('Rp 44.500.000');
    }

    public function test_generator_shows_consolidation_summary_and_generate_action(): void
    {
        $response = $this->get(route('secretary.invoicing'));

        $response->assertOk();
        $response->assertSee('Total Dokumen Terpilih');
        $response->assertSee('3 Surat Jalan (1.970 KG)');
        $response->assertSee('Syarat &amp; Jatuh Tempo Konsolidasi', false);
        $response->assertSee('45 Hari Kalender (Jatuh Tempo: 08 Des 2024)');
        $response->assertSee('Total Tagihan Faktur');
        $response->assertSee('Rp 114.200.000');
        $response->assertSee('Generate &amp; Terbitkan Dokumen Faktur Konsolidasi', false);
    }

    public function test_client_documents_reconcile_with_consolidated_ledger_invoices(): void
    {
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        $clients = collect(SecretaryInvoiceData::clients())->keyBy('id');
        $ledger = collect(SecretaryInvoiceData::ledgerRows())->keyBy('id');

        $totals = $clients->map(fn ($client) => [
            'weight' => array_sum(array_column($client['documents'], 'weight')),
            'value' => array_sum(array_column($client['documents'], 'value')),
            'count' => count($client['documents']),
        ]);

        $this->assertSame(1970, $totals['aerofood']['weight']);
        $this->assertSame(114200000, $totals['aerofood']['value']);
        $this->assertSame(3, $totals['aerofood']['count']);

        $this->assertSame(114200000, $ledger['INV-2024-10-0089']['total']);
        $this->assertSame($totals['aerofood']['count'], $ledger['INV-2024-10-0089']['sj_count']);
        $this->assertSame($totals['ambarrukmo']['value'], $ledger['INV-2024-09-0214']['total']);
        $this->assertSame($totals['segar-makmur']['value'], $ledger['INV-2024-09-0150']['total']);

        $aerofood = $clients['aerofood'];
        $consolidated = $ledger['INV-2024-10-0089'];

        $this->assertSame($aerofood['terms_days'], $consolidated['terms_days']);

        $due = (new DateTimeImmutable($aerofood['issue_date']))->modify('+'.$aerofood['terms_days'].' days');
        $formatted = sprintf(
            '%02d %s %d',
            (int) $due->format('j'),
            $months[(int) $due->format('n') - 1],
            $due->format('Y'),
        );

        $this->assertSame('08 Des 2024', $formatted);
        $this->assertSame($formatted, $consolidated['due']);
    }

    public function test_console_renders_invoice_ledger_and_top_filters(): void
    {
        $response = $this->get(route('secretary.invoicing'));

        $response->assertOk();
        $response->assertSee('Ledger Faktur &amp; Monitoring Penagihan Tempo (TOP)', false);
        $response->assertSee('Semua (42)');
        $response->assertSee('TOP 14');
        $response->assertSee('TOP 30');
        $response->assertSee('TOP 45');
        $response->assertSee('Filter Status');

        $response->assertSee('No. Faktur &amp; Tanggal', false);
        $response->assertSee('Nama Klien Korporat B2B');
        $response->assertSee('SJ Tersambung');
        $response->assertSee('Total Tagihan (Rp)');
        $response->assertSee('Syarat Bayar');
        $response->assertSee('Jatuh Tempo');
        $response->assertSee('Sisa Hari / Status');
        $response->assertSee('Status Tagihan');
        $response->assertSee('Aksi Dokumen');
    }

    public function test_ledger_lists_five_invoices_with_top_status(): void
    {
        $response = $this->get(route('secretary.invoicing'));

        $response->assertOk();
        $response->assertSee('INV-2024-10-0089');
        $response->assertSee('24 Okt 2024');
        $response->assertSee('(Konsolidasi)');
        $response->assertSee('PT Aerofood ACS (Garuda Group)');
        $response->assertSee('GRP-B2B-AERO-09');
        $response->assertSee('3 SJ');
        $response->assertSee('Rp 114.200.000');
        $response->assertSee('08 Des 2024');
        $response->assertSee('45 Hari Lagi');

        $response->assertSee('INV-2024-09-0214');
        $response->assertSee('Royal Ambarrukmo Hotel');
        $response->assertSee('HTL-YOG-AMB-02');
        $response->assertSee('Rp 59.550.000');
        $response->assertSee('28 Okt 2024');
        $response->assertSee('H-3 Jatuh Tempo');
        $response->assertSee('Perlu Follow-up');

        $response->assertSee('INV-2024-09-0150');
        $response->assertSee('PT Segar Makmur Ritel');
        $response->assertSee('RTL-SGM-JKT-11');
        $response->assertSee('// Suspended');
        $response->assertSee('Rp 18.000.000');
        $response->assertSee('26 Sep 2024');
        $response->assertSee('Overdue 12 Hari');
        $response->assertSee('Auto-Blocked');

        $response->assertSee('INV-2024-10-0044');
        $response->assertSee('Restoran Padang Nusantara');
        $response->assertSee('Rp 28.750.000');
        $response->assertSee('29 Okt 2024');
        $response->assertSee('4 Hari Lagi');

        $response->assertSee('INV-2024-10-0012');
        $response->assertSee('RS Akademik Logistik (Instalasi Gizi)');
        $response->assertSee('RSA-GZ-SLM-01');
        $response->assertSee('Rp 28.000.000');
        $response->assertSee('19 Okt 2024');
        $response->assertSee('Terbayar 18 Okt');
        $response->assertSee('Lunas');
    }

    public function test_console_mounts_alpine_component_with_generation_and_top_filters(): void
    {
        $response = $this->get(route('secretary.invoicing'));

        $response->assertOk();
        $response->assertSee('secretaryInvoicing(', false);
        $response->assertSee('switchClient(', false);
        $response->assertSee('toggleDocument(', false);
        $response->assertSee('toggleAll()', false);
        $response->assertSee('belongsToClient(', false);
        $response->assertSee('creditSisa()', false);
        $response->assertSee('documentsLabel()', false);
        $response->assertSee('dueLabel()', false);
        $response->assertSee('totalValue()', false);
        $response->assertSee('generate()', false);
        $response->assertSee('setTerms(', false);
        $response->assertSee('cycleStatus()', false);
        $response->assertSee('termCount(', false);
        $response->assertSee('visibleRows()', false);
        $response->assertSee('exportLedger()', false);
        $response->assertSee('manualInvoice()', false);
        $response->assertSee('viewInvoice(', false);
        $response->assertSee('printInvoice(', false);
        $response->assertSee('followUp(', false);
        $response->assertSee('x-text', false);
    }

    public function test_navigation_marks_invoicing_module_as_active(): void
    {
        $response = $this->get(route('secretary.invoicing'));

        $response->assertOk();
        $response->assertSee('href="'.route('secretary.invoicing').'"', false);
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('Stok');
        $response->assertSee('Surat Jalan');
        $response->assertSee('Verifikasi Pesanan');
    }
}
