<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data konsol Koordinator Lapangan.
 *
 * Seluruh nilai di bawah adalah data simulasi yang mencerminkan design
 * "Dashboard Operasional Pasokan & Kesiapan Panen" untuk peran Koordinator
 * Lapangan: kontrol anti-overselling berbasis kuota (Rule 03), tiket timbangan
 * netto Gate-01, serta staging cold-chain 4 derajat Celsius.
 */
class CoordinatorDashboardData
{
    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => self::operator($user),
            'heading' => self::heading(),
            'metrics' => self::metrics(),
            'supply' => self::supply(),
            'packing' => self::packing(),
            'gate' => self::gate(),
            'footer' => self::footer(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function operator(?User $user = null): array
    {
        $name = $user?->name ?: 'Agus Tusan';

        return [
            'role' => 'Koordinator Lapangan',
            'name' => $name,
            'code' => '002',
            'initials' => self::initials($name),
            'branch' => 'Kebun Bogor Utara - Gate 01',
            'email' => $user?->email ?? 'koordinator.lapangan@agroorder.co.id',
            'shift' => 'Shift Pagi 07.00-15.00 WIB',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function heading(): array
    {
        return [
            'eyebrow' => 'DASHBOARD KOORDINATOR',
            'title_before' => 'Ringkasan Pasokan & Kesiapan',
            'title_after' => 'Panen',
            'subtitle' => 'Pantau kuota panen, hasil timbang, persiapan pesanan dingin 4'."\u{00B0}".'C, dan pemenuhan pesanan pelanggan.',
            'actions' => [
                ['key' => 'export', 'label' => 'Unduh Rekap (CSV)', 'icon' => 'download', 'variant' => 'ghost'],
                ['key' => 'filter', 'label' => 'Pilih Sentra', 'icon' => 'filter', 'variant' => 'ink'],
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
                'key' => 'verified',
                'label' => 'Pesanan Siap Dikemas',
                'icon' => 'badge-check',
                'value' => '1.840',
                'unit' => 'KG',
                'unit_tone' => 'text-ink-body',
                'foot_label' => 'Terdiri dari',
                'foot_value' => '28 PO Aktif',
                'chip' => ['label' => '100% Terkunci', 'class' => 'bg-accent text-success-ink'],
            ],
            [
                'key' => 'weighing',
                'label' => 'Komoditas Perlu Ditimbang',
                'icon' => 'package',
                'value' => '5',
                'unit' => 'Komoditas Inti',
                'unit_tone' => 'text-ink-body',
                'foot_label' => 'Intake Masuk:',
                'foot_value' => '12 Batch',
                'chip' => ['label' => '8 Diproses / 4 Antrean', 'class' => 'bg-surface-pill text-ink'],
            ],
            [
                'key' => 'buffer',
                'label' => 'Kapasitas Stok Cadangan',
                'icon' => 'gauge',
                'value' => '82.4%',
                'unit' => 'Terpakai',
                'unit_tone' => 'text-success-deep',
                'foot_label' => 'Pendinginan & Chiller A/B',
                'foot_value' => '5.93 / 7.20 ton',
                'chip' => null,
            ],
            [
                'key' => 'shrinkage',
                'label' => 'Susut Setelah Ditimbang',
                'icon' => 'scale',
                'value' => '-1.18%',
                'unit' => 'Susut',
                'unit_tone' => 'text-success-deep',
                'foot_label' => 'Batas SOP:',
                'foot_value' => '-2.5%',
                'chip' => ['label' => 'Dalam Batas', 'class' => 'bg-accent text-success-ink'],
            ],
        ];
    }

    /**
     * Tabel "Manajemen Stok Panen & Buffer Stock (Kontrol Pasokan)".
     *
     * @return array<string, mixed>
     */
    public static function supply(): array
    {
        return [
            'title' => 'Stok Panen & Stok Cadangan',
            'subtitle' => '(Kontrol Pasokan)',
            'description' => 'Cek kuota panen sebelum membagikan stok ke pesanan, agar jumlah yang dijanjikan tidak melebihi pasokan.',
            'totals' => [
                ['label' => 'Total Stok', 'value' => '1.920 KG', 'tone' => 'text-ink'],
                ['label' => 'Untuk Pesanan Aktif', 'value' => '1.840 KG', 'tone' => 'text-success-deep'],
                ['label' => 'Sisa Stok Cadangan', 'value' => '+80 KG', 'tone' => 'text-ink'],
            ],
            'columns' => [
                'Komoditas',
                'Pesanan Aktif',
                'Pasokan Petani',
                'Stok Cadangan',
                'Total Tersedia',
                'Status & Selisih',
                'Tindakan',
            ],
            'rows' => self::supplyRows(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function supplyRows(): array
    {
        return [
            [
                'key' => 'romaine',
                'name' => 'Selada Romaine Highland',
                'batch' => 'BATCH: ROM-SB4-8902',
                'grade' => 'GRADE A+',
                'bar' => 7,
                'order' => 520,
                'supply' => 480,
                'group' => 'Kelompok Tani',
                'partner' => 'Harapan Jaya',
                'buffer' => 60,
                'zone' => 'Chiller 01',
                'gap' => '+20 KG',
            ],
            [
                'key' => 'tomat-beef',
                'name' => 'Tomat Beef Greenhouse',
                'batch' => 'BATCH: TMT-SB4-1102',
                'grade' => 'CALIBRE 80-95mm',
                'bar' => 6,
                'order' => 420,
                'supply' => 400,
                'group' => 'Mitra Tani Ciater',
                'partner' => 'Mandiri',
                'buffer' => 40,
                'zone' => 'Staging Ambient',
                'gap' => '+20 KG',
            ],
            [
                'key' => 'brokoli',
                'name' => 'Brokoli Highland Super',
                'batch' => 'BATCH: BRK-SB4-3319',
                'grade' => 'DENSE FLORET',
                'bar' => 6,
                'order' => 380,
                'supply' => 350,
                'group' => 'Kelompok Tani',
                'partner' => 'Bukit Hijau',
                'buffer' => 45,
                'zone' => 'Pre-Cool Bay 2',
                'gap' => '+15 KG',
            ],
            [
                'key' => 'stroberi',
                'name' => 'Stroberi Sweet Heart (Ciwidey)',
                'batch' => 'BATCH: STR-SB4-0991',
                'grade' => 'GRADE PREMIUM EXPORT',
                'bar' => 5,
                'accent' => true,
                'order' => 240,
                'supply' => 220,
                'group' => 'Agro Wisata',
                'partner' => 'Ciwidey Barat',
                'buffer' => 30,
                'zone' => 'Cold Chiller 04',
                'gap' => '+10 KG',
            ],
            [
                'key' => 'kol-putih',
                'name' => 'Kol Putih Highland Segar',
                'batch' => 'BATCH: CAB-SB4-4100',
                'grade' => 'STANDAR PASAR RITEL',
                'bar' => 5,
                'order' => 280,
                'supply' => 270,
                'group' => 'Petani Mandiri',
                'partner' => 'Kasomalang',
                'buffer' => 25,
                'zone' => 'Dry Staging 02',
                'gap' => '+15 KG',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function packing(): array
    {
        return [
            'title' => 'Persiapan & Pengemasan Pesanan',
            'subtitle' => '3 batch sedang disiapkan di area 01-03',
            'chip' => 'Suhu chiller: 4'."\u{00B0}".'C (batas '."\u{00B1}".'0.5'."\u{00B0}".'C)',
            'orders' => [
                [
                    'po' => 'PO-HTL-2201',
                    'client' => 'Hotel Grand Pasundan Ritel (Bandung)',
                    'dispatch' => 'Kirim: 16:30 WIB',
                    'dispatch_tone' => 'accent',
                    'allocation' => '680 KG (Selada & Tomat)',
                    'bay' => 'Bay-02 (Cold 3.8'."\u{00B0}".'C)',
                    'status' => '85% sortir selesai',
                    'percent' => 85,
                ],
                [
                    'po' => 'PO-RES-1049',
                    'client' => 'Restoran Segar Nusantara (Jakarta Hub)',
                    'dispatch' => 'Kirim: 17:00 WIB',
                    'dispatch_tone' => 'accent',
                    'allocation' => '540 KG (Brokoli & Kol)',
                    'bay' => 'Bay-01 (Cold 4.1'."\u{00B0}".'C)',
                    'status' => '62% dikemas',
                    'percent' => 62,
                ],
                [
                    'po' => 'PO-EXP-0082',
                    'client' => 'Sentra Supermarket Eksklusif (Bekasi)',
                    'dispatch' => 'Kirim: 18:15 WIB',
                    'dispatch_tone' => 'ink',
                    'allocation' => '620 KG (5 komoditas)',
                    'bay' => 'Area-03 (Pendinginan Awal)',
                    'status' => '35% menunggu stok',
                    'percent' => 35,
                ],
            ],
            'footer' => [
                'label' => 'Prosedur pemantauan suhu: sensor sesuai ISO 17025',
                'link' => 'Buka pengaturan ruang dingin',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function gate(): array
    {
        return [
            'title' => 'Catatan Timbangan Gate-01',
            'subtitle' => 'Hasil timbang bersih terkini',
            'logs' => [
                [
                    'ticket' => 'TIKET GATE #8821',
                    'time' => '13:58 WIB',
                    'name' => 'Asep Sobana',
                    'commodity' => 'Selada Romaine',
                    'gross' => 495,
                    'tare' => 15,
                    'net' => 480,
                    'qc' => 'Lolos QC: Grade A',
                    'note' => 'Brix 4.2 '."\u{2022}".' Kadar air 94%',
                ],
                [
                    'ticket' => 'TIKET GATE #8820',
                    'time' => '13:42 WIB',
                    'name' => 'Dedi Rohimat',
                    'commodity' => 'Tomat Beef',
                    'gross' => 418,
                    'tare' => 18,
                    'net' => 400,
                    'qc' => 'Lolos QC: Grade A',
                    'note' => 'Susut -0.9% (sesuai batas)',
                ],
                [
                    'ticket' => 'TIKET GATE #8819',
                    'time' => '13:15 WIB',
                    'name' => 'Kurniawan',
                    'commodity' => 'Brokoli Highland',
                    'gross' => 366,
                    'tare' => 16,
                    'net' => 350,
                    'qc' => 'Lolos QC: Grade A+',
                    'note' => 'Disimpan di ruang dingin 01',
                ],
            ],
            'footer' => [
                'label' => 'Timbangan digital tera #MET-SUB-01',
                'action' => 'Tambah tiket',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function footer(): array
    {
        return [
            'left' => [
                ['label' => 'Gate-01 Aktif', 'tone' => 'success'],
                ['label' => 'Sinkron 13:58 WIB', 'tone' => 'success'],
                ['label' => '3 batch disiapkan', 'tone' => 'ink'],
                ['label' => 'Kalibrasi ISO 17025', 'tone' => 'ink'],
            ],
            'right' => 'Pembaruan terakhir: 14:00 WIB '."\u{2022}".' Susut -1.18%, masih dalam batas SOP.',
        ];
    }

    private static function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        $initials = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $initials .= mb_strtoupper(mb_substr($part, 0, 1));

            if (mb_strlen($initials) === 2) {
                break;
            }
        }

        return $initials !== '' ? $initials : 'AT';
    }
}
