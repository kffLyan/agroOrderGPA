<?php

namespace App\Support;

use App\Models\User;

/**
 * Konsol mobile Armada untuk status kendaraan, telemetri IoT, audit pre-trip,
 * rekap kinerja shift, dan dukungan operasional hub.
 */
class ArmadaStatusData
{
    public const ARMADA_PLATE = 'D 8888 ABC';

    public const DRIVER_CODE = 'DRV-08';

    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => ArmadaPodData::operator($user),
            'header' => self::header(),
            'vehicle' => self::vehicle(),
            'driver' => self::driver(),
            'telemetry' => self::telemetry(),
            'trip' => self::trip(),
            'performance' => self::performance(),
            'support' => self::support(),
            'closure' => self::closure(),
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
     * Status operasional kendaraan dan indikator sinkronisasi.
     *
     * @return array<string, string>
     */
    public static function vehicle(): array
    {
        return [
            'label' => 'STATUS TELEMETRI KENDARAAN',
            'status' => 'OPERASIONAL',
            'status_tail' => 'AKTIF',
            'icon' => 'truck',
            'badge' => 'LIVE SYNC',
            'badge_tail' => '15S',
        ];
    }

    /**
     * Data supir dan unit kendaraan yang sedang berjalan.
     *
     * @return array<string, mixed>
     */
    public static function driver(): array
    {
        $driverCode = substr((string) self::DRIVER_CODE, strlen('DRV-'));
        $unitCode = 'SUPIR-GPA-'.$driverCode;

        return [
            'section' => 'DATA PENGEMUDI & UNIT',
            'code_label' => 'KODE: DRV-',
            'code' => $driverCode,
            'initials' => 'JP',
            'name' => 'Joko Prasetyo',
            'unit_code' => $unitCode,
            'tags' => [
                ['label' => 'PANGKALAN: STA HUB BOGOR-04', 'tone' => 'neutral'],
                ['label' => 'SIM B1: EXP 2028-11', 'tone' => 'accent'],
            ],
            'specs' => [
                'label' => 'SPESIFIKASI KENDARAAN',
                'lines' => ['Engkel CDD', 'Reefer'],
                'foot' => 'Unit Tag #03',
            ],
            'plate_label' => 'NOMOR POLISI',
            'plate_tail' => 'TERDAFTAR',
            'plate' => 'B 9284 TDA',
            'plate_note' => 'Validasi Dishub DKI',
        ];
    }

    /**
     * Telemetri IoT kendaraan: chiller, kapasitas muat, bahan bakar, GPS.
     *
     * @return array<string, mixed>
     */
    public static function telemetry(): array
    {
        return [
            'section' => 'TELEMETRI IOT TERKONEKSI',
            'badge' => 'LIVE',
            'badge_tail' => 'SENSOR',
            'chiller' => [
                'label' => 'SUHU CHILLER BOX (KOMPARTEMEN SAYURAN)',
                'value' => '+3.8'."\u{00B0}".'C',
                'icon' => 'thermometer',
                'badge' => 'NORMAL (TARGET OK)',
                'range_label' => 'Ambang Batas:',
                'range_min' => '+2.0'."\u{00B0}".'C s.d.',
                'range_max' => '+6.0'."\u{00B0}".'C',
                'commodity' => 'Komoditas: Sayuran',
                'commodity_tail' => 'Daun',
            ],
            'capacity' => [
                'label' => 'KAPASITAS MUAT BOX',
                'value' => '1.986 / 3.000 kg',
                'percent' => '(66.2%)',
                'percent_value' => 66.2,
                'foot' => [
                    ['label' => '0 kg', 'tail' => '(Kosong)'],
                    ['label' => 'Beban Ideal', 'tail' => 'Hortikultura'],
                    ['label' => 'Max Tonase: 3.0', 'tail' => 'Ton'],
                ],
            ],
            'cards' => [
                [
                    'label' => 'BAHAN BAKAR',
                    'icon' => 'gauge',
                    'value' => '78% Solar Dex',
                    'note' => 'Odo: 48.210 KM Total',
                ],
                [
                    'label' => 'GPS TRACKER',
                    'icon' => 'map-pin',
                    'value' => 'ONLINE 4G',
                    'note' => '11 Satelit &bull; Latensi',
                    'note_tail' => '38ms',
                ],
            ],
        ];
    }

