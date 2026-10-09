<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data konsol verifikasi pesanan & kesiapan alokasi (Sekretaris).
 *
 * Seluruh nilai di bawah adalah data simulasi yang mencerminkan design
 * "Konsol Verifikasi Pesanan & Kesiapan Alokasi (Sekretaris Desk)".
 * Nanti dapat dipindahkan ke query Eloquent / service layer tanpa mengubah
 * struktur array pada view.
 */
class SecretaryVerificationData
{
    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        $orders = self::orders();

        return [
            'operator' => SecretaryDashboardData::operator($user),
            'heading' => self::heading(),
            'steps' => self::steps(),
            'queue' => self::queue($orders),
            'checklist' => self::checklist(),
            'rule03' => self::rule03(),
            'rule04' => self::rule04(),
            'rule14' => self::rule14(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function heading(): array
    {
        return [
            'title' => 'Konsol Verifikasi Pesanan & Kesiapan Alokasi (Sekretaris Desk)',
            'subtitle' => 'Eksekusi validasi aturan bisnis: stok buffer, kuantitas netto riil, hard-gate surat jalan, serta rekonsiliasi manual.',
            'compliance_label' => 'Tingkat Kepatuhan Validasi',
            'compliance_value' => '100.0% (Zero Bypass)',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function steps(): array
    {
        return [
            [
                'number' => '01',
                'label' => 'Antrean Verifikasi Pesanan',
                'note' => 'Menunggu',
                'active' => true,
                'message' => 'Antrean verifikasi pesanan sedang dikerjakan pada modul ini.',
            ],
            [
                'number' => '02',
                'label' => 'Konsolidasi Faktur & Penagihan Tempo',
                'note' => null,
                'active' => false,
                'message' => 'Modul konsolidasi faktur & penagihan tempo sedang disiapkan.',
            ],
            [
                'number' => '03',
                'label' => 'Verifikasi Pembayaran Manual',
                'note' => null,
                'active' => false,
                'message' => 'Modul verifikasi pembayaran manual sedang disiapkan.',
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $orders
     * @return array<string, mixed>
     */
    public static function queue(array $orders): array
    {
        $pending = count($orders);
        $b2b = count(array_filter($orders, fn (array $order) => $order['channel'] === 'b2b'));
        $wa = $pending - $b2b;

        return [
            'title' => 'Antrean Pesanan Masuk',
            'chip' => $pending.' Item',
            'filters' => [
                ['key' => 'all', 'label' => "Semua ({$pending})"],
                ['key' => 'b2b', 'label' => "Web B2B ({$b2b})"],
                ['key' => 'wa', 'label' => "WA / Manual ({$wa})"],
            ],
            'orders' => $orders,
            'footer_note' => 'Hard-gate checklist wajib terisi penuh sebelum PO diteruskan ke koordinator lapangan.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function checklist(): array
    {
        return [
            'title' => 'Hard-Gate Checklist Sekretariat',
            'items' => [
                'Pastikan alokasi kebun petani aktif & terverifikasi.',
                'Jangan terbitkan Surat Jalan sebelum verifikasi tonase riil timbangan.',
                'Klien tempo wajib memiliki saldo plafon aktif.',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function rule03(): array
    {
        return [
            'title' => 'Validasi Stok & Alokasi Pasokan',
            'foot_left' => 'Alokasi kuota petani telah disinkronkan ke Dasbor Koordinator Lapangan Sukabumi & Lembang.',
            'foot_right' => 'Rule 03 Status: Enforced & Verified',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function rule04(): array
    {
        return [
            'chip' => 'SOP Wajib Sistem',
            'title' => 'Pemberitahuan Aturan Bisnis #04 (Netto vs Estimasi):',
            'body_before' => 'Kuantitas pesanan awal',
            'body_mid' => 'adalah estimasi perikatan PO. Nilai final tagihan, batas tonase armada, dan penerbitan Surat Jalan',
            'highlight' => 'wajib menunggu Actual Net Weight',
            'body_after' => 'dari timbangan Koordinator Lapangan pasca panen & sortir grading.',
            'bullets' => [
                'Hard-Gate Surat Jalan: Terkunci hingga Korlap submit timbangan',
                'Toleransi Shrinkage: Maksimal 2.5%',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function rule14(): array
    {
        return [
            'chip' => 'Rule 14 Preview',
            'title' => 'Konsolidasi Faktur Bulanan & Penagihan Tempo',
            'draft_label' => 'Cetak Draft Tagihan',
            'issue_label' => 'Terbitkan Invoice Tempo (TOP 30)',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function orders(): array
    {
        return [
            [
                'id' => 'ORD-GPA-202610-0042',
                'channel' => 'b2b',
                'chip' => null,
                'client' => 'Katering Berkah Mandiri',
                'meta' => 'ID: AGRO-B2B-4091 • B2B Portal',
                'entered' => 'Masuk: 18m lalu',
                'queue_commodity_label' => 'Komoditas Utama',
                'queue_commodities' => [
                    ['name' => 'Selada Romaine', 'weight' => '800 kg (Est PO)', 'class' => 'text-ink', 'weight_class' => 'text-ink-body'],
                ],
                'queue_estimate_label' => 'Estimasi Nilai PO',
                'queue_estimate' => 'Rp 12.000.000',
                'queue_estimate_note' => ['label' => 'Ready Alokasi', 'class' => 'text-success-deep'],
                'queue_status' => ['label' => 'Stok Buffer Aman', 'icon' => 'shield', 'class' => 'bg-accent text-success-ink'],
                'source' => 'Source: Portal Web B2B',
                'po_ref' => 'PO REQ: PO-BKM/2026/X/089',
                'order_no' => 'Order #ORD-GPA-202610-0042',
                'status' => ['label' => 'Menunggu Verifikasi Sekre', 'class' => 'bg-accent text-success-ink'],
                'created' => 'Dibuat: 26 Okt 2026, 08:24 WIB',
                'identity' => [
                    [
                        'label' => 'Identitas Pembeli',
                        'name' => 'Katering Berkah Mandiri',
                        'code' => 'ID: AGRO-B2B-4091',
                        'code_class' => 'text-success-deep',
                        'note' => 'Status: Kontrak B2B Terverifikasi (Plafon Rp 85.000.000)',
                    ],
                    [
                        'label' => 'Kontak & PIC Penerima',
                        'name' => 'Bpk. Hendra Pratama',
                        'code' => '0812-3456-7890 (WA Aktif)',
                        'code_class' => 'text-ink-body',
                        'note' => 'Email: logistic@berkahkatering.co.id',
                    ],
                    [
                        'label' => 'Destinasi & Syarat Armada',
                        'name' => 'Gudang Sentral Tajur Bogor',
                        'code' => 'Syarat: Truk Box Pendingin (Chilled 4°C - 8°C)',
                        'code_class' => 'text-danger',
                        'note' => 'Jadwal Tiba: 27 Okt 2026, Pukul 06:00 WIB',
                    ],
                ],
                'rule03_status' => ['label' => 'Aman / Tercukupi', 'icon' => 'check', 'class' => 'bg-accent text-success-ink'],
                'allocations' => [
                    [
                        'name' => 'Selada Romaine Grade A Super',
                        'sku' => 'SKU: GPA-VEG-ROM-01 • Toleransi Sortir max 3%',
                        'request' => '800',
                        'request_unit' => 'KG',
                        'system' => '1.250',
                        'system_unit' => 'KG',
                        'system_class' => 'text-success-deep',
                        'surplus' => 'Surplus +450 kg',
                        'surplus_class' => 'text-success-deep',
                        'plan' => [
                            ['label' => '1. Petani Binaan Kebun C (Panen Pagi):', 'qty' => '500 kg', 'percent' => 55, 'fill' => 'bg-success-deep'],
                            ['label' => '2. Buffer Stock Mitra Lembang:', 'qty' => '300 kg', 'percent' => 33, 'fill' => 'bg-accent-edge'],
                        ],
                        'status' => ['label' => 'Terkunci', 'icon' => 'lock', 'class' => 'bg-accent text-success-ink'],
                    ],
                ],
                'pricing' => [
                    ['label' => 'Harga Satuan PO', 'value' => 'Rp 15.000', 'suffix' => '/ kg', 'note' => null, 'highlight' => false],
                    ['label' => 'Estimasi Bruto PO', 'value' => 'Rp 12.000.000', 'suffix' => null, 'note' => null, 'highlight' => false],
                    ['label' => 'PPN / Retribusi (0%)', 'value' => 'Rp 0', 'suffix' => null, 'note' => '(Komoditas Pokok)', 'highlight' => false],
                    ['label' => 'Total Estimasi PO', 'value' => 'Rp 12.000.000', 'suffix' => null, 'note' => null, 'highlight' => true],
                ],
                'rule04_qty' => '800 kg',
                'deliveries' => [
                    [
                        'sj' => 'SJ-GPA/2026/10-0198',
                        'date' => '20 Okt 2026 (05:40 WIB)',
                        'commodity' => 'Selada Keriting Hijau',
                        'netto' => 'Actual Netto: 650.0 kg (Net Weight Valid)',
                        'rate' => 'Rp 14.500 / kg',
                        'subtotal' => 'Rp 9.425.000',
                    ],
                    [
                        'sj' => 'SJ-GPA/2026/10-0214',
                        'date' => '23 Okt 2026 (06:12 WIB)',
                        'commodity' => 'Wortel Berastagi Brankas',
                        'netto' => 'Actual Netto: 1.100.0 kg (Net Weight Valid)',
                        'rate' => 'Rp 11.000 / kg',
                        'subtotal' => 'Rp 12.100.000',
                    ],
                    [
                        'sj' => 'SJ-GPA/2026/10-0229',
                        'date' => '25 Okt 2026 (05:15 WIB)',
                        'commodity' => 'Bayam Horenzo Premium',
                        'netto' => 'Actual Netto: 50.0 kg (Net Weight Valid)',
                        'rate' => 'Rp 20.000 / kg',
                        'subtotal' => 'Rp 1.000.000',
                    ],
                ],
                'deliveries_total' => 'Rp 22.525.000',
                'deliveries_held' => '3 Dokumen Tertahan',
                'plafon' => 'Rp 85.000.000',
            ],
            [
                'id' => 'ORD-GPA-202610-0045',
                'channel' => 'wa',
                'chip' => ['label' => 'Manual WA', 'class' => 'bg-warning-soft text-warning-ink'],
                'client' => 'Hotel Grand Pangrango',
                'meta' => 'PIC: Ibu Rina (F&B Dept)',
                'entered' => 'Masuk: 42m lalu',
                'queue_commodity_label' => 'Komoditas',
                'queue_commodities' => [
                    ['name' => 'Tomat', 'weight' => '150kg', 'class' => 'text-ink', 'weight_class' => 'text-ink-body'],
                    ['name' => 'Brokoli', 'weight' => '100kg', 'class' => 'text-ink', 'weight_class' => 'text-ink-body'],
                ],
                'queue_estimate_label' => 'Estimasi Total',
                'queue_estimate' => 'Rp 4.675.000',
                'queue_estimate_note' => ['label' => 'Termin: CBD', 'class' => 'text-ink-body'],
                'queue_status' => ['label' => 'Validasi Nota Manual', 'icon' => 'file-text', 'class' => 'bg-surface-pill text-ink-body'],
                'source' => 'Source: WhatsApp Manual',
                'po_ref' => 'WA REQ: WA-0045-102',
                'order_no' => 'Order #ORD-GPA-202610-0045',
                'status' => ['label' => 'Menunggu Verifikasi Sekre', 'class' => 'bg-accent text-success-ink'],
                'created' => 'Dibuat: 26 Okt 2026, 07:42 WIB',
                'identity' => [
                    [
                        'label' => 'Identitas Pembeli',
                        'name' => 'Hotel Grand Pangrango',
                        'code' => 'ID: AGRO-B2B-3180',
                        'code_class' => 'text-success-deep',
                        'note' => 'Status: Kontrak B2B Terverifikasi (Plafon Rp 40.000.000)',
                    ],
                    [
                        'label' => 'Kontak & PIC Penerima',
                        'name' => 'Ibu Rina (F&B Dept)',
                        'code' => '+62 812-9044-2211 (WA Aktif)',
                        'code_class' => 'text-ink-body',
                        'note' => 'Email: purchasing@grandpangrango.co.id',
                    ],
                    [
                        'label' => 'Destinasi & Syarat Armada',
                        'name' => 'Grand Pangrango Hotel, Bandung',
                        'code' => 'Syarat: Truk Box Pendingin (Chilled 4°C - 8°C)',
                        'code_class' => 'text-danger',
                        'note' => 'Jadwal Tiba: 27 Okt 2026, Pukul 09:00 WIB',
                    ],
                ],
                'rule03_status' => ['label' => 'Aman / Tercukupi', 'icon' => 'check', 'class' => 'bg-accent text-success-ink'],
                'allocations' => [
                    [
                        'name' => 'Tomat Beef Super',
                        'sku' => 'SKU: GPA-VEG-TOM-02 • Toleransi Sortir max 4%',
                        'request' => '150',
                        'request_unit' => 'KG',
                        'system' => '400',
                        'system_unit' => 'KG',
                        'system_class' => 'text-success-deep',
                        'surplus' => 'Surplus +250 kg',
                        'surplus_class' => 'text-success-deep',
                        'plan' => [
                            ['label' => '1. Petani Binaan Kebun A (Panen Siang):', 'qty' => '180 kg', 'percent' => 66, 'fill' => 'bg-success-deep'],
                            ['label' => '2. Buffer Stock Mitra Ciwidey:', 'qty' => '60 kg', 'percent' => 22, 'fill' => 'bg-accent-edge'],
                        ],
                        'status' => ['label' => 'Terkunci', 'icon' => 'lock', 'class' => 'bg-accent text-success-ink'],
                    ],
                    [
                        'name' => 'Brokoli Super Grade A',
                        'sku' => 'SKU: GPA-VEG-BRK-03 • Toleransi Sortir max 3%',
                        'request' => '100',
                        'request_unit' => 'KG',
                        'system' => '180',
                        'system_unit' => 'KG',
                        'system_class' => 'text-success-deep',
                        'surplus' => 'Surplus +80 kg',
                        'surplus_class' => 'text-success-deep',
                        'plan' => [
                            ['label' => '1. Petani Binaan Kebun D (Panen Pagi):', 'qty' => '100 kg', 'percent' => 55, 'fill' => 'bg-success-deep'],
                            ['label' => '2. Buffer Stock Mitra Lembang:', 'qty' => '40 kg', 'percent' => 22, 'fill' => 'bg-accent-edge'],
                        ],
                        'status' => ['label' => 'Terkunci', 'icon' => 'lock', 'class' => 'bg-accent text-success-ink'],
                    ],
                ],
                'pricing' => [
                    ['label' => 'Harga Satuan PO', 'value' => 'Rp 21.000', 'suffix' => '/ kg', 'note' => 'Brokoli Rp 15.250 / kg', 'highlight' => false],
                    ['label' => 'Estimasi Bruto PO', 'value' => 'Rp 4.675.000', 'suffix' => null, 'note' => null, 'highlight' => false],
                    ['label' => 'PPN / Retribusi (0%)', 'value' => 'Rp 0', 'suffix' => null, 'note' => '(Komoditas Pokok)', 'highlight' => false],
                    ['label' => 'Total Estimasi PO', 'value' => 'Rp 4.675.000', 'suffix' => null, 'note' => null, 'highlight' => true],
                ],
                'rule04_qty' => '250 kg',
                'deliveries' => [
                    [
                        'sj' => 'SJ-GPA/2026/10-0207',
                        'date' => '22 Okt 2026 (06:40 WIB)',
                        'commodity' => 'Tomat Beef Super',
                        'netto' => 'Actual Netto: 140.0 kg (Net Weight Valid)',
                        'rate' => 'Rp 21.000 / kg',
                        'subtotal' => 'Rp 2.940.000',
                    ],
                ],
                'deliveries_total' => 'Rp 2.940.000',
                'deliveries_held' => '1 Dokumen Tertahan',
                'plafon' => 'Rp 40.000.000',
            ],
            [
                'id' => 'ORD-GPA-202610-0039',
                'channel' => 'b2b',
                'chip' => ['label' => 'Butuh Buffer', 'class' => 'bg-danger-soft text-danger-ink'],
                'client' => 'Resto Daun Hijau',
                'meta' => 'B2B Portal • Cabang Baranangsiang',
                'entered' => 'Masuk: 1j 10m lalu',
                'queue_commodity_label' => 'Komoditas',
                'queue_commodities' => [
                    ['name' => 'Pakcoy Baby', 'weight' => '400kg', 'class' => 'text-danger', 'weight_class' => 'text-danger'],
                ],
                'queue_estimate_label' => 'Estimasi Nilai PO',
                'queue_estimate' => 'Rp 3.800.000',
                'queue_estimate_note' => ['label' => 'Buffer Defisit 80 kg', 'class' => 'text-danger'],
                'queue_status' => ['label' => 'Injeksi Mitra Cisarua', 'icon' => 'truck', 'class' => 'bg-warning-border/40 text-warning-ink'],
                'source' => 'Source: Portal Web B2B',
                'po_ref' => 'PO REQ: PO-RDH/2026/X/012',
                'order_no' => 'Order #ORD-GPA-202610-0039',
                'status' => ['label' => 'Perlu Injeksi Mitra', 'class' => 'bg-warning-soft text-warning-ink'],
                'created' => 'Dibuat: 26 Okt 2026, 06:14 WIB',
                'identity' => [
                    [
                        'label' => 'Identitas Pembeli',
                        'name' => 'Resto Daun Hijau',
                        'code' => 'ID: AGRO-B2B-2255',
                        'code_class' => 'text-success-deep',
                        'note' => 'Status: Kontrak B2B Terverifikasi (Plafon Rp 25.000.000)',
                    ],
                    [
                        'label' => 'Kontak & PIC Penerima',
                        'name' => 'Bpk. Rahmat (Operasional)',
                        'code' => '0812-7788-1200 (WA Aktif)',
                        'code_class' => 'text-ink-body',
                        'note' => 'Email: ops@daunhijau.id',
                    ],
                    [
                        'label' => 'Destinasi & Syarat Armada',
                        'name' => 'Resto Daun Hijau Cabang Baranangsiang',
                        'code' => 'Syarat: Truk Box Pendingin (Chilled 4°C - 8°C)',
                        'code_class' => 'text-danger',
                        'note' => 'Jadwal Tiba: 27 Okt 2026, Pukul 11:00 WIB',
                    ],
                ],
                'rule03_status' => ['label' => 'Perlu Injeksi', 'icon' => 'alert-triangle', 'class' => 'bg-warning-soft text-warning-ink'],
                'allocations' => [
                    [
                        'name' => 'Pakcoy Baby Premium',
                        'sku' => 'SKU: GPA-VEG-PKC-04 • Toleransi Sortir max 2%',
                        'request' => '400',
                        'request_unit' => 'KG',
                        'system' => '320',
                        'system_unit' => 'KG',
                        'system_class' => 'text-danger',
                        'surplus' => 'Defisit -80 kg',
                        'surplus_class' => 'text-danger',
                        'plan' => [
                            ['label' => '1. Petani Binaan Kebun B (Panen Siang):', 'qty' => '200 kg', 'percent' => 50, 'fill' => 'bg-success-deep'],
                            ['label' => '2. Buffer Stock Mitra Cisarua:', 'qty' => '120 kg', 'percent' => 30, 'fill' => 'bg-accent-edge'],
                        ],
                        'status' => ['label' => 'Perlu Injeksi', 'icon' => 'alert-triangle', 'class' => 'bg-warning-soft text-warning-ink'],
                    ],
                ],
                'pricing' => [
                    ['label' => 'Harga Satuan PO', 'value' => 'Rp 9.500', 'suffix' => '/ kg', 'note' => null, 'highlight' => false],
                    ['label' => 'Estimasi Bruto PO', 'value' => 'Rp 3.800.000', 'suffix' => null, 'note' => null, 'highlight' => false],
                    ['label' => 'PPN / Retribusi (0%)', 'value' => 'Rp 0', 'suffix' => null, 'note' => '(Komoditas Pokok)', 'highlight' => false],
                    ['label' => 'Total Estimasi PO', 'value' => 'Rp 3.800.000', 'suffix' => null, 'note' => null, 'highlight' => true],
                ],
                'rule04_qty' => '400 kg',
                'deliveries' => [
                    [
                        'sj' => 'SJ-GPA/2026/10-0189',
                        'date' => '19 Okt 2026 (05:55 WIB)',
                        'commodity' => 'Pakcoy Baby Premium',
                        'netto' => 'Actual Netto: 280.0 kg (Net Weight Valid)',
                        'rate' => 'Rp 9.500 / kg',
                        'subtotal' => 'Rp 2.660.000',
                    ],
                ],
                'deliveries_total' => 'Rp 2.660.000',
                'deliveries_held' => '1 Dokumen Tertahan',
                'plafon' => 'Rp 25.000.000',
            ],
        ];
    }
}
