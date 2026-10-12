<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data konsol "Manajemen Stok Panen & Alokasi Buffer Stock Gudang".
 *
 * Menjaga furnish anti-overselling: alokasi bindersiana terikat, buffer mitra
 * siaga, serta catatan intake lapangan.
 */
class CoordinatorStockData
{
    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => CoordinatorDashboardData::operator($user),
            'header' => self::header(),
            'protocol' => self::protocol(),
            'kpis' => self::kpis(),
            'filters' => self::filters(),
            'commodities' => self::commodities(),
            'intake' => self::intake(),
            'logs' => self::logs(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function header(): array
    {
        return [
            'eyebrow' => 'Sub-06 // Manajemen Stok',
            'title_before' => 'Manajemen Stok Panen & Alokasi',
            'title_after' => 'Buffer Stock Gudang',
            'subtitle' => 'Monitoring ketersediaan pasokan panen petani binaan dan pasokan penyangga (buffer stock) mitra luar untuk pencegahan overselling (PRD Section 5.1, 7.3, Rule 02 & 03).',
            'actions' => [
                ['key' => 'export', 'label' => 'EXPORT LEDGER .CSV', 'icon' => 'download', 'variant' => 'ghost'],
                ['key' => 'lock-all', 'label' => 'KUNCI ALOKASI PASOKAN', 'icon' => 'lock', 'variant' => 'ink'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function protocol(): array
    {
        return [
            'title' => 'PROTOKOL PENJAGA MUTU & INTEGRITAS STOK (PRD SECTION 7.3 & 8.2)',
            'badge' => 'HARD-LOCK ENABLED',
            'body' => 'Setiap PO yang diterbitkan otomatis memotong kuota komoditas secara real-time. Komitmen stok binaan terkunci 100%. Apabila alokasi binaan kurang dari permintaan PO, sistem mewajibkan injeksi buffer mitra tervalidasi dengan selisih susut bobot toleransi maks ≤ 2.5%.',
            'validation_label' => 'VALIDASI INTEGRITAS',
            'validation_value' => '0 ANOMALI OVERSELL',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function kpis(): array
    {
        return [
            [
                'key' => 'available',
                'label' => 'TOTAL STOK TERSEDIA',
                'value' => 14850,
                'unit' => 'KG',
                'tone' => 'ink',
                'icon' => 'package',
                'icon_tone' => 'shell',
                'footnote_left' => 'Binaan: 10.200 KG (68.7%)',
                'footnote_right' => 'Buffer: 4.650 KG',
                'bar_total' => 14850,
                'segments' => [
                    ['value' => 10200, 'class' => 'bg-success-deep'],
                    ['value' => 4650, 'class' => 'bg-brand'],
                ],
            ],
            [
                'key' => 'reserved',
                'label' => 'STOK TERPESAN / PO',
                'value' => 8900,
                'unit' => 'KG',
                'tone' => 'ink',
                'icon' => 'file-text',
                'icon_tone' => 'shell',
                'footnote_left' => '5 Kontrak B2B Hotel/Resto',
                'footnote_right' => '100% TERIKAT',
                'footnote_chip' => true,
                'bar_total' => 14850,
                'segments' => [['value' => 8900, 'class' => 'bg-ink']],
            ],
            [
                'key' => 'free',
                'label' => 'SISA BEBAS (FREE STOCK)',
                'value' => 5950,
                'unit' => 'KG',
                'tone' => 'success',
                'icon' => 'gauge',
                'icon_tone' => 'accent',
                'footnote_left' => 'Ready to Promise (RTP)',
                'footnote_right' => 'AMAN DARI DEFISIT',
                'bar_total' => 14850,
                'segments' => [['value' => 5950, 'class' => 'bg-accent-edge']],
            ],
            [
                'key' => 'hubs',
                'label' => 'HUB PASOKAN AKTIF',
                'value' => 3,
                'unit' => 'LOKASI SENTRA',
                'tone' => 'ink',
                'icon' => 'truck',
                'icon_tone' => 'shell',
                'footnote_left' => 'Cianjur, Lembang, Pangalengan',
                'footnote_right' => '100% ONLINE',
                'segments' => [
                    ['value' => 1, 'class' => 'bg-success-deep'],
                    ['value' => 1, 'class' => 'bg-success-deep'],
                    ['value' => 1, 'class' => 'bg-success-deep'],
                ],
                'segment_mode' => 'equal',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function filters(): array
    {
        return [
            'label' => 'FILTER KOMODITAS:',
            'options' => [
                ['key' => 'all', 'label' => 'Semua (5)'],
                ['key' => 'daun', 'label' => 'Sayuran Daun'],
                ['key' => 'buah', 'label' => 'Buah & Solanaceae'],
                ['key' => 'cruciferae', 'label' => 'Cruciferae'],
            ],
            'legend' => [
                ['label' => 'Binaan Terikat', 'class' => 'bg-success-deep'],
                ['label' => 'Buffer Siaga', 'class' => 'bg-brand'],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function commodities(): array
    {
        return [
            [
                'key' => 'selada-romaine',
                'name' => 'Selada Romaine Highland',
                'grade' => 'GRADE A+ SUPER',
                'grade_tone' => 'accent',
                'code' => 'CMD-ROM-092',
                'hub' => 'CIANJUR PACET & LEMBANG',
                'icon' => 'leaf',
                'icon_tone' => 'success',
                'total' => 4000,
                'rtp' => 1400,
                'rtp_tone' => 'success',
                'rtp_note' => null,
                'category' => 'daun',
                'tiles' => [
                    ['label' => 'PASOKAN BINAAN INTI', 'value' => 2800, 'unit' => 'KG', 'note' => 'Kelompok Tani Pacet Makmur', 'tone' => 'ink', 'note_tone' => 'success'],
                    ['label' => 'BUFFER MITRA SIAGA', 'value' => 1200, 'unit' => 'KG', 'note' => 'Mitra Tani Sejahtera (Lembang)', 'tone' => 'ink', 'note_tone' => 'muted'],
                    ['label' => 'ALOKASI TERIKAT PO', 'value' => 2600, 'unit' => 'KG', 'note' => 'PO-B2B-8931 & PO-B2B-8934', 'tone' => 'danger', 'note_tone' => 'ink'],
                    ['label' => 'KONDISI FISIK / MUTU', 'value' => 'BRIX 4.2° | RH 92%', 'unit' => null, 'note' => 'Toleransi Susut: 1.1%', 'tone' => 'success', 'note_tone' => 'ink'],
                ],
                'utilization' => 65,
                'utilization_note' => '65% Terjadwal',
                'utilization_status' => 'Aman',
                'utilization_status_tone' => 'ink',
                'utilization_tone' => 'success',
                'buffer_need' => 0,
                'injection' => false,
            ],
            [
                'key' => 'tomat-beef',
                'name' => 'Tomat Beef Super Grade',
                'grade' => 'GRADE A (180G+)',
                'grade_tone' => 'pill',
                'code' => 'CMD-TMT-104',
                'hub' => 'PANGALENGAN GREENHOUSE',
                'icon' => 'package',
                'icon_tone' => 'danger',
                'total' => 4300,
                'rtp' => 1800,
                'rtp_tone' => 'success',
                'rtp_note' => null,
                'category' => 'buah',
                'tiles' => [
                    ['label' => 'PASOKAN BINAAN INTI', 'value' => 3100, 'unit' => 'KG', 'note' => 'Gapoktan Barokah Pangalengan', 'tone' => 'ink', 'note_tone' => 'success'],
                    ['label' => 'BUFFER MITRA SIAGA', 'value' => 1200, 'unit' => 'KG', 'note' => 'Mitra Agro Ciwidey', 'tone' => 'ink', 'note_tone' => 'muted'],
                    ['label' => 'ALOKASI TERIKAT PO', 'value' => 2500, 'unit' => 'KG', 'note' => 'PO-B2B-8930 & Superindo', 'tone' => 'danger', 'note_tone' => 'ink'],
                    ['label' => 'KONDISI FISIK / MUTU', 'value' => 'HARDNESS 8.5 | MATURE 85%', 'unit' => null, 'note' => 'Toleransi Susut: 0.8%', 'tone' => 'success', 'note_tone' => 'ink'],
                ],
                'utilization' => 58,
                'utilization_note' => '58% Terjadwal',
                'utilization_status' => 'Aman',
                'utilization_status_tone' => 'ink',
                'utilization_tone' => 'success',
                'buffer_need' => 0,
                'injection' => false,
            ],
            [
                'key' => 'brokoli-highland',
                'name' => 'Brokoli Highland Fresh Cut',
                'grade' => 'GRADE A (KUNTUM RAPAT)',
                'grade_tone' => 'accent',
                'code' => 'CMD-BRK-044',
                'hub' => 'LEMBANG CIKOLE',
                'icon' => 'package',
                'icon_tone' => 'success',
                'total' => 1500,
                'rtp' => 300,
                'rtp_tone' => 'danger',
                'rtp_note' => '(KETAT)',
                'category' => 'cruciferae',
                'tiles' => [
                    ['label' => 'PASOKAN BINAAN INTI', 'value' => 950, 'unit' => 'KG', 'note' => 'KT Cibodas Hijau', 'tone' => 'ink', 'note_tone' => 'success'],
                    ['label' => 'BUFFER MITRA SIAGA', 'value' => 550, 'unit' => 'KG', 'note' => 'Mitra Sayur Parongpong', 'tone' => 'ink', 'note_tone' => 'muted'],
                    ['label' => 'ALOKASI TERIKAT PO', 'value' => 1200, 'unit' => 'KG', 'note' => 'PO-B2B-8932 (Restoran Boga)', 'tone' => 'danger', 'note_tone' => 'ink'],
                    ['label' => 'KONDISI FISIK / MUTU', 'value' => 'SUHU 4°C | COLD CHAIN', 'unit' => null, 'note' => 'Toleransi Susut: 1.5%', 'tone' => 'success', 'note_tone' => 'ink'],
                ],
                'utilization' => 80,
                'utilization_note' => '80% Terikat (Peringatan Batas)',
                'utilization_status' => 'Perlu Buffer +300kg',
                'utilization_status_tone' => 'warning',
                'utilization_tone' => 'brand',
                'buffer_need' => 300,
                'injection' => true,
            ],
            [
                'key' => 'stroberi-ciwidey',
                'name' => 'Stroberi Ciwidey Premium Sweet',
                'grade' => 'GRADE AA (BRIX 10+)',
                'grade_tone' => 'accent',
                'code' => 'CMD-STR-018',
                'hub' => 'CIWIDEY PATENGGANG',
                'icon' => 'package',
                'icon_tone' => 'danger',
                'total' => 450,
                'rtp' => 100,
                'rtp_tone' => 'success',
                'rtp_note' => null,
                'category' => 'buah',
                'tiles' => [
                    ['label' => 'PASOKAN BINAAN INTI', 'value' => 300, 'unit' => 'KG', 'note' => 'KT Alam Endah Ciwidey', 'tone' => 'ink', 'note_tone' => 'success'],
                    ['label' => 'BUFFER MITRA SIAGA', 'value' => 150, 'unit' => 'KG', 'note' => 'Mitra Petik Strawberry', 'tone' => 'ink', 'note_tone' => 'muted'],
                    ['label' => 'ALOKASI TERIKAT PO', 'value' => 350, 'unit' => 'KG', 'note' => 'PO-B2B-8935 (Pastry Hub)', 'tone' => 'danger', 'note_tone' => 'ink'],
                    ['label' => 'KONDISI FISIK / MUTU', 'value' => 'BRIX 10.8° | PUNNET 250G', 'unit' => null, 'note' => 'Toleransi Susut: 0.5%', 'tone' => 'success', 'note_tone' => 'ink'],
                ],
                'utilization' => 77,
                'utilization_note' => '77% Terikat',
                'utilization_status' => 'Normal',
                'utilization_status_tone' => 'ink',
                'utilization_tone' => 'success',
                'buffer_need' => 0,
                'injection' => false,
            ],
            [
                'key' => 'kol-putih',
                'name' => 'Kol Putih Padat Lembang',
                'grade' => 'GRADE A (1.2 - 2.0 KG/HEAD)',
                'grade_tone' => 'accent',
                'code' => 'CMD-KOL-055',
                'hub' => 'LEMBANG & SUKABUMI TIMUR',
                'icon' => 'package',
                'icon_tone' => 'success',
                'total' => 4600,
                'rtp' => 2350,
                'rtp_tone' => 'success',
                'rtp_note' => null,
                'category' => 'cruciferae',
                'tiles' => [
                    ['label' => 'PASOKAN BINAAN INTI', 'value' => 3050, 'unit' => 'KG', 'note' => 'KT Wargi Saluyu Lembang', 'tone' => 'ink', 'note_tone' => 'success'],
                    ['label' => 'BUFFER MITRA SIAGA', 'value' => 1550, 'unit' => 'KG', 'note' => 'Mitra Tani Sukabumi Inti', 'tone' => 'ink', 'note_tone' => 'muted'],
                    ['label' => 'ALOKASI TERIKAT PO', 'value' => 2250, 'unit' => 'KG', 'note' => 'PO-B2B-8933 & Supermarket', 'tone' => 'danger', 'note_tone' => 'ink'],
                    ['label' => 'KONDISI FISIK / MUTU', 'value' => 'KEPADATAN TINGGI | TRIMMING 98%', 'unit' => null, 'note' => 'Toleransi Susut: 1.8%', 'tone' => 'success', 'note_tone' => 'ink'],
                ],
                'utilization' => 48.9,
                'utilization_note' => '48.9% Terjadwal',
                'utilization_status' => 'Sangat Aman',
                'utilization_status_tone' => 'ink',
                'utilization_tone' => 'success',
                'buffer_need' => 0,
                'injection' => false,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function intake(): array
    {
        return [
            'title' => 'Quick Intake Ledger',
            'subtitle' => 'PENCATATAN PANEN MASUK LAPANGAN',
            'badge' => 'POS-01 AUTO-CAPTURE',
            'fields' => [
                [
                    'key' => 'vendor',
                    'label' => 'PETANI BINAAN / MITRA VENDOR *',
                    'type' => 'select',
                    'value' => 'Pak Endang',
                    'hint' => 'Gapoktan Pacet Makmur - Binaan',
                    'options' => [
                        ['key' => 'Pak Endang', 'label' => 'Pak Endang (Gapoktan Pacet Makmur - Binaan)'],
                        ['key' => 'Bu Yanti', 'label' => 'Bu Yanti (Gapoktan Barokah Pangalengan)'],
                        ['key' => 'Pak Wargi', 'label' => 'Pak Wargi (KT Wargi Saluyu Lembang)'],
                        ['key' => 'Mitra Tani Sejahtera', 'label' => 'Mitra Tani Sejahtera (Lembang - Buffer)'],
                    ],
                ],
                [
                    'key' => 'commodity',
                    'label' => 'KOMODITAS INTI *',
                    'type' => 'select',
                    'value' => 'selada-romaine',
                    'options' => [
                        ['key' => 'selada-romaine', 'label' => 'Selada Romaine Highland (CMD-ROM-092)'],
                        ['key' => 'tomat-beef', 'label' => 'Tomat Beef Super Grade (CMD-TMT-104)'],
                        ['key' => 'brokoli-highland', 'label' => 'Brokoli Highland Fresh Cut (CMD-BRK-044)'],
                        ['key' => 'stroberi-ciwidey', 'label' => 'Stroberi Ciwidey Premium Sweet (CMD-STR-018)'],
                        ['key' => 'kol-putih', 'label' => 'Kol Putih Padat Lembang (CMD-KOL-055)'],
                    ],
                ],
                [
                    'key' => 'grade',
                    'label' => 'GRADE MUTU',
                    'type' => 'select',
                    'value' => 'Grade A (Premium)',
                    'options' => [
                        ['key' => 'Grade A+ (Super)', 'label' => 'Grade A+ (Super)'],
                        ['key' => 'Grade A (Premium)', 'label' => 'Grade A (Premium)'],
                        ['key' => 'Grade AA (Wajib Panas)', 'label' => 'Grade AA (Wajib Panas)'],
                        ['key' => 'Grade B (Sortir)', 'label' => 'Grade B (Sortir)'],
                    ],
                ],
                [
                    'key' => 'category',
                    'label' => 'KATEGORI PASOKAN',
                    'type' => 'select',
                    'value' => 'Panen Binaan Inti',
                    'options' => [
                        ['key' => 'Panen Binaan Inti', 'label' => 'Panen Binaan Inti'],
                        ['key' => 'Injeksi Buffer Mitra', 'label' => 'Injeksi Buffer Mitra'],
                        ['key' => 'Retur Sortir', 'label' => 'Retur Sortir'],
                    ],
                ],
                ['key' => 'quantity', 'label' => 'KUANTITAS (NETTO)', 'type' => 'number', 'value' => 450, 'unit' => 'KG'],
                ['key' => 'date', 'label' => 'TANGGAL TEBAS/PETIK', 'type' => 'date', 'value' => '2025-05-18'],
                ['key' => 'notes', 'label' => 'BRIX / MOISTURE / QC NOTES', 'type' => 'text', 'value' => 'Brix 4.5°, Sortir manual 100%, peti kemas plastik #04'],
            ],
            'port_label' => 'Port Timbangan: COM-4 (Netto Stabil)',
            'port_status' => 'CONNECTED',
            'submit' => 'SIMPAN DATA PASOKAN',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function logs(): array
    {
        return [
            'title' => 'Log Inbound & Realtime',
            'badge' => 'LIVE FEED',
            'total' => 48,
            'items' => [
                [
                    'lot' => 'LOT-ROM-20250518-09',
                    'time' => '09:38:12 WIB',
                    'entry' => '+450 KG Selada Romaine (Binaan - Endang)',
                    'note' => 'QC Pass: BRIX 4.3°',
                    'audit' => 'AUDIT: SEC-OP-882',
                    'tone' => 'success',
                ],
                [
                    'lot' => 'BUF-TMT-20250518-04',
                    'time' => '09:14:02 WIB',
                    'entry' => '+800 KG Tomat Beef (Buffer Mitra Sejahtera)',
                    'note' => 'Alokasi PO-B2B-8930',
                    'audit' => 'AUDIT: SEC-OP-882',
                    'tone' => 'brand',
                ],
                [
                    'lot' => 'LOT-KOL-20250518-02',
                    'time' => '08:45:51 WIB',
                    'entry' => '+1.200 KG Kol Putih (Binaan - Wargi Saluyu)',
                    'note' => 'Grade A • Bersih Trimming',
                    'audit' => 'AUDIT: SEC-SYS-AUTO',
                    'tone' => 'success',
                ],
            ],
        ];
    }
}
