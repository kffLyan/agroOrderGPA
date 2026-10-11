<?php

namespace App\Support;

use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * Sumber data "Piutang & Tagihan" (buku besar faktur tempo dan risiko kredit).
 *
 * PRD Rule 11, 15.3 & 15.6: penerbitan Surat Jalan baru wajib melewati
 * verifikasi dua arah oleh Sekretaris & Finance, pembekuan PO dipicu otomatis
 * saat faktur tempo menunggak lebih dari ambang 7 hari, dan utilisasi plafon
 * kredit tidak boleh melewati 100% limit yang disahkan Direktur.
 *
 * Seluruh angka diturunkan dari satu nilai total piutang sehingga pita KPI,
 * matriks umur piutang, buku besar faktur, dan kolektibilitas portofolio
 * selalu saling cocok.
 */
class DirectorReceivablesData
{
    /**
     * Tanggal posisi buku besar yang menjadi acuan seluruh sisa hari tempo.
     */
    public const AS_OF = '2026-10-24';

    /**
     * Total piutang B2B berjalan pada periode Oktober 2026.
     */
    public const TOTAL_RECEIVABLE = 248_500_000;

    /**
     * Jumlah kontrak aktif yang dipantau Direksi.
     */
    public const ACTIVE_CONTRACTS = 18;

    /**
     * Ambang keterlambatan yang memicu pembekuan PO otomatis (Rule 15.3).
     */
    public const FREEZE_THRESHOLD_DAYS = 7;

    /**
     * Plafon utilisasi maksimum sebelum Surat Jalan ditolak (Rule 15.6).
     */
    public const PLAFON_CAP = 100.0;

    /**
     * Antrean verifikasi giro dan RTGS yang menunggu Finance.
     */
    public const PENDING_INVOICES = 3;

