<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data "Volume Komoditas, Stok Panen & Kapasitas Pasokan".
 *
 * PRD Section 7.3, 8.2 & 16: Direksi wajib mengawasi alokasi tonase riil,
 * rasio pasokan petani binaan vs buffer stock, mitigasi risiko overselling,
 * serta neraca lima komoditas inti. PRD Rule 02 mengunci alokasi pasokan
 * binaan 82% vs buffer 18%, PRD Rule 03 mensyaratkan safety stock
 * siap pakai, sedangkan PRD Rule 04 & 13 menetapkan plafon susut gudang dan
 * sortir maksimum 2.0%.
 *
 * Seluruh angka diturunkan dari satu volume MTD konsolidasi sehingga pita KPI,
 * neraca lima komoditas, grafik mingguan, dan matriks alokasi kontrak B2B
 * selalu saling cocok.
 */
class DirectorVolumeData
{
    /**
     * Volume terjual MTD Oktober 2026 yang menjadi acuan seluruh modul.
     */
    public const TOTAL_VOLUME = 482.65;

    /**
     * Baseline kuartal yang dipakai menghitung capaian target periode.
     */
    public const QUARTER_TARGET = 420.0;

    /**
     * Porsi pasokan dari petani binaan menurut PRD Rule 02.
     */
    public const FARMER_SHARE = 82.0;

    /**
     * Porsi pasokan dari mitra luar dan buffer stock.
     */
    public const BUFFER_SHARE = 18.0;

    /**
     * Volume pasokan yang berasal dari petani binaan.
     */
    public const FARMER_VOLUME = 395.77;

    /**
     * Volume buffer stock yang menutup selisih alokasi.
     */
    public const BUFFER_VOLUME = 86.88;

    /**
     * Susut gudang dan sortasi rata-rata periode berjalan.
     */
    public const SHRINKAGE = 0.7;

    /**
     * Plafon susut menurut PRD Rule 04 & 13.
     */
    public const SHRINKAGE_CEILING = 2.0;

