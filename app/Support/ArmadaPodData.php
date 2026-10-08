<?php

namespace App\Support;

use App\Models\User;

/**
 * Konsol mobile Armada / Supir untuk bukti terima digital (PoD).
 */
class ArmadaPodData
{
    /**
     * Nomor plat kendaraan yang sedang menjalankan penugasan.
     */
    public const ARMADA_PLATE = 'D 8888 ABC';

    /**
     * Nomor Surat Jalan yang menjadi target penangkapan foto.
     */
    public const TARGET_SJ = 'SJ-GPA-202610-0001';

    /**
     * Berat bersih muatan yang harus dicocokkan di dock.
     */
    public const TARGET_NET = 795.0;

    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => self::operator($user),
            'header' => self::header(),
            'target' => self::target(),
            'steps' => self::steps(),
            'condition_title' => 'Status Kondisi Muatan & Retur',
            'conditions' => self::conditions(),
            'closure' => self::closure(),
            'navigation' => self::navigation(),
        ];
    }

    /**
     * Identitas awak armada yang sedang menjalankan penugasan.
     *
     * @return array<string, string>
     */
    public static function operator(?User $user = null): array
    {
        $name = $user?->name ?: 'Asep Firmansyah';

        return [
            'role' => 'Supir Armada',
            'name' => $name,
            'initials' => self::initials($name),
            'email' => $user?->email ?? 'armada@agroorder.co.id',
            'plate' => self::ARMADA_PLATE,
        ];
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

        return $initials !== '' ? $initials : 'SF';
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
            'tone' => 'brand',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function target(): array
    {
        return [
            'sj' => self::TARGET_SJ,
            'status' => 'DOKUMEN AKTIF',
            'client_label' => 'KLIEN PENERIMA',
            'client' => 'Katering Berkah Mandiri',
            'client_legal' => 'PT Kuliner Prima Nusantara',
            'dock_label' => 'TITIK BONGKAR',
            'dock' => 'Ciracas Docking C-2',
            'dock_area' => 'Tajur - Kab. Bogor',
            'weight_label' => 'TOTAL MUATAN RESMI',
            'weight' => (string) (int) self::TARGET_NET,
            'weight_unit' => 'KG Bersih',
            'commodity' => 'Sayur Daun &',
            'commodity_tail' => 'Hortikultura',
            'pic_label' => 'PIC PENERIMA RESMI',
            'pic' => 'Pak Hendra',
            'pic_role' => 'Dock Head',
        ];
    }

    /**
     * Tiga langkah wajib penangkapan PoD di dock penerima.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function steps(): array
    {
        return [
            [
                'key' => 'manifest',
                'step' => '1',
                'title' => 'Foto Fisik Surat Jalan',
                'title_tail' => '(Cap Basah)',
                'badge' => 'WAJIB CAP DOCK',
                'badge_tone' => 'accent',
                'viewfinder_label' => 'VIEWFINDER RATIO 3:4 OCR READY',
                'status' => 'READY TO CAPTURE',
                'subject' => 'SURAT JALAN ASLI',
                'meta' => [
                    ['text' => 'ISO AUTO // RES 1080x1440', 'tone' => 'soft'],
                    ['text' => 'HIGH CONTRAST', 'tone' => 'accent'],
                ],
                'placeholder_icon' => 'file-text',
                'placeholder_title' => 'Posisikan Lembar Surat Jalan GPA',
                'placeholder_hint' => 'Pastikan cap basah & paraf penerima tampak jelas',
                'action' => 'Ambil Foto Surat Jalan',
                'action_icon' => 'camera',
                'checklist_title' => 'VERIFIKASI PRA-UNGGAH:',
                'checklist' => [
                    'Cap stempel basah penerima terbaca jelas',
                    'Tanda tangan PIC penerima ada',
                    'Nama terang tertulis jelas & sesuai identitas',
                ],
            ],
            [
                'key' => 'dock',
                'step' => '2',
                'title' => 'Foto Dock & Muatan',
                'title_tail' => '(Geo-Lock)',
                'badge' => 'SENSOR TELEMETRI',
                'badge_tone' => 'warning',
                'telemetry' => [
                    ['label' => 'GEO: -6.6124, 106.8142 (Ciracas)', 'tone' => 'accent'],
                    ['label' => 'RAD: 12M OK', 'tone' => 'solid'],
                ],
                'telemetry_meta' => [
                    ['label' => 'TIMESTAMP: 24 OKT 2026', 'label_tail' => '07:34:12 WIB'],
                    ['label' => 'CHILLER REEFER:', 'label_tail' => '+3.2'."\u{00B0}".'C'],
                ],
                'viewfinder_label' => 'WIDE LENS (0.5X)',
                'dock_chip' => 'DOCK BONGKAR C-2',
                'status' => 'OPTICAL GRID: LOCKED',
                'status_tail' => 'AMBIENT LUX: 420 lx',
                'status_tail_note' => '(SUFFICIENT)',
                'placeholder_icon' => 'package',
                'placeholder_title' => 'Sorot Peti/Krat Sayur di Depan Pintu Gudang',
                'placeholder_hint' => 'Muatan terlihat utuh berdampingan dengan PIC',
                'action' => 'Ambil Foto Dock Muatan',
                'action_icon' => 'camera',
            ],
            [
                'key' => 'signature',
                'step' => '3',
                'title' => 'Tanda Tangan Digital',
                'title_tail' => 'Penerima',
                'badge' => 'SENTUH LAYAR',
                'badge_tone' => 'accent',
                'canvas_label' => 'CANVAS SENTUH ELEKTRONIK',
                'canvas_clear' => 'Hapus / Ulang',
                'identity_label' => 'NAMA TERANG & JABATAN TERVERIFIKASI:',
                'identity_name' => 'Hendra Kurniawan',
                'identity_role' => 'Dock Head / Kepala Gudang Katering Berkah Mandiri',
            ],
        ];
    }

    /**
     * Dua kondisi penerimaan muatan yang dapat dipilih supir.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function conditions(): array
    {
        return [
            [
                'key' => 'clear',
                'tone' => 'accent',
                'badge' => 'SEALED',
                'badge_tone' => 'success',
                'title' => 'Barang Diterima Utuh Tanpa Retur (795 KG)',
                'title_tone' => 'ink',
                'description' => 'Suhu reefer stabil 3.2'."\u{00B0}".'C, segel aman, timbangan klop 100% tanpa selisih.',
            ],
            [
                'key' => 'discrepancy',
                'tone' => 'neutral',
                'badge' => 'DISCREPANCY',
                'badge_tone' => 'warning',
                'title' => 'Ada Selisih / Retur Lapangan',
                'title_tone' => 'warning',
                'description' => 'Form input kuantitas ditolak, kompensasi kuota, & verifikasi alasan fisik (daun layu / krat pecah).',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function closure(): array
    {
        return [
            'label' => 'Kendala bongkar muat dock?',
            'hub_label' => 'Hubungi Hub Bogor',
            'hub_phone' => '(0811-9988-77)',
            'action' => 'VALIDASI & SELESAIKAN PENGIRIMAN',
            'action_tail' => '(LOCK POD)',
            'note' => 'Otomatis mengunci status Selesai di Pusat & menerbitkan Invoice B2B.',
        ];
    }

    /**
     * Lima entri bottom navigation aplikasi armada.
     *
     * @return array<int, array<string, string>>
     */
    public static function navigation(): array
    {
        return [
            ['key' => 'tasks', 'label' => 'Tugas', 'icon' => 'clipboard'],
            ['key' => 'dispatch', 'label' => 'Surat Jalan', 'icon' => 'file-text'],
            ['key' => 'pod', 'label' => 'Scan PoD', 'icon' => 'qrcode'],
            ['key' => 'fleet', 'label' => 'Armada', 'icon' => 'truck'],
            ['key' => 'menu', 'label' => 'Menu', 'icon' => 'grid'],
        ];
    }
}
