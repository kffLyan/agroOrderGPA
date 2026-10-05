<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data "Dashboard Eksekutif // Monitoring Bisnis & Otorisasi Direktur".
 *
 * PRD Section 16 & 20: seluruh pengajuan kontrak tier-1 (diskon di luar plafon,
 * perpanjangan tenor TOP, dan kontrak induk tahunan) hanya boleh dilepas بعد
 * tanda tangan elektronik Direktur Utama. PRD Metrologi Legal UU No. 2/1981
 * menjadi rujukan pembuktian tera sah pada neraca komoditas.
 */
class DirectorDashboardData
{
    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => self::operator($user),
            'header' => self::header(),
            'kpis' => self::kpis(),
            'weekly' => self::weeklyTrend(),
            'commodities' => self::commodities(),
            'channels' => self::channels(),
            'receivables' => self::receivables(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function operator(?User $user = null): array
    {
        $name = $user?->name ?: 'Asep Tember';

        return [
            'role' => 'Direktur',
            'name' => $name,
            'code' => '001',
            'initials' => self::initials($name),
            'branch' => 'Kantor Pusat GPA - Lantai 3',
            'email' => $user?->email ?? 'direktur@agroorder.co.id',
            'period' => 'W44-2026 // Q4 YTD',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function header(): array
    {
        return [
            'eyebrow' => 'RINGKASAN EKSEKUTIF',
            'title' => 'Dashboard Eksekutif // Monitoring Bisnis & Otorisasi Direktur',
            'subtitle' => 'Konsolidasi metrik strategis penjualan, neraca komoditas, manajemen risiko piutang B2B, dan otorisasi kontrak tier-1 berbasis regulasi Metrologi Legal UU No. 2/1981.',
            'period_label' => 'PERIODE AUDIT AKTIF',
            'period_value' => 'W44-2026 // Q4 YTD',
            'print_label' => 'Cetak Dokumen Otorisasi',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function kpis(): array
    {
        $revenue = 482_650_000;
        $revenueTarget = 600_000_000;
        $volumeKg = 38_450;
        $volumeTurbine = 26_150;
        $volumeBuffer = 12_300;
        $authorizationValue = 385_000_000;

        return [
            [
                'key' => 'revenue',
                'variant' => 'progress',
                'label' => 'OMZET Q4 YTD',
                'icon' => 'chart',
                'tone' => 'success',
                'value' => self::rupiah($revenue),
                'value_note' => 'Target: '.self::rupiah($revenueTarget),
                'badge' => '+14.2% MoM',
                'progress' => [
                    'label' => 'Progress Target',
                    'value' => self::percent($revenue / $revenueTarget * 100, 1).'% (84.6% Terbayar)',
                    'percent' => round($revenue / $revenueTarget * 100, 1),
                ],
            ],
            [
                'key' => 'volume',
                'variant' => 'volume',
                'label' => 'VOLUME KOMODITAS',
                'icon' => 'package',
                'tone' => 'success',
                'value' => number_format($volumeKg, 0, '.', '.'),
                'value_suffix' => 'kg ('.number_format($volumeKg / 1000, 2, '.', '.').' T)',
                'value_note' => self::percent($volumeTurbine / $volumeKg * 100, 0).'% Binaan ('
                    .self::decimal($volumeTurbine / 1000).' T) : '
                    .self::percent($volumeBuffer / $volumeKg * 100, 0).'% Buffer ('
                    .self::decimal($volumeBuffer / 1000).' T)',
                'split' => [
                    ['text' => self::percent($volumeTurbine / $volumeKg * 100, 0).'%', 'tone' => 'ink'],
                    ['text' => ' Binaan ('.self::decimal($volumeTurbine / 1000).' T) : ', 'tone' => 'muted'],
                    ['text' => self::percent($volumeBuffer / $volumeKg * 100, 0).'%', 'tone' => 'ink'],
                    ['text' => ' Buffer ('.self::decimal($volumeBuffer / 1000).' T)', 'tone' => 'muted'],
                ],
                'footer_left' => '100% Tera Sah Metrologi',
                'footer_right' => 'UU 2/1981',
            ],
            [
                'key' => 'receivable',
                'variant' => 'badge',
                'label' => 'PIUTANG TERBUKA',
                'icon' => 'alert-triangle',
                'tone' => 'warning',
                'value' => self::rupiah(74_320_000),
                'value_note' => '12 Kontrak',
                'badge' => '2 Klien Tempo <7 Hari',
                'footer_left' => 'Rasio Tertagih: 91.2%',
                'footer_right' => 'Rp 45.2M Near-Due',
            ],
            [
                'key' => 'sla',
                'variant' => 'badge',
                'label' => 'KEPATUHAN SLA',
                'icon' => 'check-circle',
                'tone' => 'success',
                'value' => '98.6%',
                'value_suffix' => 'Tuntas',
                'value_note' => 'Retur Fisik: 0.82% (Batas: <1.50%)',
                'footer_left' => 'MUTU GRADE A/B+',
                'footer_right' => 'Cold-Chain Valid',
            ],
            [
                'key' => 'authorization',
                'variant' => 'highlight',
                'label' => 'OTORISASI DIREKTUR',
                'icon' => 'shield',
                'tone' => 'danger',
                'value' => '3 Kontrak',
                'value_note' => 'Total Nilai: '.self::rupiah($authorizationValue),
                'split' => [
                    ['text' => 'Total Nilai: ', 'tone' => 'body'],
                    ['text' => self::rupiah($authorizationValue), 'tone' => 'strong'],
                ],
                'badge' => 'MUTLAK',
                'cta' => 'Tinjau Sekarang',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function weeklyTrend(): array
    {
        $rows = [
            [
                'week' => 'W40',
                'label' => 'W40: 275 T // Target Rp 110M',
                'volume_ton' => 275,
                'target' => 110_000_000,
                'realized' => 108_000_000,
                'ratio' => 98.1,
                'status' => 'closed',
                'bar_label' => 'Rp 108M',
                'volume_label' => '275 Ton Panen',
                'marker' => 81.1,
            ],
            [
                'week' => 'W41',
                'label' => 'W41: 290 T // Target Rp 120M',
                'volume_ton' => 290,
                'target' => 120_000_000,
                'realized' => 124_000_000,
                'ratio' => 103.3,
                'status' => 'closed',
                'bar_label' => 'Rp 124M',
                'volume_label' => '290 Ton Panen',
                'marker' => 81.1,
            ],
            [
                'week' => 'W42',
                'label' => 'W42: 315 T // Target Rp 135M',
                'volume_ton' => 315,
                'target' => 135_000_000,
                'realized' => 138_000_000,
                'ratio' => 102.2,
                'status' => 'closed',
                'bar_label' => 'Rp 138M',
                'volume_label' => '315 Ton Panen',
                'marker' => 81.1,
            ],
            [
                'week' => 'W43',
                'label' => 'W43: 338 T // Target Rp 140M',
                'volume_ton' => 338,
                'target' => 140_000_000,
                'realized' => 140_000_000,
                'ratio' => 100.0,
                'status' => 'closed',
                'bar_label' => 'Rp 140M',
                'volume_label' => '338 Ton Panen',
                'marker' => 81.1,
            ],
            [
                'week' => 'W44',
                'label' => 'W44 [Berjalan - H+4]: Target Rp 140M',
                'volume_ton' => 202.5,
                'target' => 140_000_000,
                'realized' => 112_650_000,
                'ratio' => 80.5,
                'status' => 'running',
                'bar_label' => 'Rp 112.65M',
                'volume_label' => '202.5 Ton Terdistribusi',
                'marker' => 66.1,
            ],
        ];

        $peak = max(array_column($rows, 'realized'));

        foreach ($rows as $index => $row) {
            $rows[$index]['realized_label'] = self::rupiah($row['realized']);
            $rows[$index]['ratio_label'] = self::percent($row['ratio'], 1).'%';
            $rows[$index]['bar_percent'] = round($row['realized'] / $peak * 100, 1);
            $rows[$index]['marker_tone'] = $row['status'] === 'running' ? 'ink' : 'success';
        }

        return [
            'title' => 'Tren Omzet vs Target Mingguan & Realisasi Panen',
            'subtitle' => 'Siklus W40 - W44 (Realisasi Tonase vs Cash Inflow)',
            'legend' => [
                ['label' => 'Realisasi Omzet (Juta Rp)', 'swatch' => 'bg-brand'],
                ['label' => 'Target Rencana', 'swatch' => 'bg-accent-edge'],
                ['label' => 'Volume (Ton)', 'swatch' => 'bg-accent-deep'],
            ],
            'rows' => $rows,
            'source' => 'SUMBER DATA: CORE TRANSAKSI TIMBANGAN ELEKTRONIK HUBS',
            'deviation' => 'DEVIASI TERTINGGI: +3.3% (W41)',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function commodities(): array
    {
        $rows = [
            ['name' => '1. Selada Romaine', 'tons' => 14.20, 'share' => 37, 'bar' => 'bg-ink'],
            ['name' => '2. Tomat Sayur & Beef', 'tons' => 10.80, 'share' => 28, 'bar' => 'bg-brand'],
            ['name' => '3. Brokoli Super', 'tons' => 7.10, 'share' => 18, 'bar' => 'bg-success'],
            ['name' => '4. Stroberi Ciwidey', 'tons' => 4.20, 'share' => 11, 'bar' => 'bg-accent-deep'],
            ['name' => '5. Kol Putih Organik', 'tons' => 2.15, 'share' => 6, 'bar' => 'bg-accent-edge'],
        ];

        foreach ($rows as $index => $row) {
            $rows[$index]['tons_label'] = number_format($row['tons'], 2, '.', '');
        }

        $total = array_sum(array_column($rows, 'tons'));

        return [
            'title' => 'Distribusi 5 Komoditas Inti & Kanal',
            'subtitle' => 'Realisasi Volume Q4 (Total '.number_format($total, 2, '.', '').' Ton)',
            'rows' => $rows,
            'total_tons' => $total,
            'footer_left' => 'BUFFER RASIO SEHAT: 1 : 2.1',
            'footer_right' => 'COLD-CHAIN RETENTION: 99.4%',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function channels(): array
    {
        $rows = [
            ['name' => 'Horeca & Inflight', 'share' => 65, 'tons' => 25.00],
            ['name' => 'Ritel Modern', 'share' => 25, 'tons' => 9.60],
            ['name' => 'Reguler WA', 'share' => 10, 'tons' => 3.85],
        ];

        foreach ($rows as $index => $row) {
            $rows[$index]['tons_label'] = self::tons($row['tons']);
        }

        return [
            'title' => 'SEGMENTASI KANAL PENJUALAN',
            'rows' => $rows,
            'total_share' => array_sum(array_column($rows, 'share')),
            'total_tons' => array_sum(array_column($rows, 'tons')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function receivables(): array
    {
        $buckets = [
            [
                'key' => 'normal',
                'label' => 'NORMAL (0 - 15 HARI)',
                'value' => 120_000_000,
                'detail' => '8 Klien // Siklus Penagihan Aman',
                'status' => 'STATUS: BERSIH',
                'tone' => 'success',
            ],
            [
                'key' => 'due',
                'label' => 'MENUJU TEMPO (16 - 30 HARI)',
                'value' => 65_300_000,
                'detail' => '4 Klien // Reminder Otomatis Terkirim',
                'status' => 'STATUS: NOTICE AKTIF',
                'tone' => 'ink',
            ],
            [
                'key' => 'late',
                'label' => 'LEWAT TEMPO (1 - 14 HARI)',
                'value' => 32_200_000,
                'detail' => '2 Klien // Somasi Administratif I',
                'status' => 'WARNING LEVEL 2',
                'tone' => 'caution',
            ],
            [
                'key' => 'critical',
                'label' => 'OVERDUE KRITIS (> 15 HARI)',
                'value' => 18_000_000,
                'detail' => '1 Klien (CV Mitra Boga Nusantara)',
                'status' => 'AUTO FREEZE PO: DIKUNCI',
                'tone' => 'danger',
            ],
        ];

        foreach ($buckets as $index => $bucket) {
            $buckets[$index]['value_label'] = self::rupiah($bucket['value']);
        }

        return [
            'title' => 'Lembar Pengawasan Piutang & Aging Tagihan Klien (TOP)',
            'description' => 'Pencegahan defisit kas melalui automated credit hold, pembekuan repeat order, dan penagihan berjenjang.',
            'total_label' => 'TOTAL PIUTANG TERBUKA: Rp 235.500.000',
            'total' => array_sum(array_column($buckets, 'value')),
            'buckets' => $buckets,
            'note' => [
                'lead' => 'Sistem mendeteksi CV Mitra Boga Nusantara menunggak Rp 18.000.000 melampaui 18 hari dari TOP. Pengiriman baru telah',
                'emphasis' => 'DIKUNCI SECARA OTOMATIS',
                'tail' => 'oleh bot kepatuhan.',
            ],
            'audit_action' => 'Buka Catatan Audit',
            'dispensation_action' => 'Dispensasi Direktur',
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

    /**
     * Angka tonase dengan nol desimal akhiring dibuang, mis. 12.30 -> 12.3.
     */
    private static function decimal(float $value, int $decimals = 2): string
    {
        return rtrim(rtrim(number_format($value, $decimals, '.', ''), '0'), '.');
    }

    /**
     * Tonase kanal yang tetap memiliki satu desimal untuk nilai bulat,
     * mis. 25.00 -> 25.0 dan 3.85 -> 3.85.
     */
    private static function tons(float $value): string
    {
        $formatted = self::decimal($value);

        return str_contains($formatted, '.') ? $formatted : $formatted.'.0';
    }

    private static function initials(string $name): string
    {
        $initials = '';

        foreach (preg_split('/\s+/', trim($name)) ?: [] as $part) {
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
