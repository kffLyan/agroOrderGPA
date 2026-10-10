<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data konsol "Penerbitan Surat Jalan & Dispatch Armada Logistik".
 *
 * Menjaga rantai integritas netto (Rule 05): Surat Jalan hanya terbit dari
 * timbangan yang sudah terkunci, lengkap dengan penugasan armada dan supir.
 */
class CoordinatorDispatchData
{
    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => CoordinatorDashboardData::operator($user),
            'header' => self::header(),
            'sop' => self::sop(),
            'kpis' => self::kpis(),
            'queue' => self::queue(),
            'manifest' => self::manifest(),
            'batch' => self::batch(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function header(): array
    {
        return [
            'eyebrow' => 'Sub-07 // Surat Jalan & Logistik',
            'title_before' => 'Penerbitan Surat Jalan & Dispatch',
            'title_after' => 'Armada Logistik',
            'subtitle' => 'Penerbitan dokumen legal Surat Jalan (SJ-GPA) berbasis hasil berat aktual netto yang telah dikunci (Lock Data) dan penugasan armada supir.',
            'actions' => [
                ['key' => 'log', 'label' => 'Log Dispatch Terbit', 'icon' => 'file-text', 'variant' => 'ghost'],
                ['key' => 'thermal', 'label' => 'Antrean Cetak Thermal', 'icon' => 'printer', 'variant' => 'ink', 'badge' => 'thermal'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function sop(): array
    {
        return [
            'title' => 'SOP MANDATORI AGRO-GPA RULE 05',
            'badge' => 'INTEGRITAS NETTO',
            'body' => 'Surat Jalan hanya dapat diterbitkan untuk barang yang telah melewati penimbangan netto sah dan disegel (Locked). Segala bentuk bypass tera timbangan digital akan memicu pembekuan gate otomatis (Lockout Code 904).',
            'cert_prefix' => 'LEGAL CERT:',
            'cert_value' => 'ISO/IEC 17025',
            'lockout_code' => '904',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function kpis(): array
    {
        return [
            [
                'key' => 'ready_docs',
                'label' => 'SURAT JALAN SIAP DISPATCH',
                'value' => 4,
                'unit' => 'Dokumen Sah',
                'unit_tone' => 'quiet',
                'value_tone' => 'ink',
                'icon' => 'file-text',
                'icon_chip' => 'accent-soft',
                'icon_tone' => 'success-deep',
                'foot_label' => 'Tonase Netto Sah:',
                'foot_value' => '1.585,00 KG',
                'foot_tone' => 'success',
            ],
            [
                'key' => 'fleet',
                'label' => 'ARMADA STANDBY DI DOCK',
                'value' => 3,
                'unit' => 'Unit Ready',
                'unit_tone' => 'quiet',
                'value_tone' => 'ink',
                'icon' => 'truck',
                'icon_chip' => 'shell',
                'icon_tone' => 'ink',
                'foot_chips' => ['Blind Van', 'Engkel Box', 'Pickup'],
            ],
            [
                'key' => 'departure',
                'label' => 'KEBERANGKATAN PERTAMA',
                'value' => '04:30',
                'unit' => 'WIB',
                'unit_tone' => 'success',
                'value_tone' => 'ink',
                'icon' => 'clock',
                'icon_chip' => 'warning-soft',
                'icon_tone' => 'warning-deep',
                'foot_label' => 'Target Pengiriman:',
                'foot_chip' => 'SLA DAWN DELIVERY',
            ],
            [
                'key' => 'scale',
                'label' => 'STATUS TERA & CETAK',
                'value' => '100%',
                'unit' => 'Sah Validated',
                'unit_tone' => 'ink',
                'value_tone' => 'success',
                'icon' => 'badge-check',
                'icon_chip' => 'accent',
                'icon_tone' => 'success-ink',
                'foot_label' => 'Zebra Driver Link:',
                'foot_chip' => 'ONLINE 203 DPI',
                'foot_dot' => true,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function queue(): array
    {
        return [
            'title' => 'Antrean Penerbitan Surat Jalan',
            'meta' => '2 ORDER MENUNGGU DOKUMEN CETAK',
            'items' => [
                [
                    'po' => 'PO #ORD-GPA-202610-0042',
                    'customer' => 'Katering Berkah Mandiri (Sentul Hub Depot)',
                    'status_chip' => 'TERKUNCI / VALID',
                    'net' => 795.00,
                    'tara' => 45.00,
                    'gross' => 840.00,
                    'driver' => 'Budi Santoso',
                    'driver_id' => 'ID-DRV-089 (Vendor GPA Log)',
                    'vehicle' => 'Engkel Box',
                    'plate' => 'B 9102 SDA',
                    'commodity' => 'Komoditas: Kubis Manis Lembang (Grade A)',
                    'dock' => 'TIMBANGAN DOCK #2',
                    'lock_stamp' => '2026-10-24 03:58 WIB',
                    'sj' => 'SJ-GPA-202610-0001',
                    'issue_label' => 'TERBITKAN & CETAK SJ-GPA-202610-0001',
                    'issued_label' => 'SJ-GPA-202610-0001 SUDAH TERBIT',
                ],
                [
                    'po' => 'PO #ORD-GPA-202610-0039',
                    'customer' => 'PT Aerofood ACS (Bandara Soekarno-Hatta)',
                    'status_chip' => 'TERKUNCI / VALID',
                    'net' => 442.50,
                    'tara' => 28.50,
                    'gross' => 471.00,
                    'driver' => 'Hendra Wijaya',
                    'driver_id' => 'ID-DRV-041 (GPA Internal)',
                    'vehicle' => 'Blind Van Chiller',
                    'plate' => 'D 8472 GA',
                    'commodity' => 'Komoditas: Tomat Beef Hidroponik + Romaine',
                    'dock' => 'TIMBANGAN DOCK #1',
                    'lock_stamp' => '2026-10-24 04:02 WIB',
                    'sj' => 'SJ-GPA-202610-0002',
                    'issue_label' => 'TERBITKAN & CETAK SJ-GPA-202610-0002',
                    'issued_label' => 'SJ-GPA-202610-0002 SUDAH TERBIT',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function manifest(): array
    {
        return [
            'title' => 'Manifest Dispatch & Pelepasan Armada Hari Ini',
            'subtitle' => 'Live tracking legal status berkas rangkap, barcode verification, dan verifikasi pelepasan security gate.',
            'filter_label' => 'FILTER STATUS:',
            'filter_default' => 'SEMUA DOKUMEN (3)',
            'filters' => [
                ['key' => 'all', 'label' => 'SEMUA DOKUMEN (3)'],
                ['key' => 'standby', 'label' => 'STANDBY DOCK (2)'],
                ['key' => 'moving', 'label' => 'SEDANG BERJALAN (1)'],
            ],
            'columns' => [
                'NO. SURAT JALAN',
                'SUPIR & ARMADA',
                'NETTO SAH',
                'JADWAL DISPATCH',
                'STATUS BERKAS / QR',
                'STATUS DOCK',
                'AKSI KOORDINATOR',
            ],
            'rows' => [
                [
                    'id' => 'SJ-GPA-202610-0001',
                    'ref' => 'ORD-GPA-202610-0042',
                    'driver' => 'Budi Santoso',
                    'plate' => 'B 9102 SDA',
                    'vehicle' => 'Engkel Box',
                    'net' => 795.00,
                    'net_tone' => 'success',
                    'time' => '04:30 WIB',
                    'slot' => 'DAWN SLOT #1',
                    'slot_tone' => 'success',
                    'copies' => '3 RANGKAP OK',
                    'copies_tone' => 'ok',
                    'qr' => 'QR VALID',
                    'qr_icon' => true,
                    'dock' => 'STANDBY DOCK #2',
                    'dock_state' => 'standby',
                    'row_tone' => 'default',
                    'action' => 'handover',
                    'action_label' => 'Serahkan Tugas ke Supir',
                ],
                [
                    'id' => 'SJ-GPA-202610-0002',
                    'ref' => 'ORD-GPA-202610-0039',
                    'driver' => 'Hendra Wijaya',
                    'plate' => 'D 8472 GA',
                    'vehicle' => 'Blind Van',
                    'net' => 442.50,
                    'net_tone' => 'success',
                    'time' => '05:15 WIB',
                    'slot' => 'DAWN SLOT #2',
                    'slot_tone' => 'success',
                    'copies' => '3 RANGKAP OK',
                    'copies_tone' => 'ok',
                    'qr' => 'QR VALID',
                    'qr_icon' => true,
                    'dock' => 'STANDBY DOCK #1',
                    'dock_state' => 'standby',
                    'row_tone' => 'default',
                    'action' => 'reprint',
                    'action_label' => 'Cetak Ulang Rangkap',
                ],
                [
                    'id' => 'SJ-GPA-202609-0899',
                    'ref' => 'ORD-GPA-202609-0771',
                    'driver' => 'Rudi Hermawan',
                    'plate' => 'B 9331 KCA',
                    'vehicle' => 'Pickup GranMax',
                    'net' => 347.50,
                    'net_tone' => 'ink',
                    'time' => '03:10 WIB',
                    'slot' => 'DEPARTED ON-TIME',
                    'slot_tone' => 'ink',
                    'copies' => 'RANGKAP DIBAWA',
                    'copies_tone' => 'muted',
                    'qr' => 'PO-D ACTIVE',
                    'qr_icon' => false,
                    'dock' => 'MENUJU LOKASI',
                    'dock_state' => 'moving',
                    'row_tone' => 'highlight',
                    'action' => 'track',
                    'action_label' => 'Lacak GPS Supir',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function batch(): array
    {
        return [
            'title' => 'Operasi Batch Dokumen Dispatch',
            'subtitle' => 'Ekspor manifest terbit harian atau kunci gate audit logistik sentra.',
            'actions' => [
                ['key' => 'export', 'label' => 'Export Manifest Harian (.csv)', 'icon' => 'download', 'variant' => 'ghost'],
                ['key' => 'audit-gate', 'label' => 'Audit Gate Keluar Gudang', 'icon' => 'lock', 'variant' => 'ink'],
                ['key' => 'print-all', 'label' => 'Cetak Semua Surat Jalan Batch', 'icon' => 'printer', 'variant' => 'accent', 'wide' => true],
            ],
        ];
    }
}
