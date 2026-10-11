<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data "Persetujuan Kontrak" (antrean otorisasi tier-1 Direksi).
 *
 * PRD SEC 16 & 20: setiap perubahan harga di luar plafon standar, perpanjangan
 * tenor TOP, dan kontrak induk tahunan hanya sah setelah ditandatangani
 * Direktur Utama. Modul ini memuat antrean tersebut sebagai satu kesatuan
 * modul mandiri, terpisah dari ringkasan eksekutif dashboard.
 *
 * Seluruh angka diturunkan dari baris antrean yang sama sehingga nilai
 * kontrak pending, nilai batch terpilih, dan catatan audit selalu cocok.
 */
class DirectorApprovalData
{
    /**
     * Plafon diskon di luar standar yang wajib naik ke Direktur Utama.
     */
    public const DISCOUNT_CEILING = 5.0;

    /**
     * Term of payment default sebelum perpanjangan disahkan Direksi.
     */
    public const TOP_DEFAULT = 30;

    /**
     * Margin bersih minimum seluruh kontrak B2B.
     */
    public const MARGIN_FLOOR = 15.0;

    /**
     * Jumlah pengajuan yang menunggu tanda tangan digital.
     */
    public const PENDING_COUNT = 3;

    /**
     * Total nilai kontrak aktif yang masih pending otorisasi.
     */
    public const PENDING_VALUE = 385_000_000;