    /**
     * Total nilai kliring yang belum tercocokkan dua arah.
     */
    public const PENDING_CLEARING = 78_400_000;

    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => DirectorDashboardData::operator($user),
            'header' => self::header(),
            'cards' => self::kpiCards(),
            'aging' => self::aging(),
            'ledger' => self::ledger(),
            'policy' => self::policy(),
            'verification' => self::verification(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function header(): array
    {
        return [
            'eyebrow' => 'Piutang & Tagihan',
            'title' => 'Monitoring Piutang, Tagihan Tempo & Manajemen Risiko Kredit Klien B2B',
            'subtitle' => 'Pengawasan buku besar faktur konsolidasi (TOP 14/30/45 Hari), utilisasi plafon kredit klien korporat, evaluasi umur piutang (Aging AR), dan mitigasi risiko gagal bayar.',
            'period' => 'PERIODE: OKTOBER 2026',
            'filter_label' => 'Filter',
        ];
    }

    /**
     * Empat kartu KPI risiko kredit.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function kpiCards(): array
    {
        $aging = self::aging();
        $stages = $aging['stages'];

        $lancar = $stages[0]['value'] + $stages[1]['value'];
        $warning = $stages[2]['value'];
        $kritis = $stages[3]['value'];

        return [
            [
                'key' => 'total',
                'label' => 'TOTAL PIUTANG B2B',
                'value' => 'Rp '.number_format(self::TOTAL_RECEIVABLE, 0, '.', '.'),
                'tone' => 'ink',
                'icon' => 'invoice',
                'chip' => '100% TERMONITOR',
                'chip_tone' => 'accent',
                'meta' => self::ACTIVE_CONTRACTS.' Klien Kontrak Aktif',
                'meta_tone' => 'muted',
            ],
            [
                'key' => 'lancar',
                'label' => 'PIUTANG LANCAR (< 15 HARI)',
                'value' => 'Rp '.number_format($lancar, 0, '.', '.'),
                'tone' => 'success',
                'icon' => 'check-circle',
                'chip' => 'NORMAL / AMAN',
                'chip_tone' => 'neutral',
                'meta' => self::percent($lancar).'% DARI TOTAL AR',
                'meta_tone' => 'success',
            ],
            [
                'key' => 'warning',
                'label' => 'WARNING TEMPO (< '.self::FREEZE_THRESHOLD_DAYS.' HARI)',
                'value' => 'Rp '.number_format($warning, 0, '.', '.'),
                'tone' => 'warning',
                'icon' => 'clock',
                'chip' => 'FOLLOW-UP SEKRE',
                'chip_tone' => 'warning',
                'meta' => self::percent($warning).'% • 5 Faktur Jatuh Tempo',
                'meta_tone' => 'warning',
            ],
            [
                'key' => 'kritis',
                'label' => 'OVERDUE KRITIS (> 30 HARI)',
                'value' => 'Rp '.number_format($kritis, 0, '.', '.'),
                'tone' => 'danger',
                'icon' => 'alert-triangle',
                'chip' => 'AUTO-FREEZE AKTIF',
                'chip_tone' => 'danger',
                'meta' => self::percent($kritis).'% • 1 Klien Terkunci',
                'meta_tone' => 'danger',
            ],
        ];
    }

    /**
     * Matriks analisis umur piutang beserta bar komposit siklus penagihan.
     *
     * @return array<string, mixed>
     */
    public static function aging(): array
    {
        $definitions = [
            ['stage' => 'STAGE 01 (0-15 HARI)', 'status' => 'SANGAT AMAN', 'value' => 120_000_000, 'tone' => 'brand'],
            ['stage' => 'STAGE 02 (16-30 HARI)', 'status' => 'IN-SCHEDULE', 'value' => 65_300_000, 'tone' => 'success'],
            ['stage' => 'STAGE 03 (1-14 HARI LEWAT)', 'status' => 'WARNING', 'value' => 45_200_000, 'tone' => 'warning'],
            ['stage' => 'STAGE 04 (> 15 HARI OVERDUE)', 'status' => 'KRITIS', 'value' => 18_000_000, 'tone' => 'danger'],
        ];

        $total = array_sum(array_column($definitions, 'value'));
        $collectible = $total - $definitions[3]['value'];

        $stages = [];

        foreach ($definitions as $index => $definition) {
            $share = round($definition['value'] / $total * 100, 1);

            $stages[] = [
                'stage' => $definition['stage'],
                'status' => $definition['status'],
                'value' => $definition['value'],
                'value_label' => 'Rp '.number_format($definition['value'], 0, '.', '.'),
                'share' => $share,
                'share_label' => self::decimal($share, 1).'%',
                'tone' => $definition['tone'],
                'note' => match ($index) {
                    0 => self::decimal($share, 1).'% Total AR • In-Schedule',
                    1 => self::decimal($share, 1).'% Total AR • Menuju Tempo',
                    2 => self::decimal($share, 1).'% Total AR • Follow-up Sekre',
                    default => self::decimal($share, 1).'% Total AR • LOCKDOWN PO',
                },
            ];
        }

        return [
            'title' => 'Matriks Analisis Umur Piutang (Aging AR Schedule Breakdown)',
            'subtitle' => 'Distribusi komposit siklus penagihan berdasarkan Termin Pembayaran (TOP) dan keterlambatan real-time.',
            'collectibility_label' => 'Kolektibilitas Portofolio: '.self::decimal(round($collectible / $total * 100, 1), 1).'%',
            'total' => $total,
            'total_label' => 'Rp '.number_format($total, 0, '.', '.'),
            'collectible' => $collectible,
            'stages' => $stages,
        ];
    }

    /**
     * Buku besar faktur tempo konsolidasi per klien B2B.
     *
     * @return array<string, mixed>
     */
    public static function ledger(): array
    {
        $definitions = [
            [
                'name' => 'PT Aerofood ACS',
                'client_id' => 'B2B-ACS-091',
                'locked' => false,
                'sj_count' => 4,
                'sj_label' => 'SJ-088 s/d SJ-091',
                'outstanding' => 114_200_000,
                'verification' => 'TERVERIFIKASI FINANCE',
                'verification_tone' => 'success',
                'top_days' => 45,
                'due_date' => '2026-11-15',
                'limit' => 500_000_000,
                'exposure' => 314_000_000,
                'actions' => ['Detail Faktur', 'Kunci Plafon'],
            ],
            [
                'name' => 'Royal Ambarrukmo Hotel',
                'client_id' => 'B2B-RAH-042',
                'locked' => false,
                'sj_count' => 3,
                'sj_label' => 'SJ-064, SJ-071, SJ-079',
                'outstanding' => 59_550_000,
                'verification' => 'BUTUH KONFIRMASI WA',
                'verification_tone' => 'warning',
                'top_days' => 30,
                'due_date' => '2026-10-21',
                'limit' => 200_000_000,
                'exposure' => 120_000_000,
                'actions' => ['Kirim Reminder WA', 'Kunci Plafon'],
            ],
            [
                'name' => 'PT Boga Rasa Kulina Prima',
                'client_id' => 'B2B-BRK-108',
                'locked' => false,
                'sj_count' => 2,
                'sj_label' => 'SJ-082, SJ-085',
                'outstanding' => 56_750_000,
                'verification' => 'KLIRING AMAN',
                'verification_tone' => 'success',
                'top_days' => 30,
                'due_date' => '2026-11-08',
                'limit' => 150_000_000,
                'exposure' => 95_000_000,
                'actions' => ['Detail Faktur', 'Kunci Plafon'],
            ],
            [
                'name' => 'PT Segar Makmur Ritelindo',
                'client_id' => 'B2B-SMR-019',
                'locked' => true,
                'sj_count' => 5,
                'sj_label' => 'SJ-032 s/d SJ-036',
                'outstanding' => 18_000_000,
                'verification' => 'DEFISIT PLAFON',
                'verification_tone' => 'danger',
                'top_days' => 14,
                'due_date' => '2026-10-12',
                'limit' => 100_000_000,
                'exposure' => 105_000_000,
                'actions' => ['Restrukturisasi Tagihan', 'Lepas Freeze'],
            ],
        ];

        $rows = [];
        $total = 0;

        foreach ($definitions as $definition) {
            $total += $definition['outstanding'];

            $days = (int) CarbonImmutable::parse(self::AS_OF)
                ->diff(CarbonImmutable::parse($definition['due_date']), false)
                ->format('%r%a');

            $utilisation = round($definition['exposure'] / $definition['limit'] * 100, 1);
            $overLimit = $utilisation > self::PLAFON_CAP;

            $rows[] = [
                'name' => $definition['name'],
                'client_id' => 'ID-KLIEN:'.$definition['client_id'],
                'client_code' => $definition['client_id'],
                'locked' => $definition['locked'],
                'sj_count' => $definition['sj_count'],
                'sj_count_label' => $definition['sj_count'].' Surat Jalan',
                'sj_label' => $definition['sj_label'],
                'outstanding' => $definition['outstanding'],
                'outstanding_label' => 'Rp '.number_format($definition['outstanding'], 0, '.', '.'),
                'verification' => $definition['verification'],
                'verification_tone' => $definition['verification_tone'],
                'top_days' => $definition['top_days'],
                'top_label' => 'TOP '.$definition['top_days'].' HARI',
                'due_date' => CarbonImmutable::parse($definition['due_date']),
                'due_label' => self::dateLabel(CarbonImmutable::parse($definition['due_date'])),
                'days_remaining' => $days,
                'days_label' => self::daysLabel($days),
                'status_label' => self::status($days)['value'],
                'status_tone' => self::status($days)['tone'],
                'limit' => $definition['limit'],
                'exposure' => $definition['exposure'],
                'limit_label' => 'Rp '.self::shortAmount($definition['exposure'])
                    .' / '.self::shortAmount($definition['limit']),
                'utilisation' => $utilisation,
                'utilisation_label' => self::decimal($utilisation, 0).'%',
                'utilisation_note' => $overLimit ? self::decimal($utilisation, 0).'% (OVER)' : self::decimal($utilisation, 0).'%',
                'utilisation_width' => min($utilisation, self::PLAFON_CAP),
                'over_limit' => $overLimit,
                'actions' => $definition['actions'],
            ];
        }

        return [
            'title' => 'Buku Besar Faktur Tempo Konsolidasi & Evaluasi Plafon Kredit Klien',
            'subtitle' => 'Audit rincian surat jalan (SJ), sisa hari jatuh tempo TOP, dan monitoring pembekuan PO otomatis sesuai batas plafon kredit.',
            'search_placeholder' => 'Cari Klien / No. SJ...',
            'filter_label' => 'Filter TOP',
            'columns' => [
                'Klien Korporat B2B',
                'Surat Jalan (SJ) Terlampir',
                'Nilai Tagihan Tertutang',
                'Termin (TOP) & Jatuh Tempo',
                'Status AR',
                'Utilisasi Plafon Kredit',
                'Tindakan Direksi',
            ],
            'rows' => $rows,
            'total' => $total,
            'total_label' => 'Rp '.number_format($total, 0, '.', '.'),
            'active_contracts' => self::ACTIVE_CONTRACTS,
            'visible_rows' => count($rows),
            'shown_label' => 'Menampilkan '.count($rows).' dari '.self::ACTIVE_CONTRACTS.' Klien Kontrak Aktif B2B',
            'page_label' => 'Halaman 1 / 5',
            'frozen_label' => 'FILTER FROZEN — AUDIT ARCHIVE',
            'frozen_chip' => '[TERKUNCI / LOCKED]',
        ];
    }

    /**
     * Kebijakan pembekuan dan otomasi sistem.
     *
     * @return array<string, mixed>
     */
    public static function policy(): array
    {
        return [
            'title' => 'Kebijakan Pembekuan & Otomasi Sistem (PRD Rule 15.3 & 15.6)',
            'chip' => 'SISTEM AKTIF',
            'rules' => [
                [
                    'rule' => 'RULE 15.3: PEMBEKUAN PO OTOMATIS',
                    'icon' => 'lock',
                    'tone' => 'danger',
                    'threshold_label' => 'OVERDUE THRESHOLD: '.self::FREEZE_THRESHOLD_DAYS.' HARI',
                    'status_label' => 'STATUS: ENFORCED',
                    'body' => 'Setiap pemesanan (PO) baru akan langsung diblokir otomatis oleh sistem terminal jika terdapat faktur tempo yang menunggak lebih dari '.self::FREEZE_THRESHOLD_DAYS.' hari melewati batas TOP yang disepakati dalam kontrak legal.',
                ],
                [
                    'rule' => 'RULE 15.6: CEILING PLAFON KREDIT',
                    'icon' => 'gauge',
                    'tone' => 'warning',
                    'threshold_label' => 'LIMIT CAP: '.self::decimal(self::PLAFON_CAP, 1).'% PLAFON',
                    'status_label' => 'STATUS: ENFORCED',
                    'body' => 'Penerbitan Surat Jalan baru akan ditolak otomatis jika saldo tagihan berjalan ditambah nilai PO baru menghasilkan utilisasi plafon melebihi 100% dari limit yang disetujui Direktur.',
                ],
            ],
            'bypass_note' => 'Semua tindakan bypass pembekuan membutuhkan otorisasi biometrik atau PIN Direktur Operasional.',
            'bypass_action' => 'Log Otorisasi Bypass',
        ];
    }

    /**
     * Protokol verifikasi dua arah sebelum piutang dinyatakan lunas.
     *
     * @return array<string, mixed>
     */
    public static function verification(): array
    {
        return [
            'title' => 'Protokol Verifikasi (Rule 11)',
            'icon' => 'shield',
            'body' => 'Pembayaran transfer bilyet giro dan RTGS dari klien korporat wajib melalui proses pencocokan dua arah (Two-Way Reconciliation) oleh Sekretaris & Finance sebelum status piutang dinyatakan lunas.',
            'queue_label' => 'Antrean Verifikasi Masuk:',
            'queue_value' => self::PENDING_INVOICES.' Faktur',
            'clearing_label' => 'Total Nilai Kliring Pending:',
            'clearing_value' => 'Rp '.number_format(self::PENDING_CLEARING, 0, '.', '.'),
            'action' => 'Buka Antrean Verifikasi Sekre',
            'sla' => 'SLO VERIFIKASI MAKSIMAL: 1x24 JAM DARI TANGGAL MUTASI',
        ];
    }

    /**
     * Status AR diturunkan dari sisa hari terhadap tanggal jatuh tempo.
     *
     * Faktur yang menunggak lebih dari ambang Rule 15.3 dipaksa masuk status
     * pembekuan otomatis, sedangkan keterlambatan di bawah ambang hanya
     * escalated ke Secretariat.
     *
     * @return array{value: string, tone: string}
     */
    private static function status(int $days): array
    {
        if ($days < 0 && abs($days) > self::FREEZE_THRESHOLD_DAYS) {
            return ['value' => 'AUTO-FREEZE PO', 'tone' => 'danger'];
        }

        return match (true) {
            $days < 0 => ['value' => 'WARNING TEMPO', 'tone' => 'warning'],
            default => ['value' => 'LANCAR', 'tone' => 'success'],
        };
    }

    /**
     * Tanggal jatuh tempo ditulis dengan nama bulan berbahasa Indonesia.
     */
    private static function dateLabel(CarbonImmutable $date): string
    {
        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];

        return sprintf('%02d', $date->day).' '.$months[$date->month].' '.$date->year;
    }

    private static function daysLabel(int $days): string
    {
        if ($days < 0) {
            return '(! OVERDUE '.abs($days).' HARI)';
        }

        return '('.$days.' Hari Tersisa)';
    }

    private static function percent(int $value): string
    {
        return self::decimal(round($value / self::TOTAL_RECEIVABLE * 100, 1), 1);
    }

    private static function decimal(float $value, int $decimals): string
    {
        return number_format($value, $decimals, '.', '');
    }

    /**
     * Plafon kredit ditulis ringkas dalam juta rupiah pada design.
     */
    private static function shortAmount(int $value): string
    {
        if ($value % 1_000_000 === 0) {
            return intdiv($value, 1_000_000).'M';
        }

        return self::decimal($value / 1_000_000, 1).'M';
    }
}
