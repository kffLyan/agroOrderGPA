<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data konsol manajemen multi-source inventory & available-to-promise.
 *
 * Seluruh angka di bawah adalah data simulasi yang mencerminkan design
 * "Manajemen Multi-Source Inventory & Available-to-Promise (ATP)".
 * Nanti dapat dipindahkan ke query Eloquent / service layer tanpa mengubah
 * struktur array pada view.
 */
class SecretaryInventoryData
{
    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => SecretaryDashboardData::operator($user),
            'heading' => self::heading(),
            'actions' => self::actions(),
            'ruleEngine' => self::ruleEngine(),
            'metrics' => self::metrics(),
            'filters' => self::filters(),
            'ledger' => self::ledger(),
            'activity' => self::activity(),
            'readiness' => self::readiness(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function heading(): array
    {
        return [
            'title' => 'Manajemen Multi-Source Inventory & Available- to-Promise (ATP)',
            'subtitle' => 'Sinkronisasi pasokan panen kelompok tani binaan dan cadangan buffer stock eksternal untuk pemenuhan PO Klien B2B. Anti Zero-Overselling Guard Aktif',
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function actions(): array
    {
        return [
            [
                'label' => 'Sinkronisasi Data Panen Koordinator',
                'tone' => 'neutral',
                'message' => 'Data panen Koordinator Lapangan Cibitung & Lembang disegarkan ke neraca pasokan.',
            ],
            [
                'label' => 'Ekspor Neraca Stok CSV',
                'tone' => 'neutral',
                'message' => 'Neraca stok 5 komoditas inti disiapkan untuk diunduh.',
            ],
            [
                'label' => '+ Alokasi Buffer Manual',
                'tone' => 'primary',
                'message' => 'Formulir alokasi buffer mitra dibuka untuk penyesuaian emergency.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function ruleEngine(): array
    {
        return [
            'title' => 'GPA Logistics Rule Engine // Automation Safeguards',
            'chip' => 'Status: 100% Operational',
            'rules' => [
                [
                    'icon_tile' => 'bg-brand',
                    'icon' => 'lock',
                    'icon_class' => 'text-accent',
                    'title' => 'Rule 02: Hard Lock Kuota PO Terkonfirmasi',
                    'badge' => ['label' => 'Strict', 'class' => 'bg-ink text-accent'],
                    'body' => 'Sistem membekukan kuota saat PO disetujui sekretaris secara instan. Menjamin garansi zero stock-out dan mengisolasi komoditas dari kanal pesanan lain.',
                ],
                [
                    'icon_tile' => 'bg-success-deep',
                    'icon' => 'refresh',
                    'icon_class' => 'text-white',
                    'title' => 'Rule 03: Auto-Switching ke Buffer Mitra Luar',
                    'badge' => ['label' => 'Auto <20%', 'class' => 'bg-accent text-success-ink'],
                    'body' => 'Jika kuota binaan tersisa di bawah 20% demand PO terbuka, otomatis aktifkan dan gabungkan kuota buffer mitra logistik terakreditasi GPA.',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function metrics(): array
    {
        return [
            [
                'key' => 'supply',
                'label' => 'Total Pasokan Tersedia',
                'icon' => 'gauge',
                'icon_class' => 'text-success-deep',
                'label_class' => 'text-ink-body',
                'value' => '3.360',
                'unit' => 'kg',
                'unit_class' => 'text-ink-quiet',
                'foot' => [
                    ['label' => 'Gudang:', 'value' => '1.480 kg', 'value_class' => 'text-ink'],
                    ['label' => 'Kebun:', 'value' => '1.880 kg', 'value_class' => 'text-ink'],
                ],
            ],
            [
                'key' => 'composition',
                'label' => 'Komposisi Pasokan',
                'icon' => 'gauge',
                'icon_class' => 'text-success-deep',
                'label_class' => 'text-ink-body',
                'value' => '78%',
                'unit' => 'Binaan',
                'unit_class' => 'text-ink-body',
                'value_second' => '22%',
                'unit_second' => 'Buffer',
                'foot' => [
                    ['label' => 'Binaan:', 'value' => '2.620 kg', 'value_class' => 'text-ink'],
                    ['label' => 'Buffer:', 'value' => '740 kg', 'value_class' => 'text-ink'],
                ],
            ],
            [
                'key' => 'locked',
                'label' => 'Alokasi Terkunci PO',
                'icon' => 'shield',
                'icon_class' => 'text-danger',
                'label_class' => 'text-ink-body',
                'value' => '2.450',
                'unit' => 'kg',
                'unit_class' => 'text-ink-quiet',
                'foot_label' => '18 Pesanan Aktif Terikat',
                'foot_chip' => ['label' => '72.9% Kapasitas', 'class' => 'bg-surface-pill text-ink'],
            ],
            [
                'key' => 'atp',
                'label' => 'Sisa Kuota Bebas (ATP)',
                'icon' => 'gauge',
                'icon_class' => 'text-success-deep',
                'label_class' => 'text-success-deep',
                'value' => '910',
                'unit' => 'kg',
                'unit_class' => 'text-ink',
                'value_class' => 'text-success-deep',
                'badge' => ['label' => 'Ready to Promise', 'class' => 'bg-accent text-success-ink outline outline-success-deep/40'],
                'foot' => [
                    ['label' => 'Siap Dialokasikan ke PO Baru', 'class' => 'text-ink-body'],
                    ['label' => 'Non-Reserved', 'class' => 'text-success-deep'],
                ],
                'glow' => true,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function filters(): array
    {
        return [
            [
                'key' => 'commodity',
                'label' => 'Komoditas Inti',
                'icon' => 'package',
                'value' => 'Semua Komoditas (5 Standar)',
                'options' => [
                    'Semua Komoditas (5 Standar)',
                    'Selada Romaine',
                    'Tomat Beef Super',
                    'Brokoli Highland',
                    'Stroberi Ciwidey',
                    'Kol Putih Organik',
                ],
            ],
            [
                'key' => 'source',
                'label' => 'Sumber Pasokan',
                'icon' => 'truck',
                'value' => 'Semua Sumber (Multi-Source)',
                'options' => [
                    'Semua Sumber (Multi-Source)',
                    'Kebun Binaan',
                    'Gudang Transit Cibitung',
                    'Mitra Agro Lembang',
                    'Mitra Ciwidey Fresh',
                    'Koperasi Petani Patuha',
                    'Mitra Agribisnis Parongpong',
                ],
            ],
            [
                'key' => 'status',
                'label' => 'Status Kuota & ATP',
                'icon' => 'filter',
                'value' => 'Semua Status Operasional',
                'options' => [
                    'Semua Status Operasional',
                    'Kuota Aman',
                    'Buffer Aktif',
                    'Defisit Ringan',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function ledger(): array
    {
        return [
            'title' => 'Neraca Pasokan Multi-Sumber Per Komoditas Inti',
            'chip' => '5 Komoditas Standar GPA',
            'mutasi' => 'Mutasi Terakhir: Hari Ini 11:24 WIB',
            'atp_total' => '910 kg',
            'critical_threshold' => 20,
            'columns' => [
                'sku' => 'SKU / Komoditas',
                'binawan' => 'Sumber Petani Binaan',
                'buffer' => 'Sumber Buffer Luar',
                'total' => 'Total Pasokan',
                'locked' => 'Terkunci PO B2B (Rule 02)',
                'atp' => 'Sisa Kuota Bebas (ATP)',
                'actions' => 'Status & Aksi Admin',
            ],
            'rows' => self::rows(),
            'footer_left' => 'Menampilkan 5 dari 5 komoditas operasional aktif',
            'footer_atp' => 'Total Kuota Bebas (ATP): 910 kg',
            'footer_note' => 'Pagination: Locked to Single Operational Ledger',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function rows(): array
    {
        return [
            [
                'name' => 'Selada Romaine',
                'sku' => 'SKU: VEG-ROM-002',
                'grade' => 'Grade: Super A / Cold Hydro',
                'binawan_value' => '720 kg',
                'binawan_share' => '81.8% dari total',
                'binawan_percent' => 78.52,
                'buffer_value' => '160 kg',
                'buffer_partner' => 'Mitra Agro Lembang',
                'buffer_class' => 'text-ink',
                'total_value' => '880 kg',
                'locked_value' => '640 kg',
                'locked_note' => 'Hard Lock 4 PO',
                'atp_value' => '240 kg',
                'atp_note' => 'Available to Promise',
                'atp_class' => 'text-success-deep',
                'status' => ['label' => 'Kuota Aman', 'class' => 'bg-accent text-success-ink outline outline-success-deep'],
                'action' => ['label' => '+ Buffer', 'class' => 'bg-ink text-accent', 'intent' => 'buffer'],
                'keywords' => ['Selada Romaine', 'VEG-ROM-002', 'Mitra Agro Lembang', 'Petani Binaan Blok C-02'],
                'sources' => ['Kebun Binaan', 'Gudang Transit Cibitung', 'Mitra Agro Lembang'],
            ],
            [
                'name' => 'Tomat Beef Super',
                'sku' => 'SKU: TOM-BEEF-014',
                'grade' => 'Grade: Premium Export',
                'binawan_value' => '540 kg',
                'binawan_share' => '69.2% dari total',
                'binawan_percent' => 66.42,
                'buffer_value' => '240 kg',
                'buffer_partner' => 'Mitra Agro Lembang',
                'buffer_class' => 'text-warning',
                'buffer_badge' => ['label' => 'Aktif Injeksi 35%', 'class' => 'bg-warning-soft text-warning-ink'],
                'total_value' => '780 kg',
                'locked_value' => '690 kg',
                'locked_note' => 'Hard Lock 6 PO',
                'atp_value' => '90 kg',
                'atp_note' => 'Kritis (<<15%)',
                'atp_class' => 'text-success-deep',
                'status' => ['label' => 'Buffer Aktif', 'class' => 'bg-warning-soft text-warning-ink outline outline-warning/30'],
                'action' => ['label' => 'Adjust', 'class' => 'bg-warning-deep text-white', 'intent' => 'adjust'],
                'keywords' => ['Tomat Beef Super', 'TOM-BEEF-014', 'Mitra Agro Lembang', 'Mitra Lembang'],
                'sources' => ['Kebun Binaan', 'Mitra Agro Lembang'],
            ],
            [
                'name' => 'Brokoli Highland',
                'sku' => 'SKU: BRK-SUP-007',
                'grade' => 'Grade: Curled Head 15cm',
                'binawan_value' => '610 kg',
                'binawan_share' => '83.5% dari total',
                'binawan_percent' => 80.16,
                'buffer_value' => '120 kg',
                'buffer_partner' => 'Mitra Ciwidey Fresh',
                'buffer_class' => 'text-ink',
                'total_value' => '730 kg',
                'locked_value' => '480 kg',
                'locked_note' => 'Hard Lock 3 PO',
                'atp_value' => '250 kg',
                'atp_note' => 'Available to Promise',
                'atp_class' => 'text-success-deep',
                'status' => ['label' => 'Kuota Aman', 'class' => 'bg-accent text-success-ink outline outline-success-deep'],
                'action' => ['label' => '+ Buffer', 'class' => 'bg-ink text-accent', 'intent' => 'buffer'],
                'keywords' => ['Brokoli Highland', 'BRK-SUP-007', 'Mitra Ciwidey Fresh', 'Kelompok Tani Harapan Jaya'],
                'sources' => ['Kebun Binaan', 'Mitra Ciwidey Fresh'],
            ],
            [
                'name' => 'Stroberi Ciwidey',
                'sku' => 'SKU: STR-CIW-004',
                'grade' => 'Grade: Grade A Sweet',
                'binawan_value' => '310 kg',
                'binawan_share' => '73.8% dari total',
                'binawan_percent' => 70.84,
                'buffer_value' => '110 kg',
                'buffer_partner' => 'Koperasi Petani Patuha',
                'buffer_class' => 'text-ink',
                'total_value' => '420 kg',
                'locked_value' => '360 kg',
                'locked_note' => 'Hard Lock 3 PO',
                'atp_value' => '60 kg',
                'atp_note' => 'Buffer Hampir Habis',
                'atp_class' => 'text-danger',
                'status' => ['label' => 'Defisit Ringan', 'class' => 'bg-danger-soft text-danger-ink outline outline-danger/30'],
                'action' => ['label' => 'Req Tambahan', 'class' => 'bg-danger text-white', 'intent' => 'request'],
                'keywords' => ['Stroberi Ciwidey', 'STR-CIW-004', 'Koperasi Petani Patuha'],
                'sources' => ['Kebun Binaan', 'Koperasi Petani Patuha'],
            ],
            [
                'name' => 'Kol Putih Organik',
                'sku' => 'SKU: KOL-PUT-003',
                'grade' => 'Grade: Organik Sertifikasi',
                'binawan_value' => '440 kg',
                'binawan_share' => '80.0% dari total',
                'binawan_percent' => 76.8,
                'buffer_value' => '110 kg',
                'buffer_partner' => 'Mitra Agribisnis Parongpong',
                'buffer_class' => 'text-ink',
                'total_value' => '550 kg',
                'locked_value' => '280 kg',
                'locked_note' => 'Hard Lock 2 PO',
                'atp_value' => '270 kg',
                'atp_note' => 'Available to Promise',
                'atp_class' => 'text-success-deep',
                'status' => ['label' => 'Kuota Aman', 'class' => 'bg-accent text-success-ink outline outline-success-deep'],
                'action' => ['label' => '+ Buffer', 'class' => 'bg-ink text-accent', 'intent' => 'buffer'],
                'keywords' => ['Kol Putih Organik', 'KOL-PUT-003', 'Mitra Agribisnis Parongpong'],
                'sources' => ['Kebun Binaan', 'Mitra Agribisnis Parongpong'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function activity(): array
    {
        return [
            'title' => 'Log Mutasi & Eksekusi Penguncian Stok Realtime',
            'chip' => 'Stream: Live Feed',
            'events' => [
                [
                    'icon_tile' => 'bg-danger-soft',
                    'icon' => 'lock',
                    'icon_class' => 'text-danger-ink',
                    'title' => 'Hard Lock: 180 kg Selada Romaine',
                    'ref' => 'PO-B2B-8821',
                    'body' => 'Klien: PT Segar Retail Sentosa — Dialokasikan dari Kebun Binaan Blok C-02.',
                    'time' => '11:18 WIB',
                    'time_class' => 'text-success-deep',
                    'tag' => 'Rule 02 Exec',
                ],
                [
                    'icon_tile' => 'bg-accent',
                    'icon' => 'refresh',
                    'icon_class' => 'text-success-ink',
                    'title' => 'Auto-Switch: Injeksi Buffer 85 kg Tomat Beef',
                    'ref' => 'PO-B2B-8819',
                    'body' => 'Binaan drop di bawah 20% limit. Otomatis aktivasi buffer Mitra Agro Lembang.',
                    'time' => '10:52 WIB',
                    'time_class' => 'text-success-deep',
                    'tag' => 'Rule 03 Exec',
                ],
                [
                    'icon_tile' => 'bg-surface-pill',
                    'icon' => 'package',
                    'icon_class' => 'text-ink',
                    'title' => 'Sync Panen: Penerimaan Kebun 320 kg Brokoli',
                    'ref' => 'Batch-PK-092',
                    'body' => 'Kelompok Tani Harapan Jaya (Ciwidey) — Lolos QC Timbang Hub grade Super.',
                    'time' => '10:15 WIB',
                    'time_class' => 'text-success-deep',
                    'tag' => 'QC Passed',
                ],
            ],
            'footer_left' => 'Menampilkan 3 event mutasi sistem terverifikasi',
            'footer_right' => 'Lihat Audit Trail Lengkap',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function readiness(): array
    {
        return [
            'title' => 'Kesiapan DO Batch & Cold-Chain',
            'chip' => '18/18 PO Siap',
            'checks' => [
                [
                    'icon' => 'scale',
                    'title' => 'Validasi Timbangan Hub Lembang',
                    'note' => 'Kalibrasi Digital: Akurasi ±0.05 kg',
                    'badge' => ['label' => 'Verified', 'class' => 'bg-accent text-success-ink'],
                ],
                [
                    'icon' => 'thermometer',
                    'title' => 'Suhu Cold-Chain Chiller (2°C - 6°C)',
                    'note' => 'Sensor Realtime: Saat ini 3.8°C (Stabil)',
                    'badge' => ['label' => 'Optimal', 'class' => 'bg-accent text-success-ink'],
                ],
                [
                    'icon' => 'truck',
                    'title' => 'Otorisasi Surat Jalan Sekretariat',
                    'note' => '18 Dokumen siap cetak dan rilis armada',
                    'badge' => ['label' => 'Siap Cetak', 'class' => 'bg-accent text-success-ink'],
                ],
            ],
            'cta' => 'Lanjut ke Cetak Surat Jalan Massal',
        ];
    }
}
