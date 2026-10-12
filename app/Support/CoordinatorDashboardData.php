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
            'eyebrow' => 'Sub-00 // Modul Koordinator Lapangan',
            'title_before' => 'Dashboard Operasional Pasokan & Kesiapan',
            'title_after' => 'Panen',
            'subtitle' => 'Monitoring kesiapan alokasi kuota petani binaan, intake gate timbangan netto, staging cold-chain 4'."\u{00B0}".'C, dan sinkronisasi real-time pemenuhan PO ritel/horeka terverifikasi.',
            'actions' => [
                ['key' => 'export', 'label' => 'Export Rekap (CSV)', 'icon' => 'download', 'variant' => 'ghost'],
                ['key' => 'filter', 'label' => 'Filter Sentra', 'icon' => 'filter', 'variant' => 'ink'],
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
                'label' => 'Pesanan Terverifikasi (Siap Pack)',
                'icon' => 'badge-check',
                'value' => '1.840',
                'unit' => 'KG',
                'unit_tone' => 'text-ink-body',
                'foot_label' => 'Terdiri dari',
                'foot_value' => '28 PO Aktif',
                'chip' => ['label' => '100% Terlock', 'class' => 'bg-accent text-success-ink'],
            ],
            [
                'key' => 'weighing',
                'label' => 'Komoditas Butuh Timbang',
                'icon' => 'package',
                'value' => '5',
                'unit' => 'Komoditas Inti',
                'unit_tone' => 'text-ink-body',
                'foot_label' => 'Intake Masuk:',
                'foot_value' => '12 Batch',
                'chip' => ['label' => '8 Proses / 4 Queue', 'class' => 'bg-surface-pill text-ink'],
            ],
            [
                'key' => 'buffer',
                'label' => 'Kapasitas Buffer Stock Aktif',
                'icon' => 'gauge',
                'value' => '82.4%',
                'unit' => 'Terpakai',
                'unit_tone' => 'text-success-deep',
                'foot_label' => 'Pre-Cool & Chiller A/B',
                'foot_value' => '5.93 / 7.20 TON',
                'chip' => null,
            ],
            [
                'key' => 'shrinkage',
                'label' => 'Deviasi Susut Timbangan Riil',
                'icon' => 'scale',
                'value' => '-1.18%',
                'unit' => 'Shrinkage',
                'unit_tone' => 'text-success-deep',
                'foot_label' => 'Toleransi SOP: Max',
                'foot_value' => '-2.5%',
                'chip' => ['label' => 'In Tolerance', 'class' => 'bg-accent text-success-ink'],
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
            'title' => 'Manajemen Stok Panen & Buffer Stock',
            'subtitle' => '(Kontrol Pasokan)',
            'description' => 'Sistem Penjaga Anti-Overselling: Verifikasi kuota panen binaan sebelum pembukaan alokasi pemenuhan PO.',
            'totals' => [
                ['label' => 'Total Stok Terkontrol', 'value' => '1.920 KG', 'tone' => 'text-ink'],
                ['label' => 'Terkunci PO Aktif', 'value' => '1.840 KG', 'tone' => 'text-success-deep'],
                ['label' => 'Sisa Bebas Buffer', 'value' => '+80 KG', 'tone' => 'text-ink'],
            ],
            'columns' => [
                'Komoditas & Varietas',
                'Order Aktif (PO)',
                'Pasokan Binaan (Subang)',
                'Buffer Stok Luar',
                'Total Tersedia',
                'Status Alokasi & Gap',
                'Aksi Kontrol',
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
            'title' => 'Persiapan Pesanan & Packing Cold-Chain',
            'subtitle' => '3 Batch Staging Sedang Berjalan Pada Bay 01-03',
            'chip' => 'Chiller Target: 4'."\u{00B0}".'C '."\u{00B1}".' 0.5'."\u{00B0}".'C',
            'orders' => [
                [
                    'po' => 'PO-HTL-2201',
                    'client' => 'Hotel Grand Pasundan Ritel (Bandung)',
                    'dispatch' => 'Dispatch: 16:30 WIB',
                    'dispatch_tone' => 'accent',
                    'allocation' => '680 KG (Selada & Tomat)',
                    'bay' => 'Bay-02 (Cold 3.8'."\u{00B0}".'C)',
                    'status' => '85% Sortir Selesai',
                    'percent' => 85,
                ],
                [
                    'po' => 'PO-RES-1049',
                    'client' => 'Restoran Segar Nusantara (Jakarta Hub)',
                    'dispatch' => 'Dispatch: 17:00 WIB',
                    'dispatch_tone' => 'accent',
                    'allocation' => '540 KG (Brokoli & Kol)',
                    'bay' => 'Bay-01 (Cold 4.1'."\u{00B0}".'C)',
                    'status' => '62% Packing Peti',
                    'percent' => 62,
                ],
                [
                    'po' => 'PO-EXP-0082',
                    'client' => 'Sentra Supermarket Eksklusif (Bekasi)',
                    'dispatch' => 'Dispatch: 18:15 WIB',
                    'dispatch_tone' => 'ink',
                    'allocation' => '620 KG (Mix 5 Komoditas)',
                    'bay' => 'Bay-03 (Pre-Cool)',
                    'status' => '35% Pending Intake',
                    'percent' => 35,
                ],
            ],
            'footer' => [
                'label' => 'SOP Monitoring: Thermal Sensor Kalibrasi ISO 17025',
                'link' => 'Buka Kontrol Cold-Storage Hub',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function gate(): array
    {
        return [
            'title' => 'Log Intake Timbangan Gate-01',
            'subtitle' => 'Tiket Timbangan Netto Fisik Real-Time',
            'logs' => [
                [
                    'ticket' => 'GATE-LOG #8821',
                    'time' => '13:58 WIB',
                    'name' => 'Asep Sobana',
                    'commodity' => 'Selada Romaine',
                    'gross' => 495,
                    'tare' => 15,
                    'net' => 480,
                    'qc' => 'QC Pass: Grade A',
                    'note' => 'Brix 4.2 '."\u{2022}".' Water 94%',
                ],
                [
                    'ticket' => 'GATE-LOG #8820',
                    'time' => '13:42 WIB',
                    'name' => 'Dedi Rohimat',
                    'commodity' => 'Tomat Beef',
                    'gross' => 418,
                    'tare' => 18,
                    'net' => 400,
                    'qc' => 'QC Pass: Grade A',
                    'note' => 'Susut -0.9% (Lolos)',
                ],
                [
                    'ticket' => 'GATE-LOG #8819',
                    'time' => '13:15 WIB',
                    'name' => 'Kurniawan',
                    'commodity' => 'Brokoli Highland',
                    'gross' => 366,
                    'tare' => 16,
                    'net' => 350,
                    'qc' => 'QC Pass: Grade A+',
                    'note' => 'Staging Cold Bay-01',
                ],
            ],
            'footer' => [
                'label' => 'Timbangan Digital Metrologi #MET-SUB-01',
                'action' => 'Input Tiket Baru',
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
                ['label' => '3 Batch Staging', 'tone' => 'ink'],
                ['label' => 'Kalibrasi ISO 17025', 'tone' => 'ink'],
            ],
            'right' => 'Terakhir sinkron penuh: 14:00 WIB '."\u{2022}".' Deviasi susut -1.18% masih dalam toleransi SOP.',
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
