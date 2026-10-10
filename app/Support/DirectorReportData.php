<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data "Laporan Penjualan Eksekutif" (arsip audit terkunci).
 *
 * PRD Section 7.3, 8.2 & 16: laporan kuartal yang telah disahkan Direksi
 * bersifat read-only dan dilindungi hash SHA-256. Setiap angka pada halaman
 * ini diturunkan dari satu omzet konsolidasi kuartal agar pita KPI, tabel
 * komoditas, rekapitulasi piutang, dan audit trail selalu saling cocok.
 */
class DirectorReportData
{
    /**
     * Periode laporan yang telah dikunci permanen.
     */
    public const PERIOD = 'Kuartal IV 2026 (Okt - Des 2026)';

    /**
     * Label periode ringkas pada filter terkunci.
     */
    public const PERIOD_SHORT = 'Kuartal IV (Okt - Des 2026)';

    /**
     * Waktu penguncian periode beserta timestamp audit trail.
     */
    public const LOCKED_AT = '24 Okt 2026 23:59:59 WIB';

    /**
     * Pejabat penandatangan utama laporan.
     */
    public const SIGNATORY = 'Ahmad Sanusi, S.P.';

    /**
     * Jabatan penandatangan utama laporan.
     */
    public const SIGNATORY_ROLE = 'Direktur Utama Green Pasundan Agriculture';

    /**
     * Nomor sertifikat audit yang menyertai arsip.
     */
    public const CERTIFICATE = 'GPA-AUDIT-CERT-2026-Q4';

    /**
     * Hash konsolidasi arsip yang melindungi seluruh baris laporan.
     */
    public const HASH = '0x8F9C4A217B1E90D4CC67F814E32A0B7D18C992E5F67104B8A293CD0891DE33';

    /**
     * Omzet bersih konsolidasi kuartal yang disahkan.
     */
    public const REVENUE = 482_650_000;

    /**
     * Baseline target kuartal IV 2026.
     */
    public const QUARTER_TARGET = 600_000_000;

    /**
     * Cash inflow yang sudah masuk dan diverifikasi terhadap arsip.
     */
    public const CASH_INFLOW = 408_330_000;

    /**
     * Sisa piutang berjalan tempo pada saat penguncian.
     */
    public const RECEIVABLE = 74_320_000;

    /**
     * Klaim penyusutan dan retur fisik yang sudah tuntas.
     */
    public const SHRINKAGE_CLAIM = 7_400_000;

    /**
     * Komponen penyusutan gudang dari total klaim.
     */
    public const SHRINKAGE_VALUE = 3_250_000;

    /**
     * Komponen retur fisik dari total klaim.
     */
    public const RETURN_VALUE = 4_150_000;

    /**
     * Plafon toleransi penyusutan menurut PRD Rule 04 & 13.
     */
    public const TOLERANCE_CEILING = 2.0;

    /**
     * Diskon kontrak yang sudah disahkan pada periode berjalan.
     */
    public const CONTRACT_DISCOUNT = 14_800_000;

    /**
     * Tonase pasokan dari petani binaan pada periode terkunci.
     */
    public const FARMER_VOLUME = 26.15;

    /**
     * Tonase buffer stock pada periode terkunci.
     */
    public const BUFFER_VOLUME = 12.30;

    /**
     * Non-performing loan yang tercatat pada arsip.
     */
    public const NPL = 0.0;

