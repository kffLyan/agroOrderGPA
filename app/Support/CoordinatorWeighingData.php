<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data konsol "Penimbangan Aktual & Validasi Sortir" untuk Koordinator
 * Lapangan: tera massa netto riil, grading sortir afkir, dan hard-gate
 * penerbitan Surat Jalan (PRD Rule 04 & Rule 05).
 */
class CoordinatorWeighingData
{
    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => CoordinatorDashboardData::operator($user),
            'header' => self::header(),
            'stages' => self::stages(),
            'rules' => self::rules(),
            'order' => self::order(),
            'terminal' => self::terminal(),
            'grading' => self::grading(),
            'evidence' => self::evidence(),
            'reconciliation' => self::reconciliation(),
            'financial' => self::financial(),
            'audit' => self::audit(),
            'actions' => self::actions(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function header(): array
    {
        return [
            'eyebrow' => 'Sub-05 // Timbangan & Sortir',
            'title_before' => 'Konsol Penimbangan Aktual & Validasi Sortir',
            'title_after' => 'Gudang (Koordinator Lapangan)',
            'subtitle' => 'Terminal pencatatan tera massa netto riil, kompensasi grading daun, dan penguncian verifikasi legal Surat Jalan.',
            'timestamp_label' => 'TIMESTAMP TERA / UTC+7',
            'timestamp_value' => '25 SEP 2026, 05.22.37 UTC',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function stages(): array
    {
        return [
            [
                'key' => 'harvest',
                'step' => 'TAHAP 1: SELESAI',
                'title_before' => 'Preparasi Panen',
                'title_after' => null,
                'note' => 'Target Petik: 800.00 kg',
                'icon' => 'leaf',
                'state' => 'done',
            ],
            [
                'key' => 'weighing',
                'step' => 'TAHAP 2: AKTIF SEKARANG',
                'title_before' => 'Timbangan Netto &',
                'title_after' => 'Grading',
                'note' => 'Toleransi Variansi ±2.0%',
                'icon' => 'scale',
                'state' => 'active',
            ],
            [
                'key' => 'delivery',
                'step' => 'TAHAP 3: TERKUNCI HARD-GATE',
                'title_before' => 'Penerbitan Surat Jalan',
                'title_after' => '(DO)',
                'note' => 'Menunggu Lock Koordinator',
                'icon' => 'truck',
                'state' => 'locked',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function rules(): array
    {
        return [
            [
                'key' => 'rule-04',
                'badge' => 'PRD RULE 04 MANDAT',
                'meta' => 'Klausul Order Quantity vs Actual Weight',
                'icon' => 'file-text',
                'tone' => 'accent',
                'segments' => [
                    ['text' => 'Kuantitas PO (800 kg) adalah estimasi alokasi. Tagihan & DO wajib merujuk secara mutlak pada ', 'tone' => 'strong'],
                    ['text' => 'Actual Net Weight hasil timbangan stasiun', 'tone' => 'link'],
                    ['text' => ' pasca sortir.', 'tone' => 'strong'],
                ],
            ],
            [
                'key' => 'rule-05',
                'badge' => 'PRD RULE 05 HARD-GATE',
                'meta' => 'DISPATCH LOCK SYSTEM',
                'icon' => 'alert-triangle',
                'tone' => 'danger',
                'segments' => [
                    ['text' => 'Surat Jalan ', 'tone' => 'strong'],
                    ['text' => 'TIDAK DAPAT DITERBITKAN', 'tone' => 'danger'],
                    ['text' => ' sebelum Actual Net Weight divalidasi dan dikunci permanen oleh Koordinator Lapangan bersertifikasi.', 'tone' => 'strong'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function order(): array
    {
        return [
            'code' => '#ORD-GPA-202610-0042',
            'client' => 'Katering Berkah Mandiri',
            'contract' => 'HORECA CONTRACT',
            'commodity' => 'Komoditas: Selada Romaine Grade A Super (Hydroponic Heavy Leaf)',
            'estimate_label' => 'ESTIMASI AWAL PO',
            'estimate' => 800.00,
            'rate_label' => 'TARIF KONTRAK',
            'rate' => 'Rp 15.000',
            'rate_unit' => '/kg',
            'verifier_label' => 'VERIFIKATOR SAH',
            'verifier' => 'Ahmad S.',
            'verifier_code' => '[OP-0941]',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function terminal(): array
    {
        return [
            'title' => 'Terminal Input Penimbangan Massa Fisik',
            'device' => 'METTLER TOLEDO C3500 // IOT TELEMETRY BLUETOOTH',
            'iot_badge' => 'IOT CONNECTED: STABLE 0.005g',
            'sensor_id' => 'INDICATOR SENSOR: TERA-CAL-2026',
            'calibration' => 'ZERO CALIBRATED // AUTO-TARE ON',
            'readings' => [
                [
                    'key' => 'gross',
                    'label' => 'GROSS WEIGHT (KOTOR)',
                    'unit' => 'KILOGRAM (KG)',
                    'value' => 835.00,
                    'tone' => 'plain',
                ],
                [
                    'key' => 'tare',
                    'label' => 'TARE CRATE (WADAH)',
                    'unit' => '20 CRATES @ 2.00 KG',
                    'value' => 40.00,
                    'tone' => 'plain',
                ],
                [
                    'key' => 'net',
                    'label' => 'ACTUAL NET WEIGHT',
                    'unit' => 'KG NETTO RIIL',
                    'value' => 795.00,
                    'tone' => 'highlight',
                ],
            ],
            'inputs' => [
                [
                    'key' => 'gross',
                    'label' => 'MASSA GROSS (TIMBANGAN)',
                    'value' => '835.00',
                    'unit' => 'KG',
                    'tone' => 'plain',
                ],
                [
                    'key' => 'crates',
                    'label' => 'JUMLAH KERANJANG TERA',
                    'value' => '20',
                    'unit' => 'PETI',
                    'tone' => 'plain',
                ],
                [
                    'key' => 'tare_each',
                    'label' => 'BOBOT TARA BAKU / PETI',
                    'value' => '2.00',
                    'unit' => 'KG',
                    'tone' => 'muted',
                ],
            ],
            'actions' => [
                ['key' => 'reset-zero', 'label' => 'RESET ZERO (TARE)'],
                ['key' => 're-read', 'label' => 'RE-READ SENSOR'],
            ],
            'metrology_note' => 'TERA DINAS METROLOGI: VALID S.D. NOV 2026',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function grading(): array
    {
        return [
            'title' => 'Form Quality Control & Grading Sortir Fisik',
            'device' => 'VERIFIKASI FISIK MUTU, KADAR AIR, DAN DAUN AFKIR',
            'standard' => 'STANDAR HORECA GPA-01',
            'columns' => [
                'KATEGORI GRADING',
                'MASSA BERSIH',
                'PERSENTASE',
                'STATUS EVALUASI',
                'TINDAKAN LOGISTIK',
            ],
            'rows' => [
                [
                    'key' => 'grade-a',
                    'name' => 'Grade A Super Horeca',
                    'note' => ['Kerapatan krop', 'padat, renyah,', 'tanpa busuk'],
                    'mass' => 795.00,
                    'percent' => '99.375%',
                    'status' => ['LOLOS', 'PACKING'],
                    'action' => ['Muat ke', 'Box', 'Ekspedisi'],
                    'tone' => 'success',
                ],
                [
                    'key' => 'afkir',
                    'name' => 'Daun Kuning / Afkir Petik',
                    'note' => ['Penyortiran', 'helai luar layu', '& kotor'],
                    'mass' => 5.00,
                    'percent' => '0.625%',
                    'status' => ['DIBUANG', 'SUSUT'],
                    'action' => ['Kompos /', 'Pakan', 'Ternak'],
                    'tone' => 'warning',
                ],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function evidence(): array
    {
        return [
            'title' => 'Bukti Fisik Tera Timbangan & QC Daun',
            'description' => 'Ambil foto display timbangan Mettler Toledo dan sampel daun segar di stasiun QC.',
            'action' => 'UNGGAH SAMPLE BUKTI',
            'icon' => 'upload',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function reconciliation(): array
    {
        return [
            'title' => 'Rekonsiliasi Sumber Pasokan',
            'match' => 'PO MATCH: 100%',
            'sources' => [
                [
                    'key' => 'farmer',
                    'name' => 'Petani Binaan Kebun C (Cikole)',
                    'batch' => 'BATCH: HDP-SUB-2026-081',
                    'mass' => '500.00 kg',
                    'percent' => '62.89% TOTAL',
                ],
                [
                    'key' => 'buffer',
                    'name' => 'Buffer Stock Mitra Lembang',
                    'batch' => 'BATCH: BUF-LBG-2026-014',
                    'mass' => '295.00 kg',
                    'percent' => '37.11% TOTAL',
                ],
            ],
            'panel' => [
                ['label' => 'Estimasi Awal PO:', 'value' => '800.00 kg', 'tone' => 'ink'],
                ['label' => 'Realisasi Timbangan Bersih:', 'value' => '795.00 kg', 'tone' => 'success'],
                ['label' => 'Selisih Susut Sortir:', 'value' => '-5.00 kg (-0.625%)', 'tone' => 'warning', 'multiline' => true],
            ],
            'status' => 'STATUS: MASUK TOLERANSI KONTRAK (≤ 2.0%)',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function financial(): array
    {
        return [
            'title' => 'Perhitungan Finansial Otomatis',
            'meta' => 'AUTO-BILL BINDING REKONSILIASI',
            'icon' => 'banknote',
            'rows' => [
                ['label' => 'Tarif Dasar Kontrak Horeca', 'value' => 'Rp 15.000 / kg', 'tone' => 'ink'],
                ['label' => 'Estimasi Nilai PO (800 kg)', 'value' => 'Rp 12.000.000', 'tone' => 'muted-strike'],
                ['label' => 'Kompensasi Sortir Afkir (5 kg)', 'value' => '- Rp 75.000', 'tone' => 'warning'],
            ],
            'final_label' => 'NILAI TRANSAKSI FINAL TERKOREKSI',
            'final_value' => 'Rp 11.925.000',
            'final_note' => 'Dasar mutlak pencetakan Faktur Invoice Komersial & DO Resmi Katering Berkah Mandiri.',
            'metrology' => [
                ['label' => 'METROLOGY HASH:', 'value' => 'SHA256: 4e91...bf20'],
                ['label' => 'STATION OPERATOR:', 'value' => 'Ahmad Syaifullah (ID: OP-0941)'],
                ['label' => 'SECURITY STATUS:', 'value' => 'ENCRYPTED TELEMETRY STREAM'],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function audit(): array
    {
        return [
            'title' => 'INTEGRITAS AUDIT FORENSIK GPA',
            'body' => 'Data massa timbangan aktual ini dienkripsi dengan signature perangkat Mettler Toledo. Setiap perubahan manual setelah penguncian akan memicu pembatalan otomatis sertifikat dispatch.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function actions(): array
    {
        return [
            'confirm_title' => 'VALIDASI BERAT NETTO RIIL : 795.00 KG',
            'confirm_note' => 'Tindakan ini akan mengunci data timbangan & membuka kunci penerbitan Surat Jalan DO.',
            'draft' => ['key' => 'draft', 'label' => 'SIMPAN DRAF TIMBANGAN', 'icon' => 'save'],
            'lock' => ['key' => 'lock', 'label' => 'KUNCI & VALIDASI BERAT AKTUAL (LOCK DATA)', 'icon' => 'lock'],
            'delivery' => ['key' => 'delivery', 'label' => 'TERBITKAN SURAT JALAN (SJ-GPA-202610-0001)', 'icon' => 'truck'],
        ];
    }
}
