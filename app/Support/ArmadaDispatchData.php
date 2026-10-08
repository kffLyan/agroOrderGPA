<?php

namespace App\Support;

use App\Models\User;

/**
 * Surat jalan digital awak armada: rute pengantaran multi-drop, rincian muatan
 * berstempel metrologi, gate pass dock, dan prospecting keberangkatan.
 */
class ArmadaDispatchData
{
    public const ARMADA_PLATE = 'D 8888 ABC';

    public const SJ_NUMBER = 'SJ-GPA-202610-0001';

    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => ArmadaPodData::operator($user),
            'header' => self::header(),
            'intro' => self::intro(),
            'route' => self::route(),
            'letter' => self::letter(),
            'cargo' => self::cargo(),
            'gatePass' => self::gatePass(),
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
     * @return array<string, string>
     */
    public static function intro(): array
    {
        return [
            'title' => 'Surat Jalan Digital & Rincian<br>Muatan Supir',
            'subtitle' => 'Verifikasi kesesuaian fisik muatan & segel timbangan tera<br>sebelum keberangkatan.',
        ];
    }

    /**
     * Dua titik pengantaran pada manifest berjalan.
     *
     * @return array<string, mixed>
     */
    public static function route(): array
    {
        return [
            'label' => 'RUTE PENGANTARAN (2 STOPS)',
            'badge' => 'MULTI-DROP HARIAN',
            'stops' => [
                [
                    'badge' => 'STOP #1',
                    'badge_tone' => 'ink',
                    'dot' => 'success',
                    'status' => 'SIAP BERANGKAT',
                    'status_tone' => 'success',
                    'number' => 'SJ-202610-0001',
                    'number_tone' => 'strong',
                    'client' => 'PT Kuliner Prima Nusantara',
                    'client_tone' => 'strong',
                    'weight_label' => 'Muatan Sah:',
                    'weight' => '795.0 kg',
                    'weight_tone' => 'strong',
                    'active' => true,
                ],
                [
                    'badge' => 'STOP #2',
                    'badge_tone' => 'muted',
                    'dot' => 'quiet',
                    'status' => 'MENUNGGU RUTE #1',
                    'status_tone' => 'muted',
                    'number' => 'SJ-202610-0002',
                    'number_tone' => 'muted',
                    'client' => 'CV Boga Lestari Mandiri',
                    'client_tone' => 'muted',
                    'weight_label' => 'Muatan Est:',
                    'weight' => '836.5 kg',
                    'weight_tone' => 'muted',
                    'active' => false,
                ],
            ],
        ];
    }

    /**
     * Header surat jalan, rantai penerima, dan kontak PIC dock.
     *
     * @return array<string, mixed>
     */
    public static function letter(): array
    {
        return [
            'validated' => 'TERVALIDASI DISPATCH DOCK-03',
            'number' => self::SJ_NUMBER,
            'po_label' => 'Ref PO: ',
            'po_number' => '#ORD-GPA-202610-0042',
            'dispatch_label' => 'WAKTU DISPATCH',
            'dispatch_date' => '24 Okt 2026',
            'dispatch_time' => '05:42 WIB',
            'chain' => [
                [
                    'icon' => 'building',
                    'shell' => 'muted',
                    'role_label' => 'PENGIRIM LOGISTIK',
                    'role_tone' => 'muted',
                    'name' => 'PT Agro Pasti Ada',
                    'address' => 'Sentral Transhipment Hub Bogor - Dock 03',
                ],
                [
                    'icon' => 'map-pin',
                    'shell' => 'accent',
                    'role_label' => 'DOKUMEN PENERIMA (DESTINASI #1)',
                    'role_tone' => 'success',
                    'name' => 'PT Kuliner Prima Nusantara',
                    'address' => 'Central Kitchen Ciracas Hub, Jl. Raya Bogor KM<br>28, Jakarta Timur',
                ],
            ],
            'pic' => [
                'label' => 'PIC GUDANG PENERIMA',
                'name' => 'Pak Hendra Gunawan',
                'phone' => '0812-3456-7890',
            ],
        ];
    }

