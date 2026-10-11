<?php

namespace App\Support;

/**
 * Sumber data halaman "Daftar Pesanan Saya (Orders Management)".
 *
 * Angka di bawah adalah data simulasi yang mencerminkan design "Orders
 * Management". Lima baris pesanan ditampilkan sebagai pratinjau utuh; angka
 * agregat (52 pesanan aktif, 11 halaman) tetap mengikuti design sehingga
 * filter, pencarian, dan paginasi dapat diuji di browser.
 */
class ClientOrdersData
{
    /**
     * @return array<string, mixed>
     */
    public static function header(): array
    {
        return [
            'title' => 'Daftar Pesanan Saya (Orders Management)',
            'chip' => 'LIVE COCKPIT',
            'subtitle' => 'Manajemen siklus pemesanan agribisnis: estimasi kuantitas, penimbangan aktual netto, surat jalan, dan bukti PoD.',
            'export' => 'EXPORT XLS REKAP PESANAN',
            'create' => '+ BUAT PESANAN BARU',
            'timezone' => 'Zona Waktu: WIB (UTC+7)',
        ];
    }

    /**
     * Tab status beserta agregat jumlah pesanan pada masing-masing tahap.
     *
     * @return list<array<string, mixed>>
     */
    public static function statusTabs(): array
    {
        return [
            ['value' => 'all', 'label' => 'SEMUA PESANAN', 'count' => 52, 'chip_tone' => 'brand'],
            ['value' => 'verifikasi', 'label' => 'MENUNGGU VERIFIKASI', 'count' => 3, 'chip_tone' => 'quiet'],
            ['value' => 'timbang', 'label' => 'PANEN & TIMBANG AKTUAL', 'count' => 4, 'chip_tone' => 'quiet'],
            ['value' => 'dispatch', 'label' => 'SIAP KIRIM & DISPATCH', 'count' => 2, 'chip_tone' => 'quiet'],
            ['value' => 'pengiriman', 'label' => 'DALAM PENGIRIMAN', 'count' => 2, 'chip_tone' => 'accent'],
            ['value' => 'selesai', 'label' => 'SELESAI', 'count' => 41, 'chip_tone' => 'quiet'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function filters(): array
    {
        return [
            'range' => [
                'label' => 'Rentang Tanggal',
                'value' => '30 Hari Terakhir',
                'options' => [
                    ['value' => '7', 'label' => '7 Hari Terakhir'],
                    ['value' => '30', 'label' => '30 Hari Terakhir'],
                    ['value' => '90', 'label' => '90 Hari Terakhir'],
                    ['value' => 'quarter', 'label' => 'Quarter Berjalan'],
                ],
            ],
            'hub' => [
                'label' => 'Gudang Penerima',
                'value' => 'Semua Gudang Hub',
                'options' => [
                    ['value' => 'all', 'label' => 'Semua Gudang Hub'],
                    ['value' => 'ciracas', 'label' => 'Dock #01 Central Kitchen Ciracas'],
                    ['value' => 'kelapa_gading', 'label' => 'Dock #02 Satellite Commissary Hub'],
                    ['value' => 'ciawi', 'label' => 'Hub Ciawi Staging Area'],
                    ['value' => 'pangalengan', 'label' => 'Hub Pangalengan Dermaga'],
                ],
            ],
            'payment' => [
                'label' => 'Metode Bayar',
                'value' => 'Term of Payment (TOP 30)',
                'options' => [
                    ['value' => 'top30', 'label' => 'Term of Payment (TOP 30)'],
                    ['value' => 'transfer', 'label' => 'Transfer Bank Manual'],
                    ['value' => 'qris', 'label' => 'QRIS Dinamis GPA'],
                    ['value' => 'cod', 'label' => 'COD di Dock Penerima'],
                ],
            ],
            'deviation_label' => 'Tampilkan pesanan dg selisih timbangan > 1%',
            'deviation_threshold' => 1.0,
            'search' => 'Cari No. PO / No. Surat Jalan...',
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    public static function columns(): array
    {
        return [
            ['key' => 'order', 'label' => 'NO. PESANAN & TANGGAL', 'align' => 'left'],
            ['key' => 'commodities', 'label' => 'RINCIAN KOMODITAS & ESTIMASI', 'align' => 'left'],
            ['key' => 'weight', 'label' => 'HASIL TIMBANGAN AKTUAL NETTO', 'align' => 'left'],
            ['key' => 'shipment', 'label' => 'NO. SJ & ARMADA', 'align' => 'left'],
            ['key' => 'status', 'label' => 'STATUS TRANSAKSI', 'align' => 'left'],
            ['key' => 'total', 'label' => 'TOTAL TAGIHAN FINAL', 'align' => 'left'],
            ['key' => 'actions', 'label' => 'BUKTI / AKSI', 'align' => 'right'],
        ];
    }

    /**
     * Lima baris pesanan pratinjau. "stage" mengikuti nilai tab statusTabs(),
     * sedangkan "hub" / "payment" mengikuti opsi pada filters().
     *
     * @return list<array<string, mixed>>
     */
    public static function orders(): array
    {
        $orders = [
            [
                'po' => 'ORD-GPA-202410-0089',
                'date' => '24 Okt 2024',
                'time' => '08:30 WIB',
                'hub_chip' => 'REG-HUB: BANDUNG UTARA',
                'commodities' => 'Selada Romaine (500kg), Tomat Beef (300kg)',
                'estimated' => 800.0,
                'grade_note' => 'Grade A Hydroponic Ready',
                'grade_tone' => 'success',
                'weight' => 794.0,
                'weight_label' => 'KG NETTO',
                'deviation_kg' => -6.0,
                'scale_note' => 'Tera Timbangan Digital Stamped Hub',
                'sj' => 'SJ-GPA-202410-0112',
                'vehicle' => 'Isuzu Elf [B 9421 TX]',
                'driver_note' => 'Driver: Joko Widodo (GPS ON)',
                'shipment_note' => 'Suhu Chiller: +4.2°C',
                'shipment_tone' => 'success',
                'status' => 'DALAM PENGIRIMAN',
                'status_tone' => 'accent',
                'status_meta' => 'ETA 04:30 WIB',
                'status_note' => 'Tujuan: CK Ciracas Hub 01',
                'total' => 11028000,
                'total_note' => 'Basis Netto 794 kg',
                'total_chip' => 'TOP 30H',
                'total_chip_tone' => 'success',
                'actions' => [
                    ['label' => 'Lacak Armada', 'icon' => 'truck', 'variant' => 'solid'],
                    ['label' => 'Lihat SJ', 'icon' => 'file-text', 'variant' => 'outline'],
                ],
                'stage' => 'pengiriman',
                'hub' => 'ciracas',
                'payment' => 'top30',
            ],
            [
                'po' => 'ORD-GPA-202410-0074',
                'date' => '22 Okt 2024',
                'time' => '07:15 WIB',
                'hub_chip' => 'REG-HUB: PENGALENGAN',
                'commodities' => 'Brokoli (300kg), Kol Segar (400kg)',
                'estimated' => 700.0,
                'grade_note' => 'Sertifikasi Good Agricultural Practice',
                'grade_tone' => 'success',
                'weight' => 698.5,
                'weight_label' => 'KG NETTO',
                'deviation_kg' => -1.5,
                'scale_note' => 'Timbang Dermaga Bongkar Verified',
                'sj' => 'SJ-GPA-202410-0098',
                'vehicle' => 'Mitsubishi [B 9188 SAA]',
                'driver_note' => 'Driver: Mulyono',
                'shipment_note' => 'PoD Digital Signed TTD',
                'shipment_tone' => 'success',
                'status' => 'SELESAI (POD SAH)',
                'status_tone' => 'brand',
                'status_meta' => 'Penerima: Pak Hendra',
                'status_note' => 'Diterima: 22 Okt 11:20 WIB',
                'total' => 10388000,
                'total_note' => 'Lunas via Bank Escrow',
                'total_chip' => 'FAKTUR PAJAK: FP-0994',
                'total_chip_tone' => 'success',
                'actions' => [
                    ['label' => 'Lihat Dokumen PoD', 'icon' => 'receipt', 'variant' => 'outline'],
                    ['label' => 'Unduh Invoice', 'icon' => 'download', 'variant' => 'outline'],
                ],
                'stage' => 'selesai',
                'hub' => 'pangalengan',
                'payment' => 'transfer',
            ],
            [
                'po' => 'ORD-GPA-202410-0062',
                'date' => '18 Okt 2024',
                'time' => '06:40 WIB',
                'hub_chip' => 'REG-HUB: LEMBANG 02',
                'commodities' => 'Tomat Beef Hidroponik (500kg)',
                'estimated' => 500.0,
                'grade_note' => 'Catatan Kualitir: Memar Transit Dock',
                'grade_tone' => 'danger',
                'weight' => 482.0,
                'weight_label' => 'KG BERSIH SAH',
                'weight_tone' => 'warning',
                'deviation_kg' => -18.0,
                'scale_note' => 'Penyesuaian BAP Timbang disetujui',
                'scale_tone' => 'warning',
                'shipment_breakdown' => [
                    ['label' => 'Kirim:', 'value' => '497.0 kg', 'tone' => 'default'],
                    ['label' => 'Retur Dock:', 'value' => '15.0 kg', 'tone' => 'danger'],
                ],
                'sj' => 'SJ-GPA-202410-0081',
                'vehicle' => 'Blindvan [B 9421 TX]',
                'driver_note' => 'Log Inspec: QC Mario',
                'shipment_note' => 'BAP: RET-202410-09',
                'shipment_tone' => 'warning',
                'status' => 'RETUR PARSIAL DICATAT',
                'status_tone' => 'warning',
                'status_meta' => 'Kompensasi kuota',
                'status_note' => 'aktif PO berikutnya',
                'total' => 5784000,
                'total_note' => 'Disesuaikan Nota Kredit -15kg',
                'total_chip' => 'NK-GPA-0044 DITERBITKAN',
                'total_chip_tone' => 'success',
                'actions' => [
                    ['label' => 'Lihat Berita Acara', 'icon' => 'scale', 'variant' => 'warning'],
                ],
                'stage' => 'selesai',
                'hub' => 'ciracas',
                'payment' => 'top30',
            ],
            [
                'po' => 'ORD-GPA-202410-0092',
                'date' => '24 Okt 2024',
                'time' => '14:10 WIB',
                'hub_chip' => 'REG-HUB: CIAWI BOGOR',
                'commodities' => 'Brokoli Super (250kg), Kol Putih (200kg)',
                'estimated' => 450.0,
                'grade_note' => 'Standar Restoran QSR',
                'grade_tone' => 'success',
                'weight' => null,
                'weight_chip' => 'PROSES PENIMBANGAN',
                'scale_note' => 'Hub Ciawi Station 2 Scales',
                'scale_target' => 'Target lock netto: 18:00 WIB',
                'scale_tone' => 'success',
                'sj' => null,
                'sj_pending' => 'MENUNGGU BERAT AKTUAL',
                'vehicle' => 'Alokasi CDD Chilled Unit B',
                'driver_note' => 'Hub Ciawi Staging Area',
                'status' => 'PACKING & SORTIR GUDANG',
                'status_tone' => 'pill',
                'status_meta' => 'Target dispatch',
                'status_note' => '21:00 WIB',
                'total' => 7600000,
                'total_note' => 'Nilai Estimasi Draf PO',
                'total_chip' => 'Invoice final lock paska timbang',
                'total_chip_tone' => 'quiet',
                'actions' => [
                    ['label' => 'Detail Mutu', 'icon' => 'eye', 'variant' => 'outline'],
                ],
                'stage' => 'timbang',
                'hub' => 'ciawi',
                'payment' => 'top30',
            ],
            [
                'po' => 'ORD-GPA-202410-0095',
                'date' => '24 Okt 2024',
                'time' => '16:20 WIB',
                'hub_chip' => 'REG-HUB: CIWIDEY SPECIALTY',
                'commodities' => 'Stroberi Ciwidey (150kg)',
                'estimated' => 150.0,
                'grade_note' => 'Grade Super Fresh Pick',
                'grade_tone' => 'default',
                'weight' => null,
                'weight_pending' => 'Menunggu Alokasi Mitra Tani',
                'scale_note' => 'Slot Ciwidey Group A',
                'scale_tone' => 'success',
                'sj' => null,
                'sj_pending' => '-',
                'vehicle' => null,
                'driver_note' => 'Armada belum ditugaskan',
                'status' => 'VERIFIKASI ADMIN SEKRE',
                'status_tone' => 'track',
                'status_meta' => 'Antrean audit',
                'status_note' => 'kredit & kuota',
                'total' => 5250000,
                'total_note' => 'Plafon TOP 30 Dipesan',
                'total_chip' => null,
                'actions' => [
                    ['label' => 'Batal PO', 'icon' => 'x', 'variant' => 'danger'],
                ],
                'stage' => 'verifikasi',
                'hub' => 'ciawi',
                'payment' => 'top30',
            ],
        ];

        foreach ($orders as &$order) {
            $order = self::decorate($order);
        }

        unset($order);

        return $orders;
    }

    /**
     * Melengkapi baris pesanan dengan nilai turunan (persen deviasi, label
     * kilogram) sehingga view dan Alpine tidak perlu menghitung ulang.
     *
     * @param  array<string, mixed>  $order
     * @return array<string, mixed>
     */
    private static function decorate(array $order): array
    {
        $estimated = (float) $order['estimated'];
        $deviation = (float) ($order['deviation_kg'] ?? 0);
        $percent = $estimated > 0 ? round(abs($deviation) / $estimated * 100, 2) : 0.0;

        $order['estimated_label'] = number_format($estimated, 1, ',', '.');
        $order['deviation_percent'] = $percent;
        $order['deviation_label'] = $deviation === 0.0
            ? null
            : sprintf(
                'Deviasi %s kg (%s%% %s)',
                number_format($deviation, 1, ',', '.'),
                number_format($percent, 2, ',', '.'),
                $percent <= 1 ? 'wajar susut' : 'di atas toleransi'
            );
        $order['weight_label_value'] = $order['weight'] === null
            ? null
            : number_format((float) $order['weight'], 1, ',', '.');

        return $order;
    }

    /**
     * @return array<string, mixed>
     */
    public static function integrity(): array
    {
        return [
            'title' => 'Prinsip Integritas Transaksi & Tera Netto Gudang (GPA PRD Rule 04 & 05 Compliance)',
            'body' => 'Seluruh penimbangan komoditas basah menggunakan timbangan terkalibrasi berkala Badan Metrologi Legal. Deviasi susut alami <= 1.0% ditanggung pembeli, deviasi > 1.0% otomatis mengkoreksi nilai tagihan faktur final paska validasi Berita Acara Rekonsiliasi PoD.',
            'chip' => 'System Audit Pass 2024',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function metrics(): array
    {
        return [
            [
                'label' => 'Total Tonase Bulan Ini',
                'value' => '38.450,0',
                'unit' => 'KG Komoditas Teralokasi',
                'note' => null,
                'icon' => 'scale',
                'tone' => 'default',
            ],
            [
                'label' => 'Rata-Rata Deviasi',
                'value' => '-0,42%',
                'unit' => 'Status Normal (Toleransi < 1%)',
                'note' => null,
                'icon' => 'chart',
                'tone' => 'success',
            ],
            [
                'label' => 'Dispatch Hari Ini',
                'value' => '4 Armada',
                'unit' => null,
                'note' => 'Rute Bogor, Pengalengan, Ciracas',
                'icon' => 'truck',
                'tone' => 'default',
            ],
            [
                'label' => 'On-Time Delivery',
                'value' => '98,2%',
                'unit' => null,
                'note' => '+0.8% dari target SLA',
                'icon' => 'badge-check',
                'tone' => 'success',
            ],
            [
                'label' => 'Status Klaim Retur',
                'value' => '1 Nota',
                'unit' => null,
                'note' => 'Kredit Selesai Direkonsiliasi',
                'icon' => 'alert-triangle',
                'tone' => 'success',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function pagination(): array
    {
        return [
            'total' => 52,
            'per_page' => 5,
            'last_page' => 11,
        ];
    }
}
