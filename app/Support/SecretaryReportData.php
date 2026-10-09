<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data konsol rekapitulasi laporan operasional, distribusi & penjualan (Sekretaris).
 *
 * Seluruh nilai di bawah adalah data simulasi yang mencerminkan design
 * "Laporan Rekapitulasi Operasional, Distribusi & Penjualan"
 * (Rule 04 net weight binding + Rule 05 toleransi deviasi + Rule 14 trifecta audit).
 */
class SecretaryReportData
{
    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => SecretaryDashboardData::operator($user),
            'heading' => self::heading(),
            'filters' => self::filters(),
            'metrics' => self::metrics(),
            'standards' => self::standards(),
            'tabs' => self::tabs(),
            'journal' => self::journal(),
            'deviation' => self::deviation(),
            'validation' => self::validation(),
            'seal' => self::seal(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function heading(): array
    {
        return [
            'eyebrow' => 'Sub-06 // Modul Rekapitulasi Laporan Operasional & Distribusi',
            'title_before' => 'Laporan Rekapitulasi Operasional,',
            'title_after' => 'Distribusi & Penjualan',
            'subtitle' => 'Kompilasi audit transaksi harian, mingguan, dan bulanan berbasis data penimbangan aktual dan Surat Jalan terverifikasi (PRD App-GPA.md Section 6.2, 14, 15, & 22).',
            'actions' => [
                ['key' => 'sync', 'label' => 'Sinkronisasi Audit (04:00 WIB)', 'icon' => 'refresh', 'variant' => 'ghost'],
                ['key' => 'export', 'label' => 'Ekspor Rekapitulasi (.csv / .xlsx)', 'icon' => 'download', 'variant' => 'ghost'],
                ['key' => 'print', 'label' => 'Cetak Laporan PDF Resmi Direktur', 'icon' => 'printer', 'variant' => 'brand'],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function filters(): array
    {
        return [
            [
                'key' => 'period',
                'label' => 'Periode Akuntansi',
                'value' => 'Bulan Berjalan (Oktober 2024)',
                'options' => [
                    'Bulan Berjalan (Oktober 2024)',
                    'Minggu Berjalan (21-27 Okt 2024)',
                    'Hari Berjalan (24 Okt 2024)',
                    'Kuartal III 2024 (Jul-Sep)',
                ],
            ],
            [
                'key' => 'client',
                'label' => 'Kategori Klien',
                'value' => 'Semua Klien B2B & Institusi',
                'options' => [
                    'Semua Klien B2B & Institusi',
                    'Pabrik & Distribusi (B2B)',
                    'Hotel & Hospitality',
                    'Resto & Katering',
                    'Ritel & Modern Trade',
                ],
            ],
            [
                'key' => 'commodity',
                'label' => 'Portofolio Komoditas',
                'value' => '5 Komoditas Inti Standar GPA',
                'options' => [
                    '5 Komoditas Inti Standar GPA',
                    'Tomat Beef Super',
                    'Selada & Wortel Premium',
                    'Bawang Merah Super',
                    'Cabai Rawit & Bawang',
                ],
            ],
            [
                'key' => 'payment',
                'label' => 'Status Pembayaran',
                'value' => 'Semua Status Tagihan',
                'options' => [
                    'Semua Status Tagihan',
                    'Lunas',
                    'Tempo TOP 14',
                    'Tempo TOP 30',
                    'Pending Verif',
                ],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function filterLabels(): array
    {
        return [
            'all' => 'Semua Status Tagihan',
            'lunas' => 'Lunas',
            'top14' => 'Tempo TOP 14',
            'top30' => 'Tempo TOP 30',
            'pending' => 'Pending Verif',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function metrics(): array
    {
        return [
            [
                'key' => 'volume',
                'label' => 'Volume Teralokasi & Terkirim',
                'icon' => 'package',
                'icon_tile' => 'bg-accent/60',
                'icon_class' => 'text-success-deep',
                'value' => '482.65',
                'unit' => 'Ton',
                'value_class' => 'text-ink',
                'detail' => '5 komoditas inti valid SJ/QC',
                'detail_class' => 'gpa-note text-ink-body',
                'foot_left' => 'Target Bulanan: 500 Ton',
                'foot_right' => '96.5% Tercapai',
                'foot_class' => 'text-success-deep',
                'chip' => [
                    'label' => '+14.2% MoM',
                    'class' => 'bg-accent text-success-deep outline outline-1 outline-success-deep',
                ],
            ],
            [
                'key' => 'invoice_value',
                'label' => 'Nilai Faktur Diterbitkan',
                'icon' => 'invoice',
                'icon_tile' => 'bg-brand',
                'icon_class' => 'text-accent',
                'value' => 'Rp 482.650.000',
                'unit' => null,
                'value_class' => 'text-brand',
                'detail' => 'Binding PRD Rule 04/05',
                'detail_class' => 'gpa-note text-ink-body',
                'foot_left' => '64 Total Transaksi',
                'foot_right' => 'Terkunci Buku Besar',
                'foot_class' => 'text-ink-quiet',
                'chip' => [
                    'label' => '100% Actual Net',
                    'class' => 'bg-brand text-accent outline outline-1 outline-accent-deep',
                ],
            ],
            [
                'key' => 'sla',
                'label' => 'Ketepatan Pengiriman (SLA)',
                'icon' => 'truck',
                'icon_tile' => 'bg-success-soft/50',
                'icon_class' => 'text-success-deep',
                'value' => '98.4%',
                'unit' => 'On-Time',
                'value_class' => 'text-success-deep',
                'detail' => 'Tiba di loading dock klien',
                'detail_class' => 'gpa-note text-ink-body',
                'foot_left' => 'Toleransi Disposisi: <05:00 WIB',
                'foot_right' => '0 Klaim Terlambat',
                'foot_class' => 'text-success-deep',
                'chip' => [
                    'label' => 'Avg 04:15 WIB',
                    'class' => 'bg-surface-track text-ink outline outline-1 outline-line-board',
                ],
            ],
            [
                'key' => 'shrinkage',
                'label' => 'Tingkat Susut & Retur',
                'icon' => 'scale',
                'icon_tile' => 'bg-warning-soft/50',
                'icon_class' => 'text-warning-caution',
                'value' => '0.22%',
                'unit' => 'Dev',
                'value_class' => 'text-warning-caution',
                'detail' => '35.5 kg dari 15.8 Ton batch',
                'detail_class' => 'gpa-note text-ink-body',
                'foot_left' => 'Status Susut: Sangat Aman',
                'foot_right' => 'Zero Incident',
                'foot_class' => 'text-success-deep',
                'chip' => [
                    'label' => 'Batas <0.50%',
                    'class' => 'bg-warning-soft text-warning-deep outline outline-1 outline-warning/30',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function standards(): array
    {
        return [
            'eyebrow' => 'Standar Integritas',
            'title' => 'Kepatuhan Penagihan Buku Besar',
            'body' => 'Protokol wajib untuk menjamin validitas hukum, kepatuhan audit metrologi, dan transparansi antara supir, gudang, dan klien B2B.',
            'footer' => 'App-GPA Engine Verif // Hash SHA-256 Enforced',
            'rules' => [
                [
                    'chip' => 'PRD Rule 04',
                    'tag' => 'Mutlak Timbangan Sah',
                    'title' => 'Net Weight Binding',
                    'body' => 'Nilai tagihan dan faktur wajib dihitung mutlak dari Netto Timbangan Stasiun Resmi PT GPA. Dilarang menagih berbasis estimasi PO.',
                    'note' => '64 transaksi terkunci timbangan sah',
                ],
                [
                    'chip' => 'PRD Rule 05',
                    'tag' => 'Toleransi <= 0.5%',
                    'title' => 'Deviasi Bobot Loading Dock',
                    'body' => 'Selisih timbang loading vs timbang terima klien diaudit otomatis secara real-time. Jika deviasi > 0.5%, faktur diblokir otomatis untuk mediasi.',
                    'note' => '-0.19% rata-rata deviasi bulan ini (Lolos)',
                ],
                [
                    'chip' => 'PRD Rule 14',
                    'tag' => 'Trifecta Audit',
                    'title' => 'Dokumen Rantai 3-Arah',
                    'body' => 'Faktur tidak dapat diterbitkan tanpa validasi kesesuaian Nomor Registrasi PO, Berkas Surat Jalan bertanda tangan supir & penerima, serta Tiket Timbangan tersertifikasi.',
                    'note' => '3 berkas sinkron 100% per invoice',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function tabs(): array
    {
        return [
            ['key' => 'sales', 'label' => 'Tab 1: Rekapitulasi Penjualan & Pesanan (Aktif)', 'icon' => 'invoice'],
            ['key' => 'weighing', 'label' => 'Tab 2: Volume & Analisis Timbangan', 'icon' => 'scale'],
            ['key' => 'dispatch', 'label' => 'Tab 3: Surat Jalan & Disposisi Armada', 'icon' => 'truck'],
            ['key' => 'aging', 'label' => 'Tab 4: Rekapitulasi Piutang & Umur Faktur (Aging AR)', 'icon' => 'chart'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function journal(): array
    {
        return [
            'title' => 'Jurnal Buku Transaksi',
            'meta_left' => 'Menampilkan 6 dari 64 Transaksi',
            'meta_right' => 'Terverifikasi Tera Metrologi #MET-2024-88',
            'total_transactions' => 64,
            'columns' => [
                '#',
                'No Faktur / Ref SJ',
                'Klien B2B & Lokasi',
                'Komoditas Utama',
                'Estimasi PO',
                'Net Sah Gudang',
                'Deviasi',
                'Nilai Final (Rp)',
                'Status Bayar',
                'Berkas Audit',
            ],
            'rows' => self::journalRows(),
            'totals' => self::totals(),
            'total_label' => 'Total Sub-Halaman',
            'total_lock' => 'Terkunci Buku Besar',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function journalRows(): array
    {
        return array_map(
            static fn (array $row): array => self::hydrateJournalRow($row),
            self::journalSourceRows(),
        );
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private static function hydrateJournalRow(array $row): array
    {
        $value = round($row['net'] * $row['unit_price'], 2);
        $deviation = $row['estimate'] > 0
            ? round((($row['net'] - $row['estimate']) / $row['estimate']) * 100, 2)
            : 0.0;

        $row['value'] = $value;
        $row['value_label'] = 'Rp '.number_format($value, 0, ',', '.');
        $row['estimate_label'] = number_format($row['estimate'], 0, ',', '.').' kg';
        $row['net_label'] = number_format($row['net'], 1, ',', '.').' kg';
        $row['deviation_value'] = $deviation;
        $row['deviation_label'] = number_format($deviation, 2, '.', '');
        $row['deviation_class'] = $deviation <= -0.5
            ? 'text-warning-caution'
            : 'text-success-deep';
        $row['blocked'] = $deviation <= -0.5;

        return $row;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function journalSourceRows(): array
    {
        return [
            [
                'no' => '01',
                'invoice' => 'INV-202610-094',
                'sj' => 'SJ-GPA-88412',
                'client' => 'PT Sinar Agro Mandiri',
                'location' => 'Pabrik Marunda, Jakut',
                'commodity' => 'Tomat Beef Super',
                'estimate' => 30000.0,
                'net' => 29940.0,
                'unit_price' => 12000,
                'status' => 'lunas',
                'status_label' => 'Lunas',
                'status_class' => 'bg-accent text-success-deep outline outline-1 outline-success-deep',
                'audit_files' => 3,
            ],
            [
                'no' => '02',
                'invoice' => 'INV-202610-092',
                'sj' => 'SJ-GPA-88390',
                'client' => 'PT Aerofood ACS',
                'location' => 'Flight Catering Soetta',
                'commodity' => 'Selada Romaine Grade A',
                'estimate' => 1200.0,
                'net' => 1192.4,
                'unit_price' => 35000,
                'status' => 'lunas',
                'status_label' => 'Lunas',
                'status_class' => 'bg-accent text-success-deep outline outline-1 outline-success-deep',
                'audit_files' => 3,
            ],
            [
                'no' => '03',
                'invoice' => 'INV-202610-091',
                'sj' => 'SJ-GPA-88350',
                'client' => 'Hotel Santika Premiere Jakarta',
                'location' => 'Slipi, Jakarta Barat',
                'commodity' => 'Brokoli & Wortel',
                'estimate' => 850.0,
                'net' => 848.1,
                'unit_price' => 22000,
                'status' => 'top14',
                'status_label' => 'Tempo TOP 14',
                'status_class' => 'bg-warning-soft text-warning-deep outline outline-1 outline-warning/30',
                'audit_files' => 3,
            ],
            [
                'no' => '04',
                'invoice' => 'INV-202610-089',
                'sj' => 'SJ-GPA-88310',
                'client' => 'Restoran Padang Nusantara Group',
                'location' => 'Central Kitchen Kramat Jati',
                'commodity' => 'Cabai Rawit & Bawang',
                'estimate' => 2500.0,
                'net' => 2495.5,
                'unit_price' => 55000,
                'status' => 'lunas',
                'status_label' => 'Lunas',
                'status_class' => 'bg-accent text-success-deep outline outline-1 outline-success-deep',
                'audit_files' => 3,
            ],
            [
                'no' => '05',
                'invoice' => 'INV-202610-088',
                'sj' => 'SJ-GPA-88280',
                'client' => 'PT Segar Makmur Ritelindo',
                'location' => 'DC Superindo Cikarang',
                'commodity' => 'Bawang Merah Super',
                'estimate' => 3000.0,
                'net' => 2988.2,
                'unit_price' => 35000,
                'status' => 'top30',
                'status_label' => 'Tempo TOP 30',
                'status_class' => 'bg-warning-soft text-warning-deep outline outline-1 outline-warning/30',
                'audit_files' => 3,
            ],
            [
                'no' => '06',
                'invoice' => 'INV-202610-085',
                'sj' => 'SJ-GPA-88240',
                'client' => 'Catering Melati Ibu Hj. Endang',
                'location' => 'Pondok Gede, Bekasi',
                'commodity' => 'Selada & Tomat Beef',
                'estimate' => 350.0,
                'net' => 348.9,
                'unit_price' => 40000,
                'status' => 'pending',
                'status_label' => 'Pending Verif',
                'status_class' => 'bg-surface-pill text-ink-body outline outline-1 outline-line-board',
                'audit_files' => 2,
            ],
        ];
    }

    /**
     * @return array<string, float|int>
     */
    public static function totals(): array
    {
        $rows = self::journalRows();

        $estimate = (float) array_sum(array_column($rows, 'estimate'));
        $net = round((float) array_sum(array_column($rows, 'net')), 1);
        $value = (float) array_sum(array_column($rows, 'value'));
        $deviation = $estimate > 0 ? round((($net - $estimate) / $estimate) * 100, 2) : 0.0;

        return [
            'count' => count($rows),
            'estimate' => $estimate,
            'net' => $net,
            'value' => $value,
            'deviation' => $deviation,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function deviation(): array
    {
        return [
            'title' => 'Analisis Deviasi Kumulatif Timbangan',
            'badge' => 'Tera Metrologi Sah',
            'stats' => [
                [
                    'label' => 'PO Estimasi Awal',
                    'value' => '483.90 Ton',
                    'value_class' => 'text-ink',
                    'note' => 'Kalkulasi Kontrak B2B',
                ],
                [
                    'label' => 'Netto Sah Timbangan',
                    'value' => '482.65 Ton',
                    'value_class' => 'text-success-deep',
                    'note' => 'Stasiun Timbang Pusat',
                ],
                [
                    'label' => 'Deviasi Bersih Susut',
                    'value' => '-0.26%',
                    'value_class' => 'text-warning-caution',
                    'note' => '1.250 kg susut wajar',
                ],
            ],
            'body' => [
                ['type' => 'text', 'value' => 'Kompilasi audit membuktikan beban penyusutan distribusi antar pulau dan transit cold chain terkendali di bawah ambang batas toleransi 0.50%. Bebas dari '],
                ['type' => 'mono', 'value' => 'phantom weight'],
                ['type' => 'text', 'value' => ' maupun ketidaksesuaian kalibrasi load cell. Sertifikat kalibrasi Direktorat Metrologi No: '],
                ['type' => 'mono', 'value' => 'MET-7741-KBL-2024'],
                ['type' => 'text', 'value' => ' terlampir aktif.'],
            ],
            'foot_left' => 'Metrological Protocol: ISO/IEC 17025 Accredited',
            'foot_right' => 'Status: Compliant & Secured',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function validation(): array
    {
        return [
            'title' => 'Rantai Validasi Buku Besar',
            'badge' => '4/4 Verified',
            'rows' => [
                ['label' => 'Surat Jalan fisik diteken driver & staf dock', 'status' => 'Valid (OCR OK)'],
                ['label' => 'Foto tiket timbangan staging hub terarsip', 'status' => '64/64 Synced'],
                ['label' => 'Sinkronisasi e-Faktur PPN 11% Ditjen Pajak', 'status' => 'Server Connected'],
                ['label' => 'Pencocokan rekening koran bank / mutasi giro', 'status' => 'Reconciled'],
            ],
            'foot_left' => 'Sistem Validasi Otomatis: Aktif',
            'foot_right' => 'Integrity Score: 100%',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function seal(): array
    {
        return [
            'label' => 'Stempel Hash Integritas SHA-256 (Immutable Ledger):',
            'hash' => '7f8a9e4d01b92a3c8e54c03b',
            'body' => 'Laporan ini berstatus Dokumen Sah Korporat PT Guna Panen Agro, mengikat akuntansi fiskal dan operasional armada.',
            'actions' => [
                ['key' => 'download', 'label' => 'Unduh Rekapitulasi Lengkap Excel (.xlsx)', 'icon' => 'download', 'variant' => 'ghost'],
                ['key' => 'pdf', 'label' => 'Cetak Laporan Resmi (.pdf)', 'icon' => 'printer', 'variant' => 'ghost'],
                ['key' => 'send', 'label' => 'Kirim Rekapitulasi ke Konsol Direktur Utama', 'icon' => 'send', 'variant' => 'brand'],
            ],
        ];
    }
}
