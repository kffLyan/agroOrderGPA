<?php

namespace App\Support;

use App\Models\User;

/**
 * Konsol mobile Armada / Supir untuk daftar tugas harian beserta antrean
 * titik bongkar pada manifest berjalan.
 */
class ArmadaTasksData
{
    public const ARMADA_PLATE = 'D 8888 ABC';

    public const MANIFEST_CODE = 'TRP-2502-09';

    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => ArmadaPodData::operator($user),
            'header' => self::header(),
            'assignment' => self::assignment(),
            'telemetry' => self::telemetry(),
            'manifest' => self::manifest(),
            'filters' => self::filters(),
            'queue_title' => 'Antrean Titik Bongkar',
            'queue_compliance' => 'PRD V12 COMPLIANT',
            'drops' => self::drops(),
            'support' => self::support(),
            'navigation' => ArmadaPodData::navigation(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function header(): array
    {
        return [
            'brand' => 'AGROORDER GPA',
            'role' => 'Armada Logistik',
            'plate' => self::ARMADA_PLATE,
            'tone' => 'light',
        ];
    }

    /**
     * Identitas supir, armada, dan batas waktu Dock Head.
     *
     * @return array<string, string>
     */
    public static function assignment(): array
    {
        return [
            'label' => 'SUPIR BERTUGAS',
            'name' => 'Slamet Prasetyo',
            'code' => 'DVR-GPA-2024-08',
            'unit' => 'Unit CDD',
            'slot' => '#03',
            'status_label' => 'SIAP',
            'status_tail' => 'BONGKAR',
            'deadline_label' => 'BATAS:',
            'deadline' => '08:30 WIB',
        ];
    }

    /**
     * Dua ringkasan sensor dan kapasitas unit.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function telemetry(): array
    {
        return [
            [
                'label' => 'SUHU CHILLER',
                'value' => '+3.8'."\u{00B0}".'C',
                'note' => 'OPTIMAL (COLD',
                'note_tail' => 'CHAIN)',
                'tone' => 'success',
                'icon' => 'thermometer',
            ],
            [
                'label' => 'TOTAL MUATAN',
                'value' => '1.986',
                'unit' => '/3.000 kg',
                'note' => 'UTILISASI: 66.2%',
                'tone' => 'ink',
                'icon' => 'truck',
            ],
        ];
    }

    /**
     * Kartu manifest gelap beserta radar lalu lintas tol.
     *
     * @return array<string, mixed>
     */
    public static function manifest(): array
    {
        return [
            'label' => 'MANIFEST',
            'code' => '#'.self::MANIFEST_CODE,
            'badge' => '2 TITIK DROP',
            'metrics' => [
                ['label' => 'TOTAL BERAT', 'value' => '1.986,5 kg'],
                ['label' => 'JARAK TOTAL', 'value' => '64,2 km'],
                ['label' => 'KORIDOR RUTE', 'value' => 'JORR 2 &#10132; BSD'],
            ],
            'traffic_label' => 'RADAR LALU LINTAS TOL:',
            'traffic' => 'GT Ciracas padat lancar (antrean 150m). Kondisi jalur lingkar luar BSD terpantau lancar tanpa hambatan cuaca.',
        ];
    }

    /**
     * Chip filter status antrean.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function filters(): array
    {
        return [
            ['key' => 'all', 'label' => 'SEMUA', 'count' => 2, 'active' => true],
            ['key' => 'ready', 'label' => 'SIAP', 'count' => 1, 'active' => false],
            ['key' => 'road', 'label' => 'JALAN', 'count' => 1, 'active' => false],
            ['key' => 'done', 'label' => 'SELESAI', 'count' => 0, 'active' => false],
        ];
    }

    /**
     * Dua titik drop pada manifest berjalan.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function drops(): array
    {
        return [
            [
                'key' => 'drop-1',
                'sequence_label' => 'DROP KE-1',
                'state_label' => 'ON ROAD',
                'state' => 'road',
                'sequence_tone' => 'ink',
                'time_label' => 'ETA',
                'time' => '06:45 WIB',
                'time_tone' => 'warning',
                'priority' => 'PRIORITAS A1',
                'sj' => 'SJ-20250225-0042',
                'client' => 'PT Kuliner Prima Nusantara',
                'address' => 'Central Kitchen Ciracas Hub, Jl. Raya Bogor KM 28, Jakarta Timur (Loading Bay #2)',
                'cargo_label' => 'RINCIAN MUATAN (75 KRAT/BOX)',
                'cargo_net' => 'NETTO: 1.150,0 KG',
                'items' => [
                    ['label' => '1. Ayam Karkas Segar', 'weight' => '850,0 kg'],
                    ['label' => '2. Selada Romaine Hidroponik', 'weight' => '300,0 kg'],
                ],
                'window_label' => 'JENDELA TERIMA:',
                'window' => '06:00 - 07:15 WIB (TERPENUHI)',
                'window_tone' => 'ink',
                'primary' => ['label' => 'PROSES POD DROP #1', 'icon' => 'qrcode'],
                'secondary' => ['label' => 'CEK RUTE', 'icon' => 'map-pin'],
                'muted' => false,
            ],
            [
                'key' => 'drop-2',
                'sequence_label' => 'DROP KE-2',
                'state_label' => 'ANTREAN',
                'state' => 'queued',
                'sequence_tone' => 'neutral',
                'time_label' => 'Target',
                'time' => '08:00 WIB',
                'time_tone' => 'ink',
                'priority' => 'STANDAR B',
                'sj' => 'SJ-20250225-0043',
                'client' => 'CV Boga Lestari Mandiri',
                'address' => 'Gudang Dapur Serpong BSD, Kawasan Industri Taman Tekno Blok D/12',
                'cargo_label' => 'RINCIAN MUATAN (42 KARTON)',
                'cargo_net' => 'NETTO: 836,5 KG',
                'items' => [
                    ['label' => '1. Daging Ayam Parting Segar', 'weight' => '600,0 kg'],
                    ['label' => '2. Bawang Merah Brebes Super', 'weight' => '236,5 kg'],
                ],
                'window_label' => 'STATUS PROTOKOL:',
                'window' => 'DOKUMEN TERKUNCI S/D DROP #1 SELESAI',
                'window_tone' => 'warning',
                'primary' => ['label' => 'POD TERKUNCI', 'icon' => 'lock', 'locked' => true],
                'secondary' => ['label' => 'JALUR ALT.', 'icon' => 'refresh'],
                'muted' => true,
            ],
        ];
    }

    /**
     * Kontak darurat dispatch dan jejak audit pengiriman sebelumnya.
     *
     * @return array<string, mixed>
     */
    public static function support(): array
    {
        return [
            'label' => 'BANTUAN DARURAT DISPATCH',
            'phone' => '(021) 884-9021',
            'action' => 'HUBUNGI',
            'audit_title' => 'AUDIT PENGIRIMAN KEMARIN (100% SAH):',
            'audit' => [
                ['label' => '&bull; Superindo Daan Mogot (1.420 kg)', 'status' => 'SUKSES [PoD-912]'],
                ['label' => '&bull; Transmart Cempaka Putih (980 kg)', 'status' => 'SUKSES [PoD-913]'],
            ],
            'footer' => 'AGRO-COMMAND MOBILE v4.2 &bull; GPA COMPLIANCE SECTION 6.4, 11 &amp; 12',
        ];
    }
}