    /**
     * Checklist kelaiakan pagi beserta persetujuan mekanik hub.
     *
     * @return array<string, mixed>
     */
    public static function trip(): array
    {
        return [
            'section' => 'AUDIT PRE-TRIP',
            'title' => 'KELAIKAN',
            'subtitle' => 'Checklist Kelaikan',
            'subtitle_tail' => 'Pagi',
            'badge' => '5/5 LOLOS',
            'badge_tail' => 'VERIFIKASI',
            'items' => [
                [
                    'label' => 'Suhu Chiller Box Pre-cooling normal',
                    'tail' => '(+4.0'."\u{00B0}".'C)',
                    'time' => '05:12',
                    'time_tail' => 'WIB',
                ],
                [
                    'label' => 'Tekanan Ban (75 PSI) & Rem',
                    'tail' => 'terverifikasi',
                    'time' => '05:18',
                    'time_tail' => 'WIB',
                ],
                [
                    'label' => 'Dokumen KIR & STNK Asli Aktif',
                    'tail' => null,
                    'time' => '05:22 WIB',
                    'time_tail' => null,
                ],
                [
                    'label' => 'APAR (Tabung 3kg) & Kotak P3K',
                    'tail' => 'Lengkap',
                    'time' => '05:25',
                    'time_tail' => 'WIB',
                ],
                [
                    'label' => 'Kebersihan Box Bebas Residu & Bau',
                    'tail' => null,
                    'time' => '05:28 WIB',
                    'time_tail' => null,
                ],
            ],
            'mechanic_label' => 'DISETUJUI MEKANIK HUB',
            'mechanic' => 'Agus Suprapto (NPP: MK-441)',
            'sign_label' => 'SIGN: 05:30',
            'sign_tail' => 'WIB',
        ];
    }

    /**
     * Rekap kinerja konsinyasi shift pagi.
     *
     * @return array<string, mixed>
     */
    public static function performance(): array
    {
        return [
            'section' => 'REKAP KINERJA',
            'title' => 'KONSINYASI',
            'subtitle' => 'Shift Pagi &bull; R-01 (Bogor-Jkt)',
            'badge' => 'BATCH',
            'badge_tail' => 'HARIAN',
            'cards' => [
                [
                    'label' => 'TITIK PENGIRIMAN',
                    'value' => '2 Titik',
                    'note' => ['1 Selesai &bull; 1 Sedang', 'Jalan'],
                    'tone' => 'success',
                ],
                [
                    'label' => 'TOTAL TONASE',
                    'value' => '1.986,5 kg',
                    'note' => ['Alokasi 2 DO', 'Konsinyasi'],
                    'tone' => 'neutral',
                ],
                [
                    'label' => 'KETEPATAN WAKTU',
                    'label_tail' => '(SLA)',
                    'value' => '100% SLA',
                    'note' => ['Dawn Delivery &le; 07:00', 'WIB'],
                    'tone' => 'success',
                ],
                [
                    'label' => 'RETUR LAPANGAN',
                    'value' => '1 Kasus (10 kg)',
                    'note' => ['BA-04 kompensasi', 'kuota'],
                    'tone' => 'warning',
                ],
            ],
        ];
    }

    /**
     * Dukungan operasional dan hotline kendaraan.
     *
     * @return array<string, mixed>
     */
    public static function support(): array
    {
        return [
            'section' => 'DUKUNGAN OPERASIONAL',
            'section_tail' => '& HUB',
            'badge' => 'HOTLINE',
            'badge_tail' => '24/7',
            'report_label' => 'LAPOR KENDALA KENDARAAN',
            'report_note' => '(Mogok / Ban Bocor / Chiller Mati / Kecelakaan',
            'report_note_tail' => 'Lalu Lintas)',
            'dispatch_label' => 'DISPATCH KOORDINATOR HUB (STA BOGOR-04)',
            'dispatch_value' => 'Ext-402 (Hotline: 021-884-9021)',
            'sop_label' => 'Panduan SOP Penanganan Mutu &',
            'sop_tail' => 'Cold-Chain',
            'sop_badge' => 'DOC_V3',
        ];
    }

    /**
     * Aksi tutup shift dan catatan kepatuhan.
     *
     * @return array<string, string>
     */
    public static function closure(): array
    {
        return [
            'action' => 'SELESAIKAN SHIFT & TUTUP',
            'action_tail' => 'TUGAS HARIAN',
            'note' => '*Pastikan seluruh PoD terunggah & suhu akhir',
            'note_tail' => 'reefer dicatat di gerbang STA',
            'footer' => 'AGRO-ENTERPRISE LOGISTICS SYSTEM &bull; PRD APP-GPA SEC 6.4',
            'footer_tail' => '&amp; 11 &bull; BUILD #4190',
        ];
    }
}
