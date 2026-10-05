<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data konsol "Rencana Panen & Persiapan Pesanan".
 *
 * PRD Rule 04 & 09: sortir mutu, sanitasi wadah, dan pre-cooling wajib
 * tuntas sebelum batch diteruskan ke meja timbangan netto.
 */
class CoordinatorHarvestPlanData
{
    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => CoordinatorDashboardData::operator($user),
            'mandate' => self::mandate(),
            'kpis' => self::kpis(),
            'queue' => self::queue(),
            'station' => self::station(),
            'qc' => self::qc(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function mandate(): array
    {
        return [
            'title' => 'MANDAT ALUR SORTIR DAN PRE-COOLING SEBELUM DOKUMEN DISPATCH (PRD RULE 04 & 09)',
            'badge' => 'MANDATORY ENFORCED',
            'stages' => [
                [
                    'stage' => 'TAHAP 01',
                    'title' => 'Inspeksi Visual Sortir',
                    'body' => 'Cek ketat kebusukan >2%, eliminasi daun kuning, ulat, telur hama, dan residu lumpur tanah.',
                    'sop' => 'SOP: MAX AFKIR 2.0%',
                    'tag' => 'GRADE A/B FILTER',
                    'icon' => 'eye',
                ],
                [
                    'stage' => 'TAHAP 02',
                    'title' => 'Pembersihan & Trimming',
                    'body' => 'Potong rata pangkal bonggol maksimal 1.5 cm; lap embun basah berlebih pencegahan pembusukan dingin.',
                    'sop' => 'BONGGOL: 1.0 - 1.5 CM',
                    'tag' => 'DRY-WIPED',
                    'icon' => 'leaf',
                ],
                [
                    'stage' => 'TAHAP 03',
                    'title' => 'Krat Cold-Chain Steril',
                    'body' => 'Wadah berlubang standar tara 2.00 kg sanitasi food-grade desinfeksi klorin 50ppm suhu pra-isi +4°C.',
                    'sop' => 'TARA STANDARD: 2.0 KG',
                    'tag' => 'SANITIZED << 5°C',
                    'icon' => 'package',
                ],
                [
                    'stage' => 'TAHAP 04',
                    'title' => 'Timbangan Netto Sah',
                    'body' => 'Penerbitan barcode batch timbang digital terintegrasi ledger dan segel strapping pallet berlapis.',
                    'sop' => 'RFID / BARCODE LOT',
                    'tag' => 'DISPATCH READY',
                    'icon' => 'scale',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function kpis(): array
    {
        return [
            [
                'key' => 'packing',
                'label' => 'TOTAL PO DALAM PACKING',
                'value' => 7,
                'unit' => 'Batch Aktif',
                'unit_tone' => 'body',
                'note' => 'Target Hari Ini: 1.840,00 kg',
                'note_tone' => 'muted',
                'progress' => 70,
                'bar' => 'bg-ink',
                'icon' => 'package',
                'icon_chip' => 'shell',
                'icon_tone' => 'ink',
            ],
            [
                'key' => 'sorted',
                'label' => 'SELESAI SORTIR & PACKING',
                'value' => 5,
                'unit' => 'Selesai (71%)',
                'unit_tone' => 'success',
                'note' => '1.290 kg menuju antrean timbang',
                'note_tone' => 'body',
                'progress' => 71.4,
                'bar' => 'bg-success-deep',
                'icon' => 'badge-check',
                'icon_chip' => 'accent-soft',
                'icon_tone' => 'success-deep',
            ],
            [
                'key' => 'crates',
                'label' => 'KETERSEDIAAN KRAT STERIL',
                'value' => '280',
                'unit' => '/ 350 Krat',
                'unit_tone' => 'body',
                'note' => "Tara @2.0 kg \u{2022} Staging Bay A",
                'note_tone' => 'muted',
                'progress' => 80,
                'bar' => 'bg-accent-edge',
                'icon' => 'package',
                'icon_chip' => 'shell',
                'icon_tone' => 'ink',
            ],
            [
                'key' => 'temperature',
                'label' => 'SUHU RUANG PRE-COOLING',
                'value' => '+3.8°C',
                'unit' => null,
                'chip_label' => 'NORMAL',
                'note' => 'ISO 22000 Standard (+2°C ~ +5°C)',
                'note_tone' => 'muted',
                'progress' => 60,
                'bar' => 'bg-accent',
                'icon' => 'thermometer',
                'icon_chip' => 'accent-soft',
                'icon_tone' => 'ink',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function queue(): array
    {
        return [
            'title' => 'Antrean Batch Packing Berjalan',
            'subtitle' => 'Staging zone meja pembersihan, sortir mutu, dan pelabelan peti',
            'badge' => 'LIVE REFRESH: 5S',
            'columns' => [
                'BATCH & PO ID',
                'BUYER & KOMODITAS',
                'KRAT & BOBOT',
                'STATUS TAHAP',
                'AKSI',
            ],
            'stages' => [
                1 => ['chip' => 'TAHAP 1: TRIMMING', 'tone' => 'pill'],
                2 => ['chip' => 'TAHAP 2: SORTIR & QC', 'tone' => 'pill'],
                3 => ['chip' => 'TAHAP 3: MENUNGGU TIMBANGAN', 'tone' => 'accent'],
                4 => ['chip' => 'SIAP TIMBANG', 'tone' => 'accent-soft'],
            ],
            'rows' => [
                [
                    'po' => 'ORD-GPA-202610-0042',
                    'bay' => 'STG-BAY-A1',
                    'station' => 'MEJA 02',
                    'buyer' => 'Katering Berkah Mandiri',
                    'commodity' => 'Selada Romaine',
                    'commodity_note' => '[Grade A]',
                    'commodity_tone' => 'success',
                    'krat_done' => 40,
                    'krat_total' => 40,
                    'est_kg' => 800.00,
                    'stage' => 3,
                    'stage_tone' => 'accent',
                    'afkir' => null,
                    'action' => 'inspect',
                    'action_label' => 'Inspeksi',
                    'row_tone' => 'active',
                ],
                [
                    'po' => 'ORD-GPA-202610-0039',
                    'bay' => 'STG-BAY-A2',
                    'station' => 'MEJA 01',
                    'buyer' => 'PT Aerofood ACS',
                    'commodity' => 'Tomat Beef & Selada',
                    'commodity_note' => null,
                    'commodity_tone' => 'body',
                    'krat_done' => 28,
                    'krat_total' => 30,
                    'est_kg' => 442.50,
                    'stage' => 2,
                    'stage_tone' => 'pill',
                    'afkir' => 'Afkir 1.2% (Aman)',
                    'action' => 'select',
                    'action_label' => 'Pilih',
                    'row_tone' => 'default',
                ],
                [
                    'po' => 'ORD-GPA-202610-0088',
                    'bay' => 'STG-BAY-B1',
                    'station' => 'MEJA 03',
                    'buyer' => 'Superindo DC Serpong',
                    'commodity' => 'Brokoli & Kol Putih',
                    'commodity_note' => null,
                    'commodity_tone' => 'body',
                    'krat_done' => 0,
                    'krat_total' => 24,
                    'est_kg' => 347.50,
                    'stage' => 1,
                    'stage_tone' => 'pill',
                    'afkir' => null,
                    'action' => 'select',
                    'action_label' => 'Pilih',
                    'row_tone' => 'default',
                ],
                [
                    'po' => 'ORD-GPA-202610-0091',
                    'bay' => 'STG-BAY-B2',
                    'station' => 'MEJA 04',
                    'buyer' => 'Pasar Segar Modern BSD',
                    'commodity' => 'Bayam & Kangkung',
                    'commodity_note' => null,
                    'commodity_tone' => 'body',
                    'krat_done' => 18,
                    'krat_total' => 18,
                    'est_kg' => 250.00,
                    'stage' => 4,
                    'stage_tone' => 'accent-soft',
                    'afkir' => null,
                    'action' => 'select',
                    'action_label' => 'Pilih',
                    'row_tone' => 'default',
                ],
            ],
            'footer_left' => 'MEMPERLIHATKAN 4 DARI 7 BATCH OPERASIONAL',
            'footer_right' => 'STANDARD PACKING OPERATIONAL SPEED: 18 KRAT / JAM',
            'total_batches' => 7,
            'done_batches' => 5,
            'weigh_kg' => 1290.00,
            'weight_target' => 1840.00,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function station(): array
    {
        return [
            'eyebrow' => 'DETAIL WORKSTATION AKTIF',
            'tab_prefix' => 'WORKSTATION',
            'barcode_prefix' => 'KRAT-GPA-SBG-04-2026',
            'pallet_tier' => '20 Krat (Tier 4x5)',
            'steps' => [
                [
                    'title' => '1. Label Krat Terpasang (Barcode/RFID)',
                    'icon' => 'qrcode',
                    'chip' => 'TERVALIDASI',
                    'prefix_label' => 'Prefix:',
                    'notes' => ["Warna: Kuning Cold-Chain (Food Grade) \u{2022} Tara: 2.00 kg / Peti"],
                ],
                [
                    'title' => '2. Kebersihan Wadah (HACCP Food-Grade)',
                    'icon' => 'shield',
                    'chip' => 'LOLOS',
                    'checks' => ['Bebas residu jamur/tanah', 'Disinfektan klorin 50ppm'],
                    'notes' => ['Suhu peti sebelum diisi: +4.1°C (\u{2264} 5.0°C Ambang Dingin)'],
                ],
                [
                    'title' => '3. Pembagian Lot & Konfigurasi Pallet',
                    'icon' => 'package',
                    'chip' => 'TERPASANG',
                    'pallets' => true,
                    'notes' => ['Strapping pita hitam 2 titik & cover termal foil terpasang rapi'],
                ],
            ],
            'qc_note' => [
                'label' => 'CATATAN KHUSUS PENGAWAS QC:',
                'text' => '"Pangkal daun dipotong rata 1.0 cm, kondisi selada renyah, embun permukaan telah dilap bersih, siap ditimbang netto resmi."',
            ],
            'buttons' => [
                ['key' => 'print-label', 'label' => 'Cetak Label Krat', 'icon' => 'printer', 'variant' => 'outline'],
                ['key' => 'flag-afkir', 'label' => 'Tandai Afkir Tinggi', 'icon' => 'alert-triangle', 'variant' => 'warning'],
            ],
            'cta' => ['key' => 'transfer', 'label' => 'TRANSFER KE MEJA TIMBANGAN NETTO >>', 'icon' => 'scale'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function qc(): array
    {
        return [
            'title' => 'Log Uji Petik Mutu & Sortir Afkir Meja Grading',
            'subtitle' => 'Sampel acak 5 krat (12.5% sampling) dari total 40 krat PO #ORD-GPA-202610-0042',
            'avg_label' => 'RATA-RATA SUSUT TRIMMING',
            'avg_value' => '0.89%',
            'avg_note' => '(Toleransi SOP: Max 2.00%)',
            'inspector' => 'Inspektor: Agung W.',
            'sensor_note' => 'Sensor Calibrated',
            'tolerance' => 2.00,
            'samples' => [
                [
                    'krat' => 'KRAT SAMPEL #04',
                    'status' => 'PASS QC',
                    'gross' => 22.12,
                    'tara' => 2.00,
                    'trim' => 0.18,
                    'trim_percent' => 0.89,
                    'net' => 19.94,
                ],
                [
                    'krat' => 'KRAT SAMPEL #18',
                    'status' => 'PASS QC',
                    'gross' => 22.05,
                    'tara' => 2.00,
                    'trim' => 0.22,
                    'trim_percent' => 1.09,
                    'net' => 19.83,
                ],
                [
                    'krat' => 'KRAT SAMPEL #33',
                    'status' => 'PASS QC',
                    'gross' => 22.18,
                    'tara' => 2.00,
                    'trim' => 0.14,
                    'trim_percent' => 0.69,
                    'net' => 20.04,
                ],
            ],
        ];
    }
}
