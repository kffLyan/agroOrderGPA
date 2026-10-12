<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data konsol "Monitoring & Validasi Proof of Delivery (PoD)".
 *
 * PRD Rule 06 & 12: pesanan dilarang ditutup menjadi "Selesai" tanpa bukti
 * dokumen fisik bertanda tangan dan stempel basah penerima serta foto serah
 * terima barang di dock. Selisih timbangan di lokasi wajib disahkan lewat
 * BA Retur Seketika sebelum ledger boleh dikunci.
 */
class CoordinatorMonitoringData
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
            'dispatch' => self::dispatch(),
            'dossier' => self::dossier(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function mandate(): array
    {
        return [
            'icon' => 'shield',
            'title' => 'PRD RULE 06 & 12: VALIDASI MUTLAK PROOF OF DELIVERY (POD)',
            'badge' => 'GATE-STATUS: ENFORCED',
            'body_lead' => 'Status pesanan dilarang ditutup menjadi',
            'body_emphasis' => '"Selesai"',
            'body_tail' => 'tanpa bukti PoD (foto dokumen fisik bertanda tangan & stempel basah penerima, serta foto serah terima barang di dock). Segala selisih timbangan di lokasi wajib disahkan via BA Retur Seketika.',
            'body' => 'Status pesanan dilarang ditutup menjadi "Selesai" tanpa bukti PoD (foto dokumen fisik bertanda tangan & stempel basah penerima, serta foto serah terima barang di dock). Segala selisih timbangan di lokasi wajib disahkan via BA Retur Seketika.',
            'protocol_label' => 'PROTOKOL KUNCI',
            'protocol_value' => 'LEDGER LOCK: ACTIVE',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function kpis(): array
    {
        return [
            [
                'key' => 'fleet',
                'label' => 'ARMADA AKTIF',
                'label_tone' => 'body',
                'value' => 4,
                'unit' => 'Armada Lapangan',
                'unit_tone' => 'body',
                'icon' => 'truck',
                'icon_tone' => 'success-deep',
                'foot_left' => 'GPS 100% ONLINE',
                'foot_left_tone' => 'success-deep',
                'foot_right' => 'CHILLER TELEMETRY LIVE',
                'foot_right_tone' => 'muted',
                'foot_dot' => true,
            ],
            [
                'key' => 'dropoff',
                'label' => 'TITIK DROPOFF SELESAI',
                'label_tone' => 'body',
                'value' => '3 / 5',
                'unit' => 'Titik (60%)',
                'unit_tone' => 'body',
                'icon' => 'map-pin',
                'icon_tone' => 'success-deep',
                'foot_left' => 'PoD VALID: 3 BERKAS',
                'foot_left_tone' => 'success-deep',
                'foot_right' => 'PROGRESS OK',
                'foot_right_tone' => 'muted',
                'foot_dot' => false,
            ],
            [
                'key' => 'enroute',
                'label' => 'DALAM PERJALANAN',
                'label_tone' => 'body',
                'value' => 2,
                'unit' => 'Armada On-Route',
                'unit_tone' => 'body',
                'icon' => 'send',
                'icon_tone' => 'success-deep',
                'foot_left' => '1 Menuju, 1 Unloading',
                'foot_left_tone' => 'body',
                'foot_right' => 'ETA ON-SCHEDULE',
                'foot_right_tone' => 'success-deep',
                'foot_dot' => false,
            ],
            [
                'key' => 'retur',
                'label' => 'KASUS RETUR / SELISIH',
                'label_tone' => 'warning-deep',
                'value' => 1,
                'unit' => 'Kasus Butuh Verifikasi',
                'unit_tone' => 'danger',
                'icon' => 'alert-triangle',
                'icon_tone' => 'danger',
                'foot_left' => 'D-8821-AB: -10 KG MEMAR',
                'foot_left_tone' => 'danger',
                'foot_right' => 'BA WAJIB',
                'foot_right_tone' => 'warning-caution',
                'foot_dot' => false,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function dispatch(): array
    {
        return [
            'title' => 'Log Status Pengiriman & Armada Lapangan',
            'subtitle' => '3 Perjalanan Terjadwal Sentra Subang Hari Ini',
            'badge' => 'LIVE DISPATCH',
            'columns' => [
                'ARMADA / SUPIR',
                'NO. SJ & DESTINASI',
                'MUATAN',
                'STATUS EKSEKUSI',
                'AKSI OPERASIONAL',
            ],
            'rows' => [
                [
                    'sj' => 'SJ-GPA-2025-0811',
                    'driver' => 'Budi Santoso',
                    'vehicle' => 'Engkel Box B-9102-SDA',
                    'arrival' => 'Tiba: 06:15 WIB',
                    'arrival_tone' => 'success-deep',
                    'customer' => 'Katering Berkah Mandiri',
                    'customer_tone' => 'body',
                    'destination' => 'Sentul Barat (Drop #2)',
                    'cargo_kg' => 795.0,
                    'commodity' => 'Selada Romaine',
                    'manifest_kg' => null,
                    'discrepancy' => null,
                    'status' => 'PROSES SERAH TERIMA / UNLOADING',
                    'status_tone' => 'accent',
                    'dossier_ref' => null,
                    'row_tone' => 'default',
                    'actions' => [
                        ['key' => 'call-driver', 'label' => 'Hubungi Driver', 'icon' => 'phone', 'variant' => 'outline'],
                        ['key' => 'view-sj', 'label' => 'Lihat Surat Jalan', 'icon' => 'file-text', 'variant' => 'soft'],
                    ],
                ],
                [
                    'sj' => 'SJ-GPA-2025-0809',
                    'driver' => 'Hendra Wijaya',
                    'vehicle' => 'Blind Van B-9821-KBA',
                    'arrival' => 'Tiba: 05:40 WIB',
                    'arrival_tone' => 'success-deep',
                    'customer' => 'PT Aerofood ACS',
                    'customer_tone' => 'strong',
                    'destination' => 'Bandara Soetta (Cargo 1)',
                    'cargo_kg' => 442.5,
                    'commodity' => 'Baby Bok Choy & Kale',
                    'manifest_kg' => null,
                    'discrepancy' => null,
                    'status' => "SELESAI \u{2014} POD TERVERIFIKASI",
                    'status_tone' => 'accent',
                    'dossier_ref' => 'POD-DOC-SUB4-0809-ACS',
                    'row_tone' => 'active',
                    'actions' => [
                        ['key' => 'inspect-pod', 'label' => 'Periksa PoD', 'icon' => 'eye', 'variant' => 'primary'],
                        ['key' => 'download-proof', 'label' => 'Unduh Bukti', 'icon' => 'download', 'variant' => 'outline'],
                    ],
                ],
                [
                    'sj' => 'SJ-GPA-2025-0814',
                    'driver' => 'Agus Prasetyo',
                    'vehicle' => 'Pickup Box D-8821-AB',
                    'arrival' => 'Tiba: 07:05 WIB',
                    'arrival_tone' => 'warning-deep',
                    'customer' => 'Restoran Padang Sederhana',
                    'customer_tone' => 'body',
                    'destination' => 'Selisih: 10 kg afkir basah',
                    'cargo_kg' => 240.0,
                    'commodity' => 'Selada & Seledai',
                    'manifest_kg' => 250.0,
                    'discrepancy' => 'Selisih: 10 kg afkir basah',
                    'status' => '! SELESAI DENGAN CATATAN (RETUR 10 KG)',
                    'status_tone' => 'danger',
                    'dossier_ref' => null,
                    'row_tone' => 'default',
                    'actions' => [
                        ['key' => 'open-retur', 'label' => 'Buka Lap. Retur', 'icon' => 'refresh', 'variant' => 'danger'],
                        ['key' => 'view-ba', 'label' => 'Detail BA', 'icon' => 'clipboard', 'variant' => 'soft'],
                    ],
                ],
            ],
            'default_sj' => 'SJ-GPA-2025-0809',
            'dropoff_total' => 5,
            'dropoff_base' => 3,
            'footer_left' => 'RECALCULATING ROUTE OPTIMIZATION SUBANG \u{2192} JKT',
            'footer_right' => 'ENCRYPTED TELEMETRY HASH: 9a8c1f0092d',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function dossier(): array
    {
        return [
            'title' => 'Verifikasi Berkas PoD Masuk',
            'subtitle' => 'Inspeksi Fisik Dokumen & Otorisasi Penerimaan',
            'badge' => 'DOKUMEN SIAP',
            'icon' => 'file-text',
            'reference_label' => 'NO. REFERENSI DOKUMEN',
            'sj_label' => 'SURAT JALAN ASOSIASI',
            'checks_label' => 'PARAMETER CEK MANDATORI PRD (WAJIB TERPENUHI SEMUA)',
            'checks' => [
                ['key' => 'signature', 'label' => 'Ttd Asli Penerima (Bu Siska)'],
                ['key' => 'stamp', 'label' => 'Stempel Cap Basah PT/Instansi'],
                ['key' => 'weight', 'label' => 'Bobot Netto Pas'],
                ['key' => 'gps', 'label' => 'Radius GPS'],
            ],
            'notes_label' => 'CATATAN KOORDINATOR GUDANG (DISIMPAN DI AUDIT TRAIL)',
            'decision_label' => 'STATUS PUTUSAN:',
            'decision' => [
                ['key' => 'approved', 'label' => 'APPROVED (VALID)', 'tone' => 'success-deep'],
                ['key' => 'reject', 'label' => 'REJECT (DITOLAK)', 'tone' => 'danger'],
            ],
            'cta' => [
                'key' => 'validate-pod',
                'label' => 'VALIDASI PoD & TUTUP PESANAN',
                'icon' => 'badge-check',
            ],
            'buttons' => [
                ['key' => 'save-draft', 'label' => 'SIMPAN DRAFT', 'icon' => 'save', 'variant' => 'outline'],
                ['key' => 'escalate-audit', 'label' => 'ESKALASI TIM AUDIT', 'icon' => 'alert-triangle', 'variant' => 'danger'],
            ],
            'empty_title' => 'Berkas PoD Belum Masuk',
            'empty_body' => 'Dokumen fisik dan foto dock untuk surat jalan ini belum diunggah pengirim. Ledger tetap terkunci sampai berkas lengkap masuk.',
            'entries' => [
                'SJ-GPA-2025-0809' => [
                    'ref' => 'POD-DOC-SUB4-0809-ACS',
                    'sj' => 'SJ-GPA-2025-0809',
                    'recipient' => 'Bu Siska (PT Aerofood ACS)',
                    'net_kg' => 442.5,
                    'gps_radius_m' => 12.0,
                    'gps_tolerance_m' => 20.0,
                    'exhibits' => [
                        [
                            'code' => 'EKSIBIT 1: DOKUMEN SJ',
                            'meta' => '300 DPI',
                            'icon' => 'file-text',
                            'caption' => 'CAP STEMPEL: BASAH QC ACS',
                            'status' => 'TERBACA',
                        ],
                        [
                            'code' => 'EKSIBIT 2: DOCK / CHILLER',
                            'meta' => "+3.8\u{00B0}C",
                            'icon' => 'truck',
                            'caption' => 'GEO: -6.1265, 106.6542',
                            'status' => 'TOL: 12M',
                        ],
                    ],
                    'checks' => [
                        'signature' => true,
                        'stamp' => true,
                    ],
                    'notes' => 'Surat jalan fisik telah diverifikasi jelas. Stempel basah QC Aerofood ACS terbaca utuh. Timbangan 442.5 kg sesuai manifes timbangan awal.',
                ],
            ],
        ];
    }
}