    /**
     * Versi sistem yang menghasilkan audit trail.
     */
    public const SYSTEM_VERSION = 'SYS-V4.9.1';

    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => DirectorDashboardData::operator($user),
            'lock' => self::lock(),
            'filters' => self::filters(),
            'cards' => self::kpiCards(),
            'commodities' => self::commodities(),
            'clients' => self::clients(),
            'audit' => self::auditTrail(),
            'seal' => self::seal(),
        ];
    }

    /**
     * Banner arsip terkunci beserta filter beku.
     *
     * @return array<string, mixed>
     */
    public static function lock(): array
    {
        return [
            'badge' => 'STATUS: LAPORAN TELAH DIKUNCI',
            'watermark' => 'LOCKED_SHA256',
            'period' => self::PERIOD,
            'locked_at' => self::LOCKED_AT,
            'signatory' => self::SIGNATORY,
            'signatory_role' => 'Direktur Utama',
            'hash_label' => 'SHA-256:',
            'hash' => self::HASH,
            'audit_button' => 'LIHAT LOG AUDIT FORENSIK',
            'certificate_chip' => 'CERTIFICATE OF AUTHENTICITY',
        ];
    }

    /**
     * Filter periode, segmentasi, dan komoditas yang sudah dibekukan.
     *
     * @return array<string, mixed>
     */
    public static function filters(): array
    {
        $commodityCount = count(self::commodities()['rows']);

        return [
            'label' => 'FILTER FROZEN — AUDIT ARCHIVE',
            'items' => [
                [
                    'label' => 'PERIODE:',
                    'value' => self::PERIOD_SHORT,
                    'chip' => 'LOCKED',
                    'chip_tone' => 'warning',
                ],
                [
                    'label' => 'SEGMENTASI:',
                    'value' => 'Semua Klien B2B',
                    'chip' => 'FROZEN',
                    'chip_tone' => 'neutral',
                ],
                [
                    'label' => 'KOMODITAS:',
                    'value' => $commodityCount.' Komoditas Inti',
                    'chip' => 'FROZEN',
                    'chip_tone' => 'neutral',
                ],
            ],
        ];
    }

    /**
     * Empat kartu KPI arsip terkunci.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function kpiCards(): array
    {
        $commodities = self::commodities();
        $teraSah = $commodities['total_tera_kg'];

        $revenuePercent = round(self::REVENUE / self::QUARTER_TARGET * 100, 1);
        $settledPercent = round(self::CASH_INFLOW / self::REVENUE * 100, 1);
        $tolerancePercent = round(self::SHRINKAGE_CLAIM / self::REVENUE * 100, 2);
        $volumeTonase = round($teraSah / 1000, 2);

        return [
            [
                'key' => 'revenue',
                'label' => 'OMZET DISAHKAN (NETTO RIIL)',
                'value' => 'Rp '.number_format(self::REVENUE, 0, '.', '.'),
                'value_tone' => 'ink',
                'chip' => 'FINAL',
                'chip_tone' => 'accent',
                'meta' => [
                    ['text' => 'Target Q4: Rp '.number_format(self::QUARTER_TARGET, 0, '.', '.'), 'tone' => 'body'],
                    ['text' => '('.self::decimal($revenuePercent, 1).'% Tercapai)', 'tone' => 'success'],
                ],
                'footer_label' => 'Diskon Kontrak Sah:',
                'footer_value' => '-Rp '.number_format(self::CONTRACT_DISCOUNT, 0, '.', '.'),
                'footer_tone' => 'warning',
            ],
            [
                'key' => 'volume',
                'label' => 'TOTAL VOLUME TERA SAH',
                'value' => number_format($teraSah, 0, ',', '.').' kg',
                'value_tone' => 'ink',
                'chip' => '100% VALID',
                'chip_tone' => 'accent',
                'meta' => [
                    [
                        'text' => 'Ekuivalen: '.self::decimal($volumeTonase, 2).' Tonase Realisasi',
                        'tone' => 'body',
                    ],
                ],
                'footer_label' => 'Rasio Pasokan:',
                'footer_value' => self::decimal(self::FARMER_VOLUME / ($teraSah / 1000) * 100, 0)
                    .'% Binaan ('.self::decimal(self::FARMER_VOLUME, 2).' T) | '
                    .self::decimal(self::BUFFER_VOLUME / ($teraSah / 1000) * 100, 0)
                    .'% Buffer ('.self::decimal(self::BUFFER_VOLUME, 2).' T)',
                'footer_tone' => 'ink',
            ],
            [
                'key' => 'inflow',
                'label' => 'CASH INFLOW REALISASI',
                'value' => 'Rp '.number_format(self::CASH_INFLOW, 0, '.', '.'),
                'value_tone' => 'success',
                'chip' => self::decimal($settledPercent, 1).'% SETTLED',
                'chip_tone' => 'accent',
                'meta' => [
                    ['text' => 'Piutang Berjalan Tempo:', 'tone' => 'body'],
                    [
                        'text' => 'Rp '.number_format(self::RECEIVABLE, 0, '.', '.'),
                        'tone' => 'warning',
                    ],
                ],
                'footer_label' => 'Kolektibilitas / NPL:',
                'footer_value' => 'BAD DEBT: '.self::decimal(self::NPL, 1).'% (LANCAR)',
                'footer_tone' => 'success',
            ],
            [
                'key' => 'deviation',
                'label' => 'DEVIASI SUSUT & RETUR',
                'value' => 'Rp '.number_format(self::SHRINKAGE_CLAIM, 0, '.', '.'),
                'value_tone' => 'ink',
                'chip' => 'KLAIM TUNTAS',
                'chip_tone' => 'warning',
                'meta' => [
                    ['text' => 'Ambang Toleransi:', 'tone' => 'body'],
                    [
                        'text' => self::decimal($tolerancePercent, 2).'% (< '.self::decimal(self::TOLERANCE_CEILING, 1).'% Toleransi)',
                        'tone' => 'success',
                    ],
                ],
                'footer_label' => 'Susut: Rp 3.25M',
                'footer_value' => 'Retur Fisik: Rp 4.15M',
                'footer_tone' => 'quiet',
            ],
        ];
    }

    /**
     * Tabel 1: estimasi PO versus tera sah gudang per komoditas inti.
     *
     * @return array<string, mixed>
     */
    public static function commodities(): array
    {
        $rows = [
            ['name' => 'Selada Romaine Hydro', 'po_kg' => 10_200, 'tera_kg' => 10_080, 'price' => 14_500, 'margin' => 28.4],
            ['name' => 'Tomat Cherry Beef Grade A', 'po_kg' => 8_100, 'tera_kg' => 8_010, 'price' => 16_000, 'margin' => 24.8],
            ['name' => 'Brokoli Hijau Dataran Tinggi', 'po_kg' => 7_400, 'tera_kg' => 7_320, 'price' => 13_500, 'margin' => 22.1],
            ['name' => 'Stroberi Ciwidey Segar', 'po_kg' => 4_660, 'tera_kg' => 4_590, 'price' => 14_000, 'margin' => 31.0],
            ['name' => 'Kol Putih Organik Lembang', 'po_kg' => 8_500, 'tera_kg' => 8_450, 'price' => 5_355, 'margin' => 19.5],
        ];

        $revenue = 0;

        foreach ($rows as $index => $row) {
            $revenue += $rows[$index]['omzet'] = (int) (round($row['tera_kg'] * $row['price'], -3));
        }

        $poTotal = array_sum(array_column($rows, 'po_kg'));
        $teraTotal = array_sum(array_column($rows, 'tera_kg'));
        $shrinkageTotal = $poTotal - $teraTotal;
        $marginTotal = array_sum(array_column($rows, 'margin'));

        foreach ($rows as $index => $row) {
            $shrinkage = $row['po_kg'] - $row['tera_kg'];
            $deviation = round($shrinkage / $row['po_kg'] * 100, 2);

            $rows[$index]['po_label'] = number_format($row['po_kg'], 0, ',', '.');
            $rows[$index]['tera_label'] = number_format($row['tera_kg'], 0, ',', '.');
            $rows[$index]['shrinkage_kg'] = $shrinkage;
            $rows[$index]['deviation'] = $deviation;
            $rows[$index]['deviation_label'] = '-'.number_format($shrinkage, 0, ',', '.')
                .' kg ('.self::decimal(-$deviation, 2).'%)';
            $rows[$index]['price_label'] = 'Rp '.number_format($row['price'], 0, ',', '.');
            $rows[$index]['revenue_label'] = 'Rp '.number_format($row['omzet'], 0, '.', '.');
            $rows[$index]['contribution'] = round($row['omzet'] / $revenue * 100, 2);
            $rows[$index]['contribution_label'] = self::decimal($row['omzet'] / $revenue * 100, 2).'%';
            $rows[$index]['margin_label'] = self::decimal($row['margin'], 1).'%';
        }

        return [
            'title' => 'Breakdown Analitik 5 Komoditas Inti (Estimasi PO vs Tera Sah Gudang)',
            'method_chip' => 'AUDIT METHOD: DIRECT DIGITAL SCALE WEIGHT',
            'locked_chip' => 'TERKUNCI',
            'columns' => [
                'Komoditas Agribisnis',
                'Volume PO (kg)',
                'Tera Sah (kg)',
                'Deviasi Susut',
                'Harga Rata-rata/kg',
                'Omzet Realisasi',
                'Kontribusi',
                'Gross Margin',
                'Status Audit',
            ],
            'rows' => $rows,
            'total_label' => 'TOTAL KONSOLIDASI',
            'total_po_kg' => $poTotal,
            'total_po_label' => number_format($poTotal, 0, ',', '.').' kg',
            'total_tera_kg' => $teraTotal,
            'total_tera_label' => number_format($teraTotal, 0, ',', '.').' kg',
            'total_shrinkage_kg' => $shrinkageTotal,
            'total_deviation_label' => '-'.number_format($shrinkageTotal, 0, ',', '.')
                .' kg ('.self::decimal(-round($shrinkageTotal / $poTotal * 100, 2), 2).'%)',
            'total_revenue' => $revenue,
            'total_revenue_label' => 'Rp '.number_format($revenue, 0, '.', '.'),
            'total_price_label' => 'Rp '.number_format(round($revenue / $teraTotal, 0), 0, ',', '.').' (Avg)',
            'total_contribution_label' => self::decimal(100.0, 1).'%',
            'total_margin' => round($marginTotal / count($rows), 1),
            'total_margin_label' => self::decimal(round($marginTotal / count($rows), 1), 1).'% (Avg)',
            'audited_chip' => '100% AUDITED',
        ];
    }

    /**
     * Tabel 2: rekapitulasi penjualan klien B2B dan status pembayaran tempo.
     *
     * @return array<string, mixed>
     */
    public static function clients(): array
    {
        $rows = [
            [
                'name' => 'Hotel Grand Pangrango Bogor',
                'code' => 'KTR-2026-HGP-004',
                'po_count' => 12,
                'volume_kg' => 9_600,
                'gross' => 132_480_000,
                'settled' => 107_980_000,
                'term_days' => 30,
                'due_label' => 'JT: 15 Nov',
            ],
            [
                'name' => 'Resto Dapur Sunda Asli (Jaringan 4 Cabang)',
                'code' => 'KTR-2026-DSA-019',
                'po_count' => 16,
                'volume_kg' => 7_200,
                'gross' => 98_400_000,
                'settled' => 80_180_000,
                'term_days' => 14,
                'due_label' => 'JT: 08 Nov',
            ],
            [
                'name' => 'PT Mitra Boga Nusantara (Catering Industri)',
                'code' => 'KTR-2026-MBN-008',
                'po_count' => 8,
                'volume_kg' => 8_400,
                'gross' => 105_000_000,
                'settled' => 88_600_000,
                'term_days' => 30,
                'due_label' => 'JT: 22 Nov',
            ],
            [
                'name' => 'Superindo Distribusi Regional Jabar',
                'code' => 'KTR-2026-SPI-002',
                'po_count' => 6,
                'volume_kg' => 11_200,
                'gross' => 124_000_000,
                'settled' => 108_800_000,
                'term_days' => 45,
                'due_label' => 'JT: 30 Nov',
            ],
            [
                'name' => 'Klien Retail Reguler & Spot Non-Kontrak',
                'code' => 'REKAP TRANSAKSI TUNAI',
                'po_count' => 42,
                'volume_kg' => 2_050,
                'gross' => 22_770_000,
                'settled' => 22_770_000,
                'term_days' => 0,
                'due_label' => 'CBD (Cash Before Deliv)',
            ],
        ];

        foreach ($rows as $index => $row) {
            $rows[$index]['volume_label'] = number_format($row['volume_kg'], 0, ',', '.').' kg';
            $rows[$index]['po_label'] = $row['po_count'].' PO';
            $rows[$index]['gross_label'] = 'Rp '.number_format($row['gross'], 0, '.', '.');
            $rows[$index]['settled_label'] = 'Rp '.number_format($row['settled'], 0, '.', '.');
            $rows[$index]['receivable'] = $receivable = $row['gross'] - $row['settled'];
            $rows[$index]['receivable_label'] = $receivable > 0
                ? 'Rp '.number_format($receivable, 0, '.', '.')
                : 'Rp 0';
            $rows[$index]['term_label'] = $row['term_days'] > 0
                ? 'TOP '.$row['term_days'].' Hari'
                : $row['due_label'];
            $rows[$index]['status'] = $row['term_days'] > 0 ? 'LANCAR' : 'LUNAS';
            $rows[$index]['status_label'] = $row['term_days'] > 0
                ? 'LANCAR ('.$row['due_label'].')'
                : 'LUNAS 100%';
            $rows[$index]['status_tone'] = $row['term_days'] > 0 ? 'lancar' : 'lunas';
        }

        $poTotal = array_sum(array_column($rows, 'po_count'));
        $volumeTotal = array_sum(array_column($rows, 'volume_kg'));
        $grossTotal = array_sum(array_column($rows, 'gross'));
        $settledTotal = array_sum(array_column($rows, 'settled'));
        $receivableTotal = $grossTotal - $settledTotal;
        $contractTerms = array_values(array_filter(array_column($rows, 'term_days')));
        $averageTerm = $contractTerms === []
            ? 0.0
            : round(array_sum($contractTerms) / count($contractTerms), 1);

        return [
            'title' => 'Rekapitulasi Penjualan Klien B2B & Status Pembayaran Tempo (TOP)',
            'count_chip' => $poTotal.' TOTAL PO // 0 SENGKETA',
            'audit_chip' => 'AR-AUDIT COMPLETED',
            'columns' => [
                'Klien / Mitra B2B',
                'Frek. PO',
                'Volume Tera Sah (kg)',
                'Total Tagihan Bruto',
                'Settled (Inflow Sah)',
                'Sisa Piutang Berjalan',
                'Ketentuan Tempo',
                'Status Piutang',
            ],
            'rows' => $rows,
            'total_label' => 'TOTAL REKAPITULASI',
            'total_po_count' => $poTotal,
            'total_po_label' => $poTotal.' PO',
            'total_volume_kg' => $volumeTotal,
            'total_volume_label' => number_format($volumeTotal, 0, ',', '.').' kg',
            'total_gross' => $grossTotal,
            'total_gross_label' => 'Rp '.number_format($grossTotal, 0, '.', '.'),
            'total_settled' => $settledTotal,
            'total_settled_label' => 'Rp '.number_format($settledTotal, 0, '.', '.'),
            'total_receivable' => $receivableTotal,
            'total_receivable_label' => 'Rp '.number_format($receivableTotal, 0, '.', '.'),
            'average_term_days' => $averageTerm,
            'average_term_label' => 'RATA-RATA: '.self::decimal($averageTerm, 1).' HARI',
            'verified_label' => 'AUDITED & VERIFIED',
        ];
    }

    /**
     * Audit trail terminal yang mengunci arsip.
     *
     * @return array<string, mixed>
     */
    public static function auditTrail(): array
    {
        $totalTera = self::commodities()['total_tera_kg'];

        return [
            'title' => 'AUDIT TRAIL LOG TERMINAL',
            'version' => '['.self::SYSTEM_VERSION.']',
            'entries' => [
                [
                    'timestamp' => '[2026-10-24 23:45:10 WIB]',
                    'tag' => '> SYS_VERIFY:',
                    'message' => '5 komoditas timbangan tera gudang tuntas dicocokkan ('
                        .number_format($totalTera, 0, ',', '.').' kg).',
                ],
                [
                    'timestamp' => '[2026-10-24 23:51:22 WIB]',
                    'tag' => '> AR_CLEARING:',
                    'message' => 'Piutang berjalan Rp '.number_format(self::RECEIVABLE, 0, '.', '.')
                        .' diverifikasi terhadap 4 kontrak aktif.',
                ],
                [
                    'timestamp' => '[2026-10-24 23:58:05 WIB]',
                    'tag' => '> DIR_AUTH:',
                    'message' => self::SIGNATORY.' memasukkan token kunci otorisasi biometric.',
                ],
                [
                    'timestamp' => '[2026-10-24 23:59:59 WIB]',
                    'tag' => '> STATUS_LOCK_ENGAGED:',
                    'message' => 'Record disahkan. SHA-256 dibuat.',
                    'trailing' => 'Write-access dicabut permanen.',
                    'highlighted' => true,
                ],
            ],
            'daemon' => 'DAEMON: RUNNING',
            'integrity' => 'INTEGRITY CHECK: PASSED',
        ];
    }

    /**
     * Lembar pengesahan digital dan sertifikat audit.
     *
     * @return array<string, mixed>
     */
    public static function seal(): array
    {
        return [
            'title' => 'Lembar Pengesahan Digital Direksi & Sertifikat Audit',
            'certificate' => self::CERTIFICATE,
            'seal_caption' => 'GPA AUDIT SEAL',
            'authority_label' => 'PENANDATANGAN UTAMA:',
            'signatory' => self::SIGNATORY,
            'signatory_role' => self::SIGNATORY_ROLE,
            'lock_time_label' => 'WAKTU PENGUNCIAN:',
            'lock_time' => self::LOCKED_AT,
            'encryption_label' => 'SERTIFIKASI ENKRIPSI:',
            'encryption' => 'X.509 v3 / RSA 4096-bit',
            'hash_label' => 'HASH KRIPTOGRAFIS KONSOLIDASI:',
            'hash' => self::HASH,
            'excel_action' => 'DOWNLOAD BUKU BESAR EXCEL (.XLSX)',
            'pdf_action' => 'CETAK DOKUMEN PENGESAHAN (.PDF)',
            'locked_status' => 'STATUS: PERIODE TELAH DIKUNCI PERMANEN',
        ];
    }

    /**
     * Angka desimal memakai pemisah titik sesuai design laporan.
     */
    private static function decimal(float $value, int $decimals): string
    {
        return number_format($value, $decimals, '.', '');
    }
}