    /**
     * Rincian muatan per SKU beserta akumulasi timbang sah.
     *
     * @return array<string, mixed>
     */
    public static function cargo(): array
    {
        return [
            'rule' => 'PRD RULE 04 & 05 METROLOGY SAH',
            'stamp' => [
                'title' => 'Stempel Metrologi Tera Disperindag Sah',
                'body' => 'SK Tera No. 510/PKTN/ML/XI/2024 &bull; Tera Sah<br>Digital Transhipment',
            ],
            'items' => [
                self::cargoItem(
                    sku: 'VEG-ROM-01',
                    name: 'Selada Romaine Super',
                    spec: 'Grade A Horeca &bull; 25 Krat Berlubang<br>Steril',
                    est: '500.0 kg',
                    netto: '497.0 kg',
                    deviation: '-3.0 kg',
                    deviationRate: '(-0.60%)',
                    containers: 'Wadah: 25 Krat',
                    tare: 'Tara: 50.0 kg',
                ),
                self::cargoItem(
                    sku: 'VEG-TMT-04',
                    name: 'Tomat Beef Dataran Tinggi',
                    spec: 'Grade A &bull; 15 Krat',
                    est: '300.0 kg',
                    netto: '298.0 kg',
                    deviation: '-2.0 kg',
                    deviationRate: '(-0.67%)',
                    containers: 'Wadah: 15 Krat',
                    tare: 'Tara: 30.0 kg',
                ),
            ],
            'total' => [
                'label' => 'TOTAL AKUMULASI TIMBANG SAH (STOP #1)',
                'columns' => [
                    [
                        'label' => 'Est Awal',
                        'label_tone' => 'muted',
                        'values' => ['800.0 kg'],
                        'value_tone' => 'plain',
                    ],
                    [
                        'label' => 'Total Netto Sah',
                        'label_tone' => 'accent',
                        'values' => ['795.0 kg'],
                        'value_tone' => 'accent',
                    ],
                    [
                        'label' => 'Deviasi Sah',
                        'label_tone' => 'muted',
                        'values' => ['-5.0 kg', '(-0.63%)'],
                        'value_tone' => 'accent',
                    ],
                ],
                'containers' => 'Wadah: 40 Krat (80.0<br>kg Tara)',
                'cold_chain' => '+3.8'."\u{00B0}".'C (Optimal<br>Sayuran Daun)',
            ],
        ];
    }

    /**
     * Satu baris rincian muatan beserta statistik timbangan.
     *
     * @return array<string, mixed>
     */
    protected static function cargoItem(
        string $sku,
        string $name,
        string $spec,
        string $est,
        string $netto,
        string $deviation,
        string $deviationRate,
        string $containers,
        string $tare,
    ): array {
        return [
            'sku' => 'SKU: '.$sku,
            'name' => $name,
            'spec' => $spec,
            'badge' => 'PASSED <2%',
            'columns' => [
                ['label' => 'Est PO', 'label_tone' => 'muted', 'value' => $est, 'value_tone' => 'strong'],
                ['label' => 'Netto Sah', 'label_tone' => 'success', 'value' => $netto, 'value_tone' => 'strong'],
                [
                    'label' => 'Deviasi Susut',
                    'label_tone' => 'muted',
                    'value' => $deviation,
                    'value_tail' => $deviationRate,
                    'value_tone' => 'success',
                ],
            ],
            'containers' => $containers,
            'tare' => $tare,
        ];
    }

    /**
     * Gate pass security dock, status segel, dan aksi keberangkatan.
     *
     * @return array<string, mixed>
     */
    public static function gatePass(): array
    {
        return [
            'title' => 'GATE PASS SECURITY DOCK',
            'expiry_label' => 'EXP:',
            'expiry_minutes' => 45,
            'token' => 'TOKEN: GPA-795-B9284-SEC',
            'hint' => 'Tunjukkan ke pos security gerbang sentral<br>hub &amp; dock penerima.',
            'seal' => [
                'label' => 'STATUS SEGEL KONTAINER',
                'value' => 'Segel Digital Aktif (Terkunci Otomatis)',
            ],
            'action' => 'MULAI PENGIRIMAN (ON-ROUTE)',
            'action_note' => 'Status GPS Logbook &amp; Reefer Tracker Aktif<br>Terkoneksi',
            'report' => 'Lapor Kendala / Selisih Muatan',
        ];
    }
}