    /**
     * Safety stock siap pakai menurut PRD Rule 03.
     */
    public const SAFETY_BUFFER = 11.20;

    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => DirectorDashboardData::operator($user),
            'header' => self::header(),
            'cards' => self::kpiCards(),
            'commodities' => self::commodities(),
            'trend' => self::weeklyTrend(),
            'allocations' => self::allocations(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function header(): array
    {
        return [
            'eyebrow' => 'Monitoring Volume Komoditas',
            'eyebrow_meta' => 'Stok Panen & Kapasitas Pasokan',
            'title' => 'Monitoring Volume Komoditas, Stok Panen & Kapasitas Pasokan',
            'subtitle' => 'Pengawasan alokasi tonase riil, komposisi pasokan dan buffer stock, mitigasi risiko overselling, dan neraca komoditas 5 produk inti (PRD Section 7.3, 8.2 & 16).',
            'range_label' => 'RANGE:',
            'range_value' => 'MTD (OKTOBER 2026)',
            'action_label' => 'REKONSILIASI NERACA',
            'refresh_label' => 'METRIK REFRESH: REALTIME LIVE SYNC',
        ];
    }

    /**
     * Empat kartu KPI neraca volume.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function kpiCards(): array
    {
        return [
            [
                'key' => 'volume',
                'label' => 'TOTAL VOLUME TERJUAL (MTD)',
                'icon' => 'scale',
                'icon_tone' => 'neutral',
                'value_parts' => [
                    ['text' => '482.65', 'emphasis' => true],
                    ['text' => 'Ton', 'emphasis' => false],
                ],
                'value' => '482.65',
                'unit' => 'Ton',
                'notes' => [
                    ['text' => '+14.2% MoM', 'tone' => 'muted', 'dot' => true],
                    ['text' => 'vs kuartal 420 T', 'tone' => 'ink'],
                ],
                'footer' => [
                    'label' => 'TARGET ACHIEVED',
                    'chip' => '114.9%',
                    'chip_tone' => 'accent',
                ],
            ],
            [
                'key' => 'sourcing',
                'label' => 'SUMBER PASOKAN (RULE 02)',
                'icon' => 'leaf',
                'icon_tone' => 'accent',
                'value_parts' => [
                    ['text' => '82%', 'emphasis' => true],
                    ['text' => 'Binaan', 'emphasis' => false],
                    ['text' => ' : 18%', 'emphasis' => true],
                    ['text' => 'Buffer', 'emphasis' => false],
                ],
                'value' => '82% : 18%',
                'unit' => 'Binaan : Buffer',
                'notes' => [
                    ['text' => 'Binaan: 395.77 T', 'tone' => 'ink'],
                    ['text' => 'Buffer: 86.88 T', 'tone' => 'ink'],
                ],
                'footer' => [
                    'label' => 'ALOKASI MANDATORI',
                    'chip' => 'RATIO OPTIMAL',
                    'chip_tone' => 'outlined',
                ],
            ],
            [
                'key' => 'shrinkage',
                'label' => 'SUSUT GUDANG & SORTIR',
                'icon' => 'gauge',
                'icon_tone' => 'neutral',
                'value_parts' => [
                    ['text' => '0.7%', 'emphasis' => true],
                    ['text' => 'Deviasi Rata-rata', 'emphasis' => false],
                ],
                'value' => '0.7%',
                'unit' => 'Deviasi Rata-rata',
                'notes' => [
                    ['text' => 'Plafon PRD Rule 04 & 13:', 'tone' => 'ink'],
                    ['text' => 'Maks 2.0%', 'tone' => 'ink'],
                ],
                'footer' => [
                    'label' => 'STATUS PENYIMPANAN',
                    'chip' => 'SANGAT AMAN',
                    'chip_tone' => 'accent',
                ],
            ],
            [
                'key' => 'safety',
                'label' => 'SAFETY STOCK BUFFER (RULE 03)',
                'icon' => 'shield',
                'icon_tone' => 'accent',
                'value_parts' => [
                    ['text' => '11.20', 'emphasis' => true],
                    ['text' => 'Ton Siap Pakai', 'emphasis' => false],
                ],
                'value' => '11.20',
                'unit' => 'Ton Siap Pakai',
                'notes' => [
                    ['text' => 'Zero-Deficit Flag Verified', 'tone' => 'muted', 'dot' => true],
                ],
                'footer' => [
                    'label' => 'ANTI-OVERSELLING GUARD',
                    'chip' => '100% AKTIF',
                    'chip_tone' => 'outlined',
                ],
            ],
        ];
    }

    /**
     * Neraca volume lima komoditas inti beserta status alokasi.
     *
     * @return array<string, mixed>
     */
    public static function commodities(): array
    {
        $rows = [
            [
                'code' => 'KMD-01',
                'name' => 'Selada Romaine',
                'grade' => 'Hydroponic Super Grade A',
                'share' => 45,
                'value' => 217.1,
                'value_label' => '217.1',
                'farmer' => 180.0,
                'farmer_label' => '180.0 T',
                'buffer' => 37.1,
                'buffer_label' => '37.1 T',
                'headroom' => 14.5,
                'headroom_label' => '14.5 T',
                'status' => 'PASOKAN STABIL',
                'status_tone' => 'success',
                'headroom_tone' => 'shell',
            ],
            [
                'code' => 'KMD-02',
                'name' => 'Tomat Beef & Sayur',
                'grade' => 'Greenhouse Climate-Ctrl',
                'share' => 25,
                'value' => 120.6,
                'value_label' => '120.6',
                'farmer' => 98.0,
                'farmer_label' => '98.0 T',
                'buffer' => 22.6,
                'buffer_label' => '22.6 T',
                'headroom' => 8.2,
                'headroom_label' => '8.2 T',
                'status' => 'PASOKAN STABIL',
                'status_tone' => 'success',
                'headroom_tone' => 'shell',
            ],
            [
                'code' => 'KMD-03',
                'name' => 'Brokoli Super',
                'grade' => 'High Altitude Lembang',
                'share' => 15,
                'value' => 72.4,
                'value_label' => '72.4',
                'farmer' => 61.0,
                'farmer_label' => '61.0 T',
                'buffer' => 11.4,
                'buffer_label' => '11.4 T',
                'headroom' => 4.8,
                'headroom_label' => '4.8 T',
                'status' => 'KUOTA TERBATAS',
                'status_tone' => 'caution',
                'headroom_tone' => 'shell',
            ],
            [
                'code' => 'KMD-04',
                'name' => 'Stroberi Ciwidey',
                'grade' => 'Sweet Grade Premium',
                'share' => 10,
                'value' => 48.3,
                'value_label' => '48.3',
                'farmer' => 39.0,
                'farmer_label' => '39.0 T',
                'buffer' => 9.3,
                'buffer_label' => '9.3 T',
                'headroom' => 2.1,
                'headroom_label' => '2.1 T',
                'status' => 'KETAT (HIGH DEMAND)',
                'status_tone' => 'caution',
                'headroom_tone' => 'danger',
            ],
            [
                'code' => 'KMD-05',
                'name' => 'Kol Putih Organik',
                'grade' => 'Certified Organic Pangalengan',
                'share' => 5,
                'value' => 24.25,
                'value_label' => '24.25',
                'farmer' => 17.77,
                'farmer_label' => '17.77 T',
                'buffer' => 6.48,
                'buffer_label' => '6.48 T',
                'headroom' => 6.0,
                'headroom_label' => '6.0 T',
                'status' => 'MELIMPAH (SAFE)',
                'status_tone' => 'success',
                'headroom_tone' => 'shell',
            ],
        ];

        $total = round(array_sum(array_column($rows, 'value')), 2);
        $farmer = round(array_sum(array_column($rows, 'farmer')), 2);
        $buffer = round(array_sum(array_column($rows, 'buffer')), 2);

        foreach ($rows as $index => $row) {
            $rows[$index]['share_percent'] = round($row['value'] / $total * 100, 1);
            $rows[$index]['share_label'] = $row['share'].'% VOL';
            $rows[$index]['share_tone'] = $row['status_tone'] === 'caution' ? 'caution' : 'success';
            $rows[$index]['buffer_percent'] = round($row['buffer'] / $row['value'] * 100, 1);
        }

        return [
            'title' => 'Neraca Volume & Komposisi 5 Komoditas Inti',
            'subtitle' => 'Realisasi alokasi kebun binaan vs buffer mitra luar serta sisa kuota order B2B terbuka.',
            'rows' => $rows,
            'total' => $total,
            'total_label' => self::ton($total),
            'farmer_total' => $farmer,
            'farmer_total_label' => self::ton($farmer),
            'buffer_total' => $buffer,
            'buffer_total_label' => self::ton($buffer),
            'headroom_total' => round(array_sum(array_column($rows, 'headroom')), 1),
            'farmer_percent' => round($farmer / $total * 100, 1),
            'buffer_percent' => round($buffer / $total * 100, 1),
        ];
    }

    /**
     * Grafik batang mingguan: demand kontrak vs yield riil vs reserve.
     *
     * @return array<string, mixed>
     */
    public static function weeklyTrend(): array
    {
        $rows = [
            ['week' => 'W40', 'total' => 108.0, 'demand' => 70, 'yield' => 20, 'reserve' => 10, 'status' => 'closed'],
            ['week' => 'W41', 'total' => 120.0, 'demand' => 68, 'yield' => 22, 'reserve' => 10, 'status' => 'closed'],
            ['week' => 'W42', 'total' => 125.0, 'demand' => 72, 'yield' => 18, 'reserve' => 10, 'status' => 'closed'],
            ['week' => 'W43', 'total' => 130.0, 'demand' => 70, 'yield' => 20, 'reserve' => 10, 'status' => 'closed'],
            ['week' => 'W44', 'total' => 135.0, 'demand' => 74, 'yield' => 18, 'reserve' => 8, 'status' => 'running'],
            ['week' => 'W45', 'total' => 124.0, 'demand' => 70, 'yield' => 20, 'reserve' => 10, 'status' => 'forecast'],
            ['week' => 'W46', 'total' => 127.0, 'demand' => 72, 'yield' => 18, 'reserve' => 10, 'status' => 'forecast'],
            ['week' => 'W47', 'total' => 130.0, 'demand' => 70, 'yield' => 20, 'reserve' => 10, 'status' => 'forecast'],
        ];

        $max = max(array_column($rows, 'total'));
        $peak = $rows[(int) array_search($max, array_column($rows, 'total'), true)];

        foreach ($rows as $index => $row) {
            $yield = round($row['total'] * $row['yield'] / 100, 2);
            $reserve = round($row['total'] * $row['reserve'] / 100, 2);
            $demand = round($row['total'] - $yield - $reserve, 2);

            $rows[$index]['total_label'] = ($row['status'] === 'forecast' ? '~' : '').self::ton($row['total'], 0).' T';
            $rows[$index]['segments'] = [
                ['key' => 'reserve', 'tons' => $reserve, 'label' => self::ton($reserve, 2).' T'],
                ['key' => 'yield', 'tons' => $yield, 'label' => self::ton($yield, 2).' T'],
                ['key' => 'demand', 'tons' => $demand, 'label' => self::ton($demand, 2).' T'],
            ];
            $rows[$index]['is_active'] = $row['status'] === 'running';
            $rows[$index]['is_peak'] = $row['total'] === $peak['total'];
            $rows[$index]['bar_percent'] = round($row['total'] / $max * 100, 1);
            $rows[$index]['label'] = $row['week'].($row['status'] === 'running' ? ' [AKTIF]' : ($row['status'] === 'forecast' ? ' (EST)' : ''));
        }

        return [
            'badge' => 'PRD 8.2',
            'title' => 'Tren Mingguan Volume vs Kapasitas Panen (W40 - W47)',
            'subtitle' => 'Komparasi demand kontrak vs yield riil petani vs safety buffer reserve.',
            'legend' => [
                ['label' => 'Demand', 'swatch' => 'bg-ink-strong'],
                ['label' => 'Yield Binaan', 'swatch' => 'bg-accent-deep'],
                ['label' => 'Reserve', 'swatch' => 'bg-accent'],
            ],
            'rows' => $rows,
            'max' => $max,
            'peak_label' => 'Puncak Demand: '.$peak['week'].' ('.self::ton($peak['total'], 0).' T)',
            'metrology' => 'CATATAN METROLOGI: Proyeksi yield W45-W47 mengintegrasikan curah hujan BMKG Stasiun Geofisika Bandung Kelas I.',
            'confidence_label' => 'KONFIDENSI MODEL: 97.4%',
            'confidence' => 97.4,
            'actual_total' => round(array_sum(array_column(array_filter(
                $rows,
                fn (array $row): bool => $row['status'] !== 'forecast'
            ), 'total')), 1),
            'forecast_total' => round(array_sum(array_column(array_filter(
                $rows,
                fn (array $row): bool => $row['status'] === 'forecast'
            ), 'total')), 1),
        ];
    }

    /**
     * Matriks alokasi kontrak B2B per mitra tier-1.
     *
     * @return array<string, mixed>
     */
    public static function allocations(): array
    {
        $rows = [
            ['name' => 'PT Aerofood ACS', 'sentra' => 'CIANJUR (AGR-01)', 'commitment' => 28.50, 'priority' => 'PRIORITAS UTAMA'],
            ['name' => 'Royal Ambarrukmo', 'sentra' => 'CIWIDEY (AGR-04)', 'commitment' => 12.20, 'priority' => 'KONTRAK BULANAN'],
            ['name' => 'PT Segar Makmur Ritelindo', 'sentra' => 'LEMBANG (AGR-02)', 'commitment' => 18.40, 'priority' => 'DISTRIBUSI NASIONAL'],
            ['name' => 'PT Boga Rasa Kulina Prima', 'sentra' => 'PANGALENGAN (AGR-03)', 'commitment' => 14.60, 'priority' => 'FOOD SERVICE QSR'],
        ];

        $total = round(array_sum(array_column($rows, 'commitment')), 2);
        $capacity = 80.80;

        foreach ($rows as $index => $row) {
            $rows[$index]['commitment_label'] = self::ton($row['commitment'], 2);
            $rows[$index]['commitment_label_unit'] = 'Ton/mgg';
        }

        $serapPercent = round($total / $capacity * 100, 1);

        return [
            'badge' => 'PRD 7.3',
            'title' => 'Matriks Alokasi Kontrak B2B',
            'subtitle' => 'Komitmen mingguan 4 mitra tier-1 vs kuota kebun.',
            'rows' => $rows,
            'total' => $total,
            'total_label' => 'TOTAL KOMITMEN 4 MITRA:',
            'total_value_label' => self::ton($total, 2).' Ton / Minggu',
            'capacity' => $capacity,
            'capacity_label' => self::ton($capacity, 2).' Ton / Minggu',
            'serap_percent' => $serapPercent,
            'serap_label' => $serapPercent.'% SERAP',
        ];
    }

    /**
     * Ringkasan rekonsiliasi untuk tombol "REKONSILIASI NERACA".
     *
     * @return array<string, mixed>
     */
    public static function reconciliation(): array
    {
        $commodities = self::commodities();

        return [
            'total' => $commodities['total'],
            'farmer_total' => $commodities['farmer_total'],
            'buffer_total' => $commodities['buffer_total'],
            'farmer_percent' => $commodities['farmer_percent'],
            'buffer_percent' => $commodities['buffer_percent'],
            'volume_label' => self::ton(self::TOTAL_VOLUME).' Ton',
            'farmer_label' => self::ton(self::FARMER_VOLUME).' Ton',
            'buffer_label' => self::ton(self::BUFFER_VOLUME).' Ton',
            'target_percent' => round(self::TOTAL_VOLUME / self::QUARTER_TARGET * 100, 1),
        ];
    }

    /**
     * Format tonase dengan presisi sesuai design, tanpa pemisah ribuan.
     */
    private static function ton(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, '.', '');
    }
}
