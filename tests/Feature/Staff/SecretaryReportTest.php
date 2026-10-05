<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use App\Support\SecretaryReportData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecretaryReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_preview_recap_report_console_with_demo_operator(): void
    {
        $response = $this->get(route('secretary.reports'));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSee('Sekretaris');
        $response->assertSee('ID : 007');
    }

    public function test_console_renders_operator_identity_from_authenticated_user(): void
    {
        $user = User::factory()->create(['name' => 'Andi Nugroho']);

        $response = $this->actingAs($user)->get(route('secretary.reports'));

        $response->assertOk();
        $response->assertSee('Andi Nugroho');
        $response->assertSee('AN');
    }

    public function test_console_renders_heading_and_page_actions(): void
    {
        $response = $this->get(route('secretary.reports'));

        $response->assertOk();
        $response->assertSee('Sub-06 // Modul Rekapitulasi Laporan Operasional &amp; Distribusi', false);
        $response->assertSee('Laporan Rekapitulasi Operasional,');
        $response->assertSee('Distribusi &amp; Penjualan', false);
        $response->assertSee('Kompilasi audit transaksi harian, mingguan, dan bulanan berbasis data penimbangan aktual dan Surat Jalan terverifikasi (PRD App-GPA.md Section 6.2, 14, 15, &amp; 22).', false);

        $response->assertSee('Sinkronisasi Audit (04:00 WIB)');
        $response->assertSee('Ekspor Rekapitulasi (.csv / .xlsx)');
        $response->assertSee('Cetak Laporan PDF Resmi Direktur');
    }

    public function test_console_renders_accounting_filters_with_default_period(): void
    {
        $response = $this->get(route('secretary.reports'));

        $response->assertOk();
        $response->assertSee('Periode Akuntansi');
        $response->assertSee('Bulan Berjalan (Oktober 2024)');
        $response->assertSee('Kategori Klien');
        $response->assertSee('Semua Klien B2B &amp; Institusi', false);
        $response->assertSee('Portofolio Komoditas');
        $response->assertSee('5 Komoditas Inti Standar GPA');
        $response->assertSee('Status Pembayaran');
        $response->assertSee('Semua Status Tagihan');

        $response->assertSee('Terapkan Filter');
        $response->assertSee('Reset');
    }

    public function test_console_renders_operational_kpi_cards(): void
    {
        $response = $this->get(route('secretary.reports'));

        $response->assertOk();

        $response->assertSee('Volume Teralokasi &amp; Terkirim', false);
        $response->assertSee('482.65');
        $response->assertSee('Ton');
        $response->assertSee('+14.2% MoM');
        $response->assertSee('5 komoditas inti valid SJ/QC');
        $response->assertSee('Target Bulanan: 500 Ton');
        $response->assertSee('96.5% Tercapai');

        $response->assertSee('Nilai Faktur Diterbitkan');
        $response->assertSee('Rp 482.650.000');
        $response->assertSee('100% Actual Net');
        $response->assertSee('Binding PRD Rule 04/05');
        $response->assertSee('64 Total Transaksi');
        $response->assertSee('Terkunci Buku Besar');

        $response->assertSee('Ketepatan Pengiriman (SLA)');
        $response->assertSee('98.4%');
        $response->assertSee('On-Time');
        $response->assertSee('Avg 04:15 WIB');
        $response->assertSee('Tiba di loading dock klien');
        $response->assertSee('Toleransi Disposisi: &lt;05:00 WIB', false);
        $response->assertSee('0 Klaim Terlambat');

        $response->assertSee('Tingkat Susut &amp; Retur', false);
        $response->assertSee('0.22%');
        $response->assertSee('Batas &lt;0.50%', false);
        $response->assertSee('35.5 kg dari 15.8 Ton batch');
        $response->assertSee('Status Susut: Sangat Aman');
        $response->assertSee('Zero Incident');
    }

    public function test_console_renders_integrity_standards_and_prd_rules(): void
    {
        $response = $this->get(route('secretary.reports'));

        $response->assertOk();
        $response->assertSee('Standar Integritas');
        $response->assertSee('Kepatuhan Penagihan Buku Besar');
        $response->assertSee('Protokol wajib untuk menjamin validitas hukum, kepatuhan audit metrologi, dan transparansi antara supir, gudang, dan klien B2B.');
        $response->assertSee('App-GPA Engine Verif // Hash SHA-256 Enforced');

        $response->assertSee('PRD Rule 04');
        $response->assertSee('Mutlak Timbangan Sah');
        $response->assertSee('Net Weight Binding');
        $response->assertSee('Dilarang menagih berbasis estimasi PO.');
        $response->assertSee('64 transaksi terkunci timbangan sah');

        $response->assertSee('PRD Rule 05');
        $response->assertSee('Toleransi &lt;= 0.5%', false);
        $response->assertSee('Deviasi Bobot Loading Dock');
        $response->assertSee('Jika deviasi &gt; 0.5%, faktur diblokir otomatis untuk mediasi.', false);
        $response->assertSee('-0.19% rata-rata deviasi bulan ini (Lolos)');

        $response->assertSee('PRD Rule 14');
        $response->assertSee('Trifecta Audit');
        $response->assertSee('Dokumen Rantai 3-Arah');
        $response->assertSee('3 berkas sinkron 100% per invoice');
    }

    public function test_console_renders_four_recap_tabs_with_sales_tab_active(): void
    {
        $response = $this->get(route('secretary.reports'));

        $response->assertOk();
        $response->assertSee('Tab 1: Rekapitulasi Penjualan &amp; Pesanan (Aktif)', false);
        $response->assertSee('Tab 2: Volume &amp; Analisis Timbangan', false);
        $response->assertSee('Tab 3: Surat Jalan &amp; Disposisi Armada', false);
        $response->assertSee('Tab 4: Rekapitulasi Piutang &amp; Umur Faktur (Aging AR)', false);
    }

    public function test_console_renders_transaction_journal_header_and_columns(): void
    {
        $response = $this->get(route('secretary.reports'));

        $response->assertOk();
        $response->assertSee('Jurnal Buku Transaksi Riil // Actual Weight Binding Enforced');
        $response->assertSee('Menampilkan 6 dari 64 Transaksi');
        $response->assertSee('Terverifikasi Tera Metrologi #MET-2024-88');

        $response->assertSee('No Faktur / Ref SJ');
        $response->assertSee('Klien B2B &amp; Lokasi', false);
        $response->assertSee('Komoditas Utama');
        $response->assertSee('Estimasi PO');
        $response->assertSee('Net Sah Gudang');
        $response->assertSee('Deviasi');
        $response->assertSee('Nilai Final (Rp)');
        $response->assertSee('Status Bayar');
        $response->assertSee('Berkas Audit');
    }

    public function test_journal_lists_six_bound_invoice_transactions(): void
    {
        $response = $this->get(route('secretary.reports'));

        $response->assertOk();

        $response->assertSee('INV-202610-094');
        $response->assertSee('SJ-GPA-88412');
        $response->assertSee('PT Sinar Agro Mandiri');
        $response->assertSee('Pabrik Marunda, Jakut');
        $response->assertSee('Tomat Beef Super');
        $response->assertSee('29.940,0');
        $response->assertSee('-0.20%');
        $response->assertSee('Rp 359.280.000');

        $response->assertSee('INV-202610-092');
        $response->assertSee('SJ-GPA-88390');
        $response->assertSee('PT Aerofood ACS');
        $response->assertSee('Selada Romaine Grade A');
        $response->assertSee('1.192,4');
        $response->assertSee('-0.63%');
        $response->assertSee('Rp 41.734.000');

        $response->assertSee('INV-202610-091');
        $response->assertSee('Hotel Santika Premiere Jakarta');
        $response->assertSee('Brokoli &amp; Wortel', false);
        $response->assertSee('848,1');
        $response->assertSee('Rp 18.658.200');
        $response->assertSee('Tempo TOP 14');

        $response->assertSee('INV-202610-089');
        $response->assertSee('Restoran Padang Nusantara Group');
        $response->assertSee('Cabai Rawit &amp; Bawang', false);
        $response->assertSee('2.495,5');
        $response->assertSee('Rp 137.252.500');

        $response->assertSee('INV-202610-088');
        $response->assertSee('PT Segar Makmur Ritelindo');
        $response->assertSee('Bawang Merah Super');
        $response->assertSee('2.988,2');
        $response->assertSee('Rp 104.587.000');
        $response->assertSee('Tempo TOP 30');

        $response->assertSee('INV-202610-085');
        $response->assertSee('Catering Melati Ibu Hj. Endang');
        $response->assertSee('348,9');
        $response->assertSee('Rp 13.956.000');
        $response->assertSee('Pending Verif');
    }

    public function test_journal_renders_trifecta_audit_files_per_transaction(): void
    {
        $response = $this->get(route('secretary.reports'));

        $response->assertOk();
        $response->assertSee('PO Reg');
        $response->assertSee('Surat Jalan');
        $response->assertSee('Tiket Timbangan');
        $response->assertSee('Menunggu');
    }

    public function test_console_renders_subtotal_locked_to_actual_net_weight(): void
    {
        $response = $this->get(route('secretary.reports'));

        $response->assertOk();
        $response->assertSee('Total Sub-Halaman (6 Transaksi Terpilih):');
        $response->assertSee('37.900');
        $response->assertSee('37.813,1');
        $response->assertSee('Rp 675.467.700');
        $response->assertSee('Terkunci Buku Besar');
    }

    public function test_console_renders_weighing_deviation_analysis(): void
    {
        $response = $this->get(route('secretary.reports'));

        $response->assertOk();
        $response->assertSee('Analisis Deviasi Kumulatif Timbangan');
        $response->assertSee('Tera Metrologi Sah');
        $response->assertSee('PO Estimasi Awal');
        $response->assertSee('483.90 Ton');
        $response->assertSee('Kalkulasi Kontrak B2B');
        $response->assertSee('Netto Sah Timbangan');
        $response->assertSee('482.65 Ton');
        $response->assertSee('Stasiun Timbang Pusat');
        $response->assertSee('Deviasi Bersih Susut');
        $response->assertSee('-0.26%');
        $response->assertSee('1.250 kg susut wajar');

        $response->assertSee('phantom weight');
        $response->assertSee('MET-7741-KBL-2024');
        $response->assertSee('Metrological Protocol: ISO/IEC 17025 Accredited');
        $response->assertSee('Status: Compliant &amp; Secured', false);
    }

    public function test_console_renders_ledger_validation_chain(): void
    {
        $response = $this->get(route('secretary.reports'));

        $response->assertOk();
        $response->assertSee('Rantai Validasi Buku Besar');
        $response->assertSee('4/4 Verified');
        $response->assertSee('Surat Jalan fisik diteken driver &amp; staf dock', false);
        $response->assertSee('Valid (OCR OK)');
        $response->assertSee('Foto tiket timbangan staging hub terarsip');
        $response->assertSee('64/64 Synced');
        $response->assertSee('Sinkronisasi e-Faktur PPN 11% Ditjen Pajak');
        $response->assertSee('Server Connected');
        $response->assertSee('Pencocokan rekening koran bank / mutasi giro');
        $response->assertSee('Reconciled');
        $response->assertSee('Sistem Validasi Otomatis: Aktif');
        $response->assertSee('Integrity Score: 100%');
    }

    public function test_console_renders_integrity_hash_and_distribution_actions(): void
    {
        $response = $this->get(route('secretary.reports'));

        $response->assertOk();
        $response->assertSee('Stempel Hash Integritas SHA-256 (Immutable Ledger):');
        $response->assertSee('7f8a9e4d01b92a3c8e54c03b');
        $response->assertSee('Laporan ini berstatus Dokumen Sah Korporat PT Guna Panen Agro, mengikat akuntansi fiskal dan operasional armada.');

        $response->assertSee('Unduh Rekapitulasi Lengkap Excel (.xlsx)');
        $response->assertSee('Cetak Laporan Resmi (.pdf)');
        $response->assertSee('Kirim Rekapitulasi ke Konsol Direktur Utama');
    }

    public function test_report_data_reconciles_journal_totals_kpis_and_deviation_analysis(): void
    {
        $journal = SecretaryReportData::journal();
        $rows = $journal['rows'];
        $totals = SecretaryReportData::totals();
        $metrics = collect(SecretaryReportData::metrics())->keyBy('key');

        $this->assertCount(6, $rows);
        $this->assertSame(37900.0, $totals['estimate']);
        $this->assertSame(37813.1, $totals['net']);
        $this->assertSame(675467700.0, $totals['value']);
        $this->assertSame(-0.23, $totals['deviation']);
        $this->assertSame($totals, $journal['totals']);

        $this->assertSame(6, $totals['count']);
        $this->assertGreaterThan(count($rows), $journal['total_transactions']);

        $this->assertSame(
            (float) array_sum(array_column($rows, 'estimate')),
            $totals['estimate']
        );
        $this->assertSame(
            (float) array_sum(array_column($rows, 'value')),
            $totals['value']
        );

        $this->assertSame(count($rows), count(array_unique(array_column($rows, 'invoice'))));
        $this->assertSame(count($rows), count(array_unique(array_column($rows, 'sj'))));

        foreach ($rows as $row) {
            $this->assertSame(
                round($row['net'] * $row['unit_price'], 2),
                $row['value'],
                'Nilai faktur '.$row['invoice'].' wajib terikat net sah gudang.'
            );

            $this->assertLessThanOrEqual(
                $row['estimate'],
                $row['net'],
                'Net sah gudang tidak boleh melebihi estimasi PO.'
            );

            $this->assertSame(
                round((($row['net'] - $row['estimate']) / $row['estimate']) * 100, 2),
                $row['deviation_value']
            );

            $this->assertGreaterThanOrEqual(2, $row['audit_files']);
            $this->assertLessThanOrEqual(3, $row['audit_files']);
        }

        $this->assertSame('482.65', $metrics['volume']['value']);
        $this->assertSame('Rp 482.650.000', $metrics['invoice_value']['value']);
        $this->assertSame('64 Total Transaksi', $metrics['invoice_value']['foot_left']);
        $this->assertSame('0.22%', $metrics['shrinkage']['value']);
        $this->assertSame('Batas <0.50%', $metrics['shrinkage']['chip']['label']);

        $blocked = array_values(array_filter($rows, fn (array $row): bool => $row['blocked']));
        $this->assertCount(1, $blocked);
        $this->assertSame('INV-202610-092', $blocked[0]['invoice']);
        $this->assertSame('text-warning-caution', $blocked[0]['deviation_class']);

        $deviation = SecretaryReportData::deviation();
        $this->assertSame('482.65 Ton', $deviation['stats'][1]['value']);
        $this->assertSame('-0.26%', $deviation['stats'][2]['value']);
        $this->assertSame('text-success-deep', $deviation['stats'][1]['value_class']);
        $this->assertSame('text-warning-caution', $deviation['stats'][2]['value_class']);
    }

    public function test_report_data_exposes_four_tabs_filters_and_four_validation_checks(): void
    {
        $filters = SecretaryReportData::filters();

        $this->assertCount(4, $filters);
        $this->assertSame(
            ['period', 'client', 'commodity', 'payment'],
            array_column($filters, 'key')
        );

        foreach ($filters as $filter) {
            $this->assertContains($filter['value'], $filter['options']);
            $this->assertGreaterThanOrEqual(4, count($filter['options']));
        }

        $tabs = SecretaryReportData::tabs();
        $this->assertCount(4, $tabs);
        $this->assertSame('sales', $tabs[0]['key']);
        $this->assertSame(['sales', 'weighing', 'dispatch', 'aging'], array_column($tabs, 'key'));

        $validation = SecretaryReportData::validation();
        $this->assertCount(4, $validation['rows']);
        $this->assertSame('4/4 Verified', $validation['badge']);
        $this->assertSame('Integrity Score: 100%', $validation['foot_right']);

        $seal = SecretaryReportData::seal();
        $this->assertSame('7f8a9e4d01b92a3c8e54c03b', $seal['hash']);
        $this->assertCount(3, $seal['actions']);
        $this->assertSame('brand', $seal['actions'][2]['variant']);
    }

    public function test_console_mounts_alpine_component_with_filter_and_totals_flow(): void
    {
        $response = $this->get(route('secretary.reports'));

        $response->assertOk();
        $response->assertSee('secretaryReports(', false);
        $response->assertSee('setTab(', false);
        $response->assertSee('activeTab', false);
        $response->assertSee('applyFilters()', false);
        $response->assertSee('resetFilters()', false);
        $response->assertSee('visibleRows()', false);
        $response->assertSee('isVisible(', false);
        $response->assertSee('totalEstimate', false);
        $response->assertSee('totalNet', false);
        $response->assertSee('totalDeviation', false);
        $response->assertSee('totalValue', false);
        $response->assertSee('deviation(', false);
        $response->assertSee('deviationClass(', false);
        $response->assertSee('isBlocked(', false);
        $response->assertSee('kgInt(', false);
        $response->assertSee('kg(', false);
        $response->assertSee('rupiah(', false);
        $response->assertSee('openAudit(', false);
        $response->assertSee('act(', false);
        $response->assertSee('x-text', false);
    }

    public function test_navigation_marks_recap_report_module_as_active(): void
    {
        $response = $this->get(route('secretary.reports'));

        $response->assertOk();
        $response->assertSee('href="'.route('secretary.reports').'"', false);
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('Verifikasi Pembayaran');
        $response->assertSee('Rekap Laporan');
    }
}