    /**
     * Sesi tanda tangan digital yang masih aktif.
     */
    public const SESSION_TIMEOUT = '14:32';

    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => DirectorDashboardData::operator($user),
            'header' => self::header(),
            'cards' => self::cards(),
            'queue' => self::queue(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function header(): array
    {
        return [
            'eyebrow' => 'Persetujuan Kontrak',
            'title' => 'Persetujuan Kontrak Khusus, Diskon Volume & Perpanjangan TOP B2B',
            'subtitle' => 'Antrean tunggal seluruh pengajuan yang memerlukan tanda tangan digital Direktur Utama: diskon di luar plafon, ekstensi TOP, dan kontrak induk tahunan.',
            'period' => 'PERIODE: OKTOBER 2026',
            'filter_label' => 'Filter',
        ];
    }

    /**
     * Kartu ringkasan antrean otorisasi.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function cards(): array
    {
        $rows = self::rows();

        return [
            [
                'key' => 'pending',
                'label' => 'Pengajuan Pending',
                'value' => self::count($rows).' Pengajuan',
                'tone' => 'warning',
                'icon' => 'clock',
                'chip' => 'GATE L4',
                'chip_tone' => 'warning',
                'meta' => 'Menunggu tanda tangan digital',
                'meta_tone' => 'warning',
            ],
            [
                'key' => 'value',
                'label' => 'Nilai Kontrak Pending',
                'value' => self::amount(array_sum(array_column($rows, 'value'))),
                'tone' => 'ink',
                'icon' => 'banknote',
                'chip' => 'TIER-1',
                'chip_tone' => 'accent',
                'meta' => 'Seluruh nilai Needs Directors',
                'meta_tone' => 'muted',
            ],
            [
                'key' => 'exception',
                'label' => 'Pelanggaran Plafon',
                'value' => self::count(self::exceptionRows()).' Pengajuan',
                'tone' => 'danger',
                'icon' => 'alert-triangle',
                'chip' => 'ESCALATED',
                'chip_tone' => 'danger',
                'meta' => 'Di atas plafon diskon atau TOP default',
                'meta_tone' => 'danger',
            ],
            [
                'key' => 'session',
                'label' => 'Sesi Tanda Tangan',
                'value' => self::SESSION_TIMEOUT,
                'tone' => 'success',
                'icon' => 'lock',
                'chip' => 'HSM OK',
                'chip_tone' => 'success',
                'meta' => 'HSM RSA-4096 // SHA-256 aktif',
                'meta_tone' => 'success',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function queue(): array
    {
        $rows = self::rows();
        $filters = self::filters();

        return [
            'badge' => 'PERLU OTORISASI DIREKTUR',
            'rule' => 'PRD SEC 16 & 20: TIER-1 APPROVAL GATE',
            'clearance' => 'CLEARANCE: DIRUT ONLY',
            'title' => 'Daftar Pengajuan Kontrak Khusus & Diskon Volume B2B',
            'description' => 'Perubahan harga di luar plafon standar (>5%), perpanjangan tenor TOP (>30 hari), dan kuota pasokan prioritas memerlukan otorisasi kriptografi Direktur Utama (UU No. 2/1981 & ISO 27001).',
            'columns' => self::columns(),
            'rows' => $rows,
            'filters' => $filters,
            'total_value' => array_sum(array_column($rows, 'value')),
            'total_value_label' => self::amount(array_sum(array_column($rows, 'value'))),
            'total_note' => 'TOTAL NILAI KONTRAK AKTIF PENDING: '.self::amount(array_sum(array_column($rows, 'value'))).' ('.self::count($rows).' Pengajuan)',
            'hsm' => 'HSM RSA-4096 Terhubung',
            'timeout' => 'Sesi Timeout: '.self::SESSION_TIMEOUT,
            'batch_approve' => 'Approve Terpilih (Batch)',
            'batch_select_all' => 'Pilih Semua',
            'detail_label' => 'Detail PKS',
            'revise_label' => 'Minta Revisi',
            'reject_label' => 'Tolak',
            'audit_note' => 'Semua pengesahan akan ditandatangani otomatis dengan Sertifikat Elektronik BSrE / Hash Kriptografi SHA-256 dan tercatat di immutable log.',
            'shown_label' => 'Menampilkan '.self::count($rows).' dari '.self::count($rows).' Pengajuan Pending',
            'active_contracts' => self::count($rows),
            'search_placeholder' => 'Cari klien, kontrak, atau komoditas...',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function columns(): array
    {
        return [
            'ID KONTRAK & PEMOHON',
            'KOMODITAS & VOLUME',
            'KETENTUAN HARGA & TOP',
            'ANALISIS MARGIN',
            'SKOR KREDIT',
            'AKSI OTORISASI DIREKTUR',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function filters(): array
    {
        $rows = self::rows();

        return [
            ['key' => 'all', 'label' => 'Semua Pending', 'count' => self::count($rows)],
            ['key' => 'discount', 'label' => 'Diskon Khusus', 'count' => self::count(self::rowsByKey('discount'))],
            ['key' => 'top', 'label' => 'Ekstensi TOP', 'count' => self::count(self::rowsByKey('top'))],
            ['key' => 'annual', 'label' => 'Kontrak Induk Tahunan', 'count' => self::count(self::rowsByKey('annual'))],
        ];
    }

    /**
     * Antrean pengajuan kontrak yang menunggu otorisasi Direktur Utama.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function rows(): array
    {
        return [
            [
                'contract' => 'CTR-B2B-2025-089',
                'region' => 'BOGOR',
                'client' => 'Hotel Grand Pangrango',
                'client_tone' => 'caution',
                'submitted_by' => 'Tim Sales Key Account',
                'submitted_at' => '10 Okt 2026',
                'commodity' => 'Selada Romaine Super',
                'volume' => '800 kg / minggu (3.2 Ton/Bln)',
                'segment' => 'Grade A - Hydroponik Binaan',
                'price' => 'Rp 13.800 / kg',
                'price_strike' => 'Rp 15.000',
                'list_price' => 15_000,
                'offer_price' => 13_800,
                'discount_percent' => 8.0,
                'discount_ceiling' => self::DISCOUNT_CEILING,
                'exception' => 'Diskon Khusus: 8.0% (Plafon 5%)',
                'exception_tone' => 'caution',
                'term' => 'TOP 30 Hari',
                'top_days' => 30,
                'top_default' => self::TOP_DEFAULT,
                'margin' => '18.5% Net Margin',
                'margin_value' => 18.5,
                'margin_minimum' => self::MARGIN_FLOOR,
                'margin_delta' => 3.5,
                'margin_flag' => 'Safe Margin Pass (+3.5%)',
                'score' => 'A- (82/100)',
                'score_note' => '0 Riwayat Macet // 18 bln',
                'approve_label' => 'Setujui Kontrak',
                'approve_tone' => 'solid',
                'filter_key' => 'discount',
                'value' => 145_000_000,
                'value_label' => self::amount(145_000_000),
                'exceeds_plafon' => true,
            ],
            [
                'contract' => 'CTR-B2B-2025-092',
                'region' => 'JAKARTA PUSAT',
                'client' => 'PT Boga Rasa Kulina Prima',
                'client_tone' => 'success',
                'submitted_by' => 'VP Commercial',
                'submitted_at' => '12 Okt 2026',
                'commodity' => 'Beras Slyp & Bumbu Basah',
                'volume' => 'Plafon Transaksi: Rp 150.000.000',
                'segment' => 'Distribusi Multi-Resto Chain (14 Outlet)',
                'price' => 'Harga Standard List Katalog',
                'price_strike' => null,
                'list_price' => null,
                'offer_price' => null,
                'discount_percent' => null,
                'discount_ceiling' => null,
                'exception' => 'Usulan Ekstensi: TOP 45 Hari',
                'exception_tone' => 'caution',
                'term' => 'Reguler Default 30 Hari',
                'top_days' => 45,
                'top_default' => self::TOP_DEFAULT,
                'margin' => '21.2% Net Margin',
                'margin_value' => 21.2,
                'margin_minimum' => self::MARGIN_FLOOR,
                'margin_delta' => 6.2,
                'margin_flag' => 'High Yield Account (+6.2%)',
                'score' => 'A+ (88/100)',
                'score_note' => 'Jaminan Bank Garansi BNI',
                'approve_label' => 'Setujui Kontrak',
                'approve_tone' => 'solid',
                'filter_key' => 'top',
                'value' => 120_000_000,
                'value_label' => self::amount(120_000_000),
                'exceeds_plafon' => true,
            ],
            [
                'contract' => 'CTR-B2B-2025-095',
                'region' => 'SOEKARNO-HATTA HUB',
                'client' => 'PT Aerofood ACS Indonesia',
                'client_tone' => 'solid',
                'submitted_by' => 'Directorate Key Account',
                'submitted_at' => '13 Okt 2026',
                'commodity' => 'Sayuran Cut & Clean Hygienic',
                'volume' => '300 kg / hari (Daily Reefer Delivery)',
                'segment' => 'Standard Inflight Catering HACCP',
                'price' => 'Kontrak Tahunan Terikat (12 Bln)',
                'price_strike' => null,
                'list_price' => null,
                'offer_price' => null,
                'discount_percent' => null,
                'discount_ceiling' => null,
                'exception' => 'Harga Fixed Index + Eskalasi BBM',
                'exception_tone' => 'neutral',
                'term' => 'TOP 30 Hari via Virtual Account BNI',
                'top_days' => 30,
                'top_default' => self::TOP_DEFAULT,
                'margin' => '18.0% Net Margin',
                'margin_value' => 18.0,
                'margin_minimum' => self::MARGIN_FLOOR,
                'margin_delta' => null,
                'margin_flag' => 'Strategic Pillar Enterprise',
                'score' => 'AAA (96/100)',
                'score_note' => 'BUMN Subsidiary // Zero Default',
                'approve_label' => 'TTD Digital (BSrE)',
                'approve_tone' => 'accent',
                'filter_key' => 'annual',
                'value' => 120_000_000,
                'value_label' => self::amount(120_000_000),
                'exceeds_plafon' => false,
            ],
        ];
    }

    /**
     * Baris antrean yang keluar dari plafon diskon atau tenor default.
     *
     * @param  array<int, array<string, mixed>>|null  $rows
     * @return array<int, array<string, mixed>>
     */
    private static function exceptionRows(?array $rows = null): array
    {
        $rows ??= self::rows();

        return array_values(array_filter(
            $rows,
            static fn (array $row): bool => (bool) $row['exceeds_plafon'],
        ));
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $rows
     * @return array<int, array<string, mixed>>
     */
    private static function rowsByKey(string $key, ?array $rows = null): array
    {
        $rows ??= self::rows();

        return array_values(array_filter(
            $rows,
            static fn (array $row): bool => $row['filter_key'] === $key,
        ));
    }

    /**
     * @param  array<int, mixed>  $items
     */
    private static function count(array $items): int
    {
        return count($items);
    }

    /**
     * Nilai kontrak ditulis dengan pemisah ribuan gaya Indonesia.
     */
    private static function amount(int $value): string
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }

    private static function decimal(float $value): string
    {
        return number_format($value, 1, '.', '');
    }
}
