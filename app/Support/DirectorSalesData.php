<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data "Penjualan // Analisis Revenue Eksekutif".
 *
 * PRD Rule 11 & 15: seluruh cash inflow wajib direkonsiliasi terhadap metode
 * pembayaran yang tercatat di gateway pembayaran, sedangkan neraca laba kotor
 * per komoditas mengikuti aturan portofolio pada PRD Section 20. PRD Metrologi
 * Legal UU No. 2/1981 menjadi rujukan pembuktian tera timbangan untuk setiap
 * baris volume yang ditagih ke kanal.
 *
 * Seluruh angka diturunkan dari satu nilai omzet konsolidasi sehingga pita KPI,
 * grafik mingguan, komposisi kanal, tabel portofolio, dan rekapitulasi metode
 * pembayaran selalu saling cocok.
 */
class DirectorSalesData
{
    /**
     * Omzet konsolidasi MTD Oktober 2026 yang menjadi acuan seluruh modul.
     */
    public const REVENUE = 482_650_000;

    /**
     * Laba kotor konsolidasi = 21.8% x omzet MTD.
     */
    public const GROSS = 105_217_700;

    /**
     * Laba minimum per komoditas yang wajib dijaga oleh Direksi.
     */
    public const MARGIN_FLOOR = 15.0;

    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => DirectorDashboardData::operator($user),
            'header' => self::header(),
            'scopes' => self::kpiScopes(),
            'trend' => self::weeklyTrend(),
            'channels' => self::channels(),
            'portfolio' => self::portfolio(),
            'settlement' => self::settlement(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function header(): array
    {
        return [
            'eyebrow' => 'Monitoring Penjualan & Analisis Revenue Eksekutif',
            'title' => 'Analisis Revenue Eksekutif',
            'subtitle' => 'Pertumbuhan Finansial Makro.',
            'export_label' => 'Unduh Ringkasan XLSX',
            'print_label' => 'Cetak Rekapitulasi PDF',
        ];
    }

    /**
     * Pita KPI menurut cakupan periode: MTD, WTD, dan YTD.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function kpiScopes(): array
    {
        $scopes = [
            'MTD' => [
                'key' => 'MTD',
                'label' => 'MTD',
                'period' => 'MTD / Q4 2026',
                'tab' => 'MTD',
                'hint' => 'Basis data 1 - 28 Oktober 2026 (28 hari buku).',
                'badge' => 'MoM',
                'trend_badge' => '+6.8%',
                'revenue' => 482_650_000,
                'target' => 600_000_000,
                'margin' => 21.8,
                'gross' => 105_217_700,
                'settled' => 98.6,
                'billing' => 556_970_000,
                'outstanding' => 74_320_000,
                'aov' => 3_398_940,
                'volume_kg' => 26_178,
                'days' => 28,
            ],
            'WTD' => [
                'key' => 'WTD',
                'label' => 'WTD',
                'period' => 'WTD / W44 2026',
                'tab' => 'WTD (Mingguan)',
                'hint' => 'Basis data Senin 26 - Kamis 29 Oktober 2026 (4 hari).',
                'badge' => 'WoW',
                'trend_badge' => '+2.4%',
                'revenue' => 97_750_000,
                'target' => 120_000_000,
                'margin' => 21.2,
                'gross' => 20_723_000,
                'settled' => 98.6,
                'billing' => 112_650_000,
                'outstanding' => 12_000_000,
                'aov' => 3_120_000,
                'volume_kg' => 5_296,
                'days' => 4,
            ],
            'YTD' => [
                'key' => 'YTD',
                'label' => 'YTD',
                'period' => 'YTD / Q4 2026',
                'tab' => 'YTD Konsolidasi',
                'hint' => 'Basis data 1 Januari - 28 Oktober 2026 (300 hari buku).',
                'badge' => 'YoY',
                'trend_badge' => '+11.6%',
                'revenue' => 3_412_900_000,
                'target' => 4_000_000_000,
                'margin' => 20.9,
                'gross' => 713_296_100,
                'settled' => 97.9,
                'billing' => 3_690_000_000,
                'outstanding' => 210_400_000,
                'aov' => 3_265_400,
                'volume_kg' => 178_640,
                'days' => 300,
            ],
        ];

        foreach ($scopes as $key => $scope) {
            $scopes[$key]['cards'] = self::scopeCards($scope);
        }

        return $scopes;
    }

    /**
     * Empat kartu KPI untuk satu cakupan periode.
     *
     * @param  array<string, mixed>  $scope
     * @return array<int, array<string, mixed>>
     */
    private static function scopeCards(array $scope): array
    {
        $targetPercent = round($scope['revenue'] / $scope['target'] * 100, 1);
        $paidPercent = round($scope['revenue'] / $scope['billing'] * 100, 1);

        return [
            [
                'key' => 'revenue',
                'label' => $scope['label'],
                'badge' => $scope['badge'],
                'badge_tone' => 'accent',
                'value' => self::rupiah($scope['revenue']),
                'notes' => [
                    ['text' => number_format($scope['target'], 0, '.', '.'), 'tone' => 'muted'],
                    ['text' => self::percent($targetPercent, 1).'%', 'tone' => 'ink'],
                ],
                'progress' => [
                    'label' => 'Realisasi omzet terhadap target periode',
                    'percent' => $targetPercent,
                    'fill' => 'bg-ink-strong',
                ],
                'footer' => [
                    ['text' => 'Sisa Rp '.number_format($scope['target'] - $scope['revenue'], 0, '.', '.'), 'tone' => 'caution'],
                    ['text' => $scope['days'].' Hari', 'tone' => 'muted'],
                ],
            ],
            [
                'key' => 'margin',
                'label' => 'RATA-RATA',
                'badge' => $scope['trend_badge'],
                'badge_tone' => 'accent',
                'value' => self::percent($scope['margin'], 1).'%',
                'notes' => [
                    ['text' => 'Min 15.0%', 'tone' => 'muted'],
                    ['text' => 'Laba', 'tone' => 'ink'],
                ],
                'progress' => [
                    'label' => 'Laba kotor terhadap plafon margin 30%',
                    'percent' => round($scope['margin'] / 30 * 100, 1),
                    'fill' => 'bg-success',
                ],
                'footer' => [
                    ['text' => 'Kotor:', 'tone' => 'muted'],
                    ['text' => 'Rp '.number_format($scope['gross'], 0, '.', '.'), 'tone' => 'ink'],
                ],
            ],
            [
                'key' => 'settled',
                'label' => '(SETTLED)',
                'badge' => self::percent($scope['settled'], 1).'%',
                'badge_tone' => 'neutral',
                'value' => 'Terpenuhi',
                'notes' => [
                    ['text' => '(AOV):', 'tone' => 'muted'],
                    ['text' => number_format($scope['aov'], 0, '.', '.'), 'tone' => 'ink'],
                ],
                'progress' => [
                    'label' => 'Tingkat penyelesaian tagihan',
                    'percent' => $scope['settled'],
                    'fill' => 'bg-ink-strong',
                ],
                'footer' => [
                    ['text' => 'Fisik:', 'tone' => 'muted'],
                    ['text' => number_format($scope['volume_kg'], 0, '.', '.').' kg', 'tone' => 'ink'],
                ],
            ],
            [
                'key' => 'billing',
                'label' => 'TERBAYAR',
                'badge' => 'SEHAT',
                'badge_tone' => 'accent',
                'value' => 'Masuk',
                'notes' => [
                    ['text' => 'Tagihan:', 'tone' => 'muted'],
                    ['text' => number_format($scope['billing'], 0, '.', '.'), 'tone' => 'ink'],
                ],
                'progress' => [
                    'label' => 'Cash inflow masuk terhadap total tagihan',
                    'percent' => $paidPercent,
                    'fill' => 'bg-ink-strong',
                ],
                'footer' => [
                    ['text' => 'Piutang (TOP):', 'tone' => 'muted'],
                    ['text' => number_format($scope['outstanding'], 0, '.', '.'), 'tone' => 'ink'],
                ],
            ],
        ];
    }

    /**
     * Grafik batang realisasi kas masuk mingguan beserta ceiling dan BEP.
     *
     * @return array<string, mixed>
     */
    public static function weeklyTrend(): array
    {
        $rows = [
            ['week' => 'W40', 'value' => 88_400_000, 'label' => 'Rp 88.4M', 'status' => 'closed'],
            ['week' => 'W41', 'value' => 92_100_000, 'label' => 'Rp 92.1M', 'status' => 'closed'],
            ['week' => 'W42', 'value' => 97_600_000, 'label' => 'Rp 97.6M', 'status' => 'closed'],
            ['week' => 'W43', 'value' => 106_800_000, 'label' => 'Rp 106.8M', 'status' => 'closed'],
            ['week' => 'W44', 'value' => 97_750_000, 'label' => 'Rp 97.7M*', 'status' => 'running'],
        ];

        $ceiling = 120_000_000;
        $bep = 65_000_000;
        $peak = max(array_column($rows, 'value'));

        foreach ($rows as $index => $row) {
            $rows[$index]['value_label'] = self::rupiah($row['value']);
            $rows[$index]['share'] = round($row['value'] / $ceiling * 100, 1);
            $rows[$index]['share_label'] = self::percent($row['value'] / $ceiling * 100, 1).'% dari ceiling';
            $rows[$index]['is_peak'] = $row['value'] === $peak;
            $rows[$index]['fill'] = match (true) {
                $row['status'] === 'running' => 'bg-accent-deep',
                $row['value'] === $peak => 'bg-success',
                default => 'bg-ink-strong',
            };
        }

        return [
            'title' => 'Direksi (W40 - W44)',
            'subtitle' => 'Realisasi kas masuk vs Ceiling Mingguan & Baseline BEP per Pekan (WIB Hari Ini)',
            'legend' => [
                ['label' => 'Realisasi', 'swatch' => 'bg-ink-strong'],
                ['label' => 'Ceiling', 'swatch' => 'bg-accent-deep'],
                ['label' => 'BEP', 'swatch' => 'bg-warning-caution'],
            ],
            'rows' => $rows,
            'ceiling' => $ceiling,
            'ceiling_label' => 'Target Cap: Rp 120M / Mgg',
            'bep' => $bep,
            'bep_label' => 'BEP Baseline: Rp 65M',
            'bep_percent' => round($bep / $ceiling * 100, 1),
            'peak_label' => 'Puncak Realisasi: W43 (Rp 106.8M)',
            'footer' => 'Bar W44 masih berjalan dan ditandai garis putus-putus; baseline BEP dihitung per pekan (WIB Hari Ini).',
            'total' => array_sum(array_column($rows, 'value')),
        ];
    }

    /**
     * Komposisi revenue per kanal penjualan.
     *
     * @return array<string, mixed>
     */
    public static function channels(): array
    {
        $rows = [
            ['name' => 'Kontrak B2B Horeca & Inflight', 'value' => 328_202_000, 'dot' => 'bg-ink-strong'],
            ['name' => 'Retail Modern & Supermarket', 'value' => 106_183_000, 'dot' => 'bg-success'],
            ['name' => 'Pesanan Reguler & WhatsApp Sekre', 'value' => 48_265_000, 'dot' => 'bg-accent-deep'],
        ];

        $total = array_sum(array_column($rows, 'value'));

        foreach ($rows as $index => $row) {
            $rows[$index]['value_label'] = self::rupiah($row['value']);
            $rows[$index]['share'] = (int) round($row['value'] / $total * 100, 0);
            $rows[$index]['share_label'] = $rows[$index]['share'].'%';
        }

        return [
            'badge' => 'REKONSILIASI',
            'title' => 'Komposisi Revenue Saluran',
            'subtitle' => 'Pasokan Reguler.',
            'rows' => $rows,
            'total' => $total,
            'total_label' => 'Konsolidasi 3 Kanal: '.self::rupiah($total),
            'total_share' => array_sum(array_column($rows, 'share')),
        ];
    }

    /**
     * Neraca laba kotor lima komoditas inti GPA.
     *
     * @return array<string, mixed>
     */
    public static function portfolio(): array
    {
        $rows = [
            ['name' => 'Selada Romaine', 'sku' => 'RMN01', 'list_price' => 26_500, 'price' => 20_193, 'revenue' => 170_130_000, 'gross' => 40_490_940],
            ['name' => 'Brokoli Super', 'sku' => 'BRK02', 'list_price' => 31_000, 'price' => 23_498, 'revenue' => 119_350_000, 'gross' => 28_882_700],
            ['name' => 'Garut', 'sku' => 'TMT03', 'list_price' => 18_500, 'price' => 14_800, 'revenue' => 94_350_000, 'gross' => 18_851_900],
            ['name' => 'Ciwidey', 'sku' => 'STR04', 'list_price' => 52_000, 'price' => 43_524, 'revenue' => 63_440_000, 'gross' => 10_340_720],
            ['name' => 'Lembang', 'sku' => 'KOL05', 'list_price' => 9_000, 'price' => 7_308, 'revenue' => 35_380_000, 'gross' => 6_651_440],
        ];

        $revenueTotal = array_sum(array_column($rows, 'revenue'));
        $volumeTotal = 0;

        foreach ($rows as $index => $row) {
            $volume = (int) round($row['revenue'] / $row['price']);
            $volumeTotal += $volume;

            $margin = $row['gross'] / $row['revenue'] * 100;
            $share = $row['revenue'] / $revenueTotal * 100;
            $status = self::portfolioStatus($margin);

            $rows[$index]['volume'] = $volume;
            $rows[$index]['volume_label'] = number_format($volume, 0, '.', '.');
            $rows[$index]['list_price_label'] = self::rupiah($row['list_price']);
            $rows[$index]['price_label'] = self::rupiah($row['price']);
            $rows[$index]['revenue_label'] = self::rupiah($row['revenue']);
            $rows[$index]['gross_label'] = self::rupiah($row['gross']);
            $rows[$index]['margin'] = round($margin, 1);
            $rows[$index]['margin_label'] = self::percent($margin, 1).'%';
            $rows[$index]['share'] = round($share, 1);
            $rows[$index]['share_label'] = self::percent($share, 1).'%';
            $rows[$index]['status'] = $status['label'];
            $rows[$index]['status_tone'] = $status['tone'];
            $rows[$index]['bar_percent'] = round(min($margin, 30) / 30 * 100, 1);
        }

        // Pembulatan satu desimal pada lima baris hanya menghasilkan 99.8%,
        // sehingga sisa pembulatan ditampung pada baris terakhir (Lembang)
        // agar kontribusi kanal tetap utuh 100.0%.
        $lastIndex = array_key_last($rows);
        $rows[$lastIndex]['share'] = round(100 - array_sum(array_column(array_slice($rows, 0, $lastIndex), 'share')), 1);
        $rows[$lastIndex]['share_label'] = self::percent($rows[$lastIndex]['share'], 1).'%';

        $grossTotal = array_sum(array_column($rows, 'gross'));

        return [
            'badge' => 'PRD CORE 5',
            'title' => 'Kinerja Portofolio Finansial: 5 Komoditas Inti GPA',
            'subtitle' => 'Rekonsiliasi laba kotor per komoditas.',
            'rule' => 'PRD SECTION 20: MARGIN FLOOR 15.0%',
            'columns' => [
                'KOMODITAS & SKU',
                'VOLUME (KG)',
                'HARGA (IDR/KG)',
                'REALISASI (IDR/KG)',
                'TOTAL OMZET',
                'GROSS PROFIT',
                'MARGIN',
                '% KONTRIBUSI',
                'DIREKSI',
            ],
            'rows' => $rows,
            'total_volume' => $volumeTotal,
            'total_volume_label' => number_format($volumeTotal, 0, '.', '.').' kg',
            'total_revenue' => $revenueTotal,
            'total_revenue_label' => self::rupiah($revenueTotal),
            'total_gross' => $grossTotal,
            'total_gross_label' => self::rupiah($grossTotal),
            'total_margin' => round($grossTotal / $revenueTotal * 100, 1),
            'total_margin_label' => self::percent($grossTotal / $revenueTotal * 100, 1).'%',
            'total_share' => round(array_sum(array_column($rows, 'share')), 1),
            'total_share_label' => self::percent(array_sum(array_column($rows, 'share')), 1).'%',
            'verified' => 'VERIFIED',
            'footer' => 'Volume diturunkan dari omzet dibagi harga realisasi sehingga tera timbangan (UU No. 2/1981) selalu utuh; sisa pembulatan kontribusi ditampung pada baris terakhir.',
        ];
    }

    /**
     * StatusDireksi berdasarkan margin laba kotor komoditas.
     *
     * @return array{label: string, tone: string}
     */
    private static function portfolioStatus(float $margin): array
    {
        return match (true) {
            $margin >= 23.0 => ['label' => 'PRIMA', 'tone' => 'success'],
            $margin >= 19.5 => ['label' => 'NOMINAL', 'tone' => 'ink'],
            $margin >= 17.5 => ['label' => 'STABIL', 'tone' => 'deep'],
            default => ['label' => 'TIGHT', 'tone' => 'caution'],
        };
    }

    /**
     * Rekapitulasi cash inflow per metode pembayaran.
     *
     * @return array<string, mixed>
     */
    public static function settlement(): array
    {
        $rows = [
            ['name' => 'Transfer Manual Bank', 'value' => 260_631_000, 'icon' => 'banknote', 'note' => '100% Bukti Bayar Tervalidasi', 'note_tone' => 'success'],
            ['name' => 'B2B', 'value' => 173_754_000, 'icon' => 'building', 'note' => 'Jatuh Tempo W46 - W48', 'note_tone' => 'ink'],
            ['name' => 'QRIS Statis Sekre', 'value' => 28_959_000, 'icon' => 'qrcode', 'note' => 'Settlement Otomatis H+0', 'note_tone' => 'ink'],
            ['name' => 'Tunai', 'value' => 19_306_000, 'icon' => 'wallet', 'note' => 'Disetor ke Kasir Lembang', 'note_tone' => 'ink'],
        ];

        $total = array_sum(array_column($rows, 'value'));

        foreach ($rows as $index => $row) {
            $rows[$index]['value_label'] = self::rupiah($row['value']);
            $rows[$index]['share'] = (int) round($row['value'] / $total * 100, 0);
            $rows[$index]['share_label'] = $rows[$index]['share'].'%';
        }

        return [
            'badge' => 'GATEWAY AUDIT: PASSED',
            'title' => 'Rekonsiliasi Cash Inflow & Distribusi Metode Pembayaran',
            'subtitle' => 'Validasi kepatuhan tata kelola arus kas masuk berdasarkan pedoman PRD Rule 11 & Rule 15.',
            'rows' => $rows,
            'total' => $total,
            'total_label' => 'TOTAL CASH INFLOW: '.self::rupiah($total),
            'total_share' => array_sum(array_column($rows, 'share')),
            'seal' => [
                'title' => 'Rekapitulasi Kas Harian',
                'note' => 'Seluruh rekap kas masuk harian ditandatangani secara digital oleh Direksi Keuangan.',
                'chip' => 'INTEGRITAS',
                'meta' => 'Tanda tangan digital sah // Hash SHA-256 // RUPTL',
            ],
        ];
    }

    private static function rupiah(int $value): string
    {
        return 'Rp '.number_format($value, 0, '.', '.');
    }

    private static function percent(float $value, int $decimals): string
    {
        return number_format($value, $decimals, '.', ',');
    }
}
