<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data konsol operasional & tata kelola administrasi (Sekretaris).
 *
 * Seluruh angka di bawah adalah data simulasi yang mencerminkan design
 * "Dashboard Konsol Operasional & Tata Kelola Administrasi (Sekretaris)".
 * Nanti dapat dipindahkan ke query Eloquent / service layer tanpa mengubah
 * struktur array pada view.
 */
class SecretaryDashboardData
{
    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => self::operator($user),
            'heading' => self::heading(),
            'toolbar' => self::toolbar(),
            'metrics' => self::metrics(),
            'queue' => self::queue(),
            'pipeline' => self::pipeline(),
            'stock' => self::stock(),
            'alerts' => self::alerts(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function operator(?User $user = null): array
    {
        $name = $user?->name ?: 'Budi Santoso';

        return [
            'role' => 'Sekretaris',
            'name' => $name,
            'code' => '007',
            'initials' => self::initials($name),
            'branch' => 'Kantor Pusat Cibitung',
            'email' => $user?->email ?? 'sekretaris@agroorder.co.id',
            'shift' => 'Shift Pagi 07.00-15.00 WIB',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function heading(): array
    {
        return [
            'title' => 'Dashboard Konsol Operasional & Tata Kelola Administrasi',
            'subtitle' => 'Monitoring terpusat status order masuk, rekonsiliasi pasokan harian, antrean verifikasi pembayaran manual, dan penerbitan dokumen logistik.',
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function toolbar(): array
    {
        return [
            [
                'label' => 'Batch Validasi (4 PO)',
                'icon' => 'badge-check',
                'message' => 'Empat PO prioritas masuk ke batch validasi buffer gudang & limit kredit.',
            ],
            [
                'label' => 'Sinkron Mutasi Bank BCA/Mandiri',
                'icon' => 'refresh',
                'message' => 'Sinkron mutasi rekening koran berjalan. 5 bukti transfer masih menunggu pencocokan.',
            ],
            [
                'label' => 'Cetak Rekap Surat Jalan (6 Dokumen)',
                'icon' => 'receipt',
                'message' => 'Rekap 6 surat jalan siap terbit disiapkan untuk tanda tangan koordinator.',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function metrics(): array
    {
        return [
            [
                'key' => 'inbound',
                'label' => 'Total Pesanan Masuk (Hari Ini)',
                'icon' => 'gauge',
                'icon_class' => 'bg-accent/50 text-success-deep',
                'value' => '28',
                'unit' => 'Pesanan Terjadwal',
                'value_class' => 'text-ink',
                'note' => [
                    ['text' => '21', 'class' => 'font-bold text-ink'],
                    ['text' => ' Klien Kontrak B2B | ', 'class' => 'text-ink-body'],
                    ['text' => '7', 'class' => 'font-semibold text-success-deep'],
                    ['text' => ' WhatsApp/Manual', 'class' => 'text-ink-body'],
                ],
                'badge' => null,
                'foot' => [
                    'label' => 'Nilai Estimasi:',
                    'value' => 'Rp 84.650.000',
                    'value_class' => 'text-ink',
                    'chip' => null,
                ],
            ],
            [
                'key' => 'verification',
                'label' => 'Menunggu Verifikasi Sekre',
                'icon' => 'shield',
                'icon_class' => 'bg-danger-soft text-danger',
                'value' => '4',
                'unit' => 'PO Butuh Review',
                'value_class' => 'text-danger',
                'unit_class' => 'font-semibold',
                'note' => [
                    ['text' => 'Cek ketersediaan buffer gudang & limit kredit sebelum lock PO.', 'class' => 'text-ink-body'],
                ],
                'badge' => ['label' => 'Perlu Tindakan', 'class' => 'border-danger/30 bg-danger-soft text-danger-ink'],
                'foot' => [
                    'label' => 'Tenggat Waktu:',
                    'value' => 'H-1 Pukul 15:00 WIB',
                    'value_class' => 'text-danger',
                    'chip' => null,
                ],
            ],
            [
                'key' => 'delivery',
                'label' => 'Surat Jalan Siap Terbit',
                'icon' => 'truck',
                'icon_class' => 'bg-surface-pill text-ink',
                'value' => '6',
                'unit' => 'Dokumen SJ Logistik',
                'value_class' => 'text-ink',
                'note' => [
                    ['text' => 'Menunggu validasi hasil timbang ', 'class' => 'text-ink-body'],
                    ['text' => 'actual weight', 'class' => 'font-semibold text-ink'],
                    ['text' => ' koordinator lapangan.', 'class' => 'text-ink-body'],
                ],
                'badge' => null,
                'foot' => [
                    'label' => '',
                    'value' => 'Ready to Sign',
                    'value_class' => 'text-success-ink',
                    'chip' => ['label' => 'Locked Net Weight', 'icon' => 'lock'],
                ],
            ],
            [
                'key' => 'payment',
                'label' => 'Antrean Pembayaran Manual',
                'icon' => 'banknote',
                'icon_class' => 'bg-warning-soft/60 text-warning-ink',
                'value' => '5',
                'unit' => 'Bukti Transfer',
                'value_class' => 'text-warning',
                'unit_class' => 'font-semibold',
                'note' => [
                    ['text' => 'Pencocokan mutasi rekening koran BCA/QRIS sebelum release.', 'class' => 'text-ink-body'],
                ],
                'badge' => null,
                'foot' => [
                    'label' => 'Nominal Antrean:',
                    'value' => 'Rp 19.420.000',
                    'value_class' => 'text-ink',
                    'chip' => null,
                ],
            ],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function queueColumns(): array
    {
        return [
            'po' => 'Nomor PO & Klien',
            'commodity' => 'Komoditas & Volume',
            'status' => 'Status Buffer / Kredit',
            'action' => 'Aksi Otorisasi',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function queue(): array
    {
        return [
            'title' => 'Antrean Verifikasi Pesanan Masuk Prioritas',
            'description' => 'Daftar PO yang membutuhkan otorisasi ketersediaan panen dan kepatuhan termin kredit.',
            'chip' => 'Total 4 PO Pending',
            'columns' => self::queueColumns(),
            'rows' => [
                [
                    'po' => 'PO-2024-0941',
                    'po_chip' => null,
                    'client' => 'Hotel Santika Premiere',
                    'terms' => ['label' => 'Kontrak B2B (TOP 14 Hari)', 'class' => 'text-ink-body'],
                    'items' => [
                        ['name' => 'Selada Romaine', 'weight' => '120 kg', 'class' => 'text-ink'],
                        ['name' => 'Tomat Beef', 'weight' => '80 kg', 'class' => 'text-ink-muted'],
                    ],
                    'estimate' => 'Rp 5.840.000',
                    'status' => ['label' => 'Buffer Aman (92%)', 'icon' => 'gauge', 'class' => 'border-success-deep/30 bg-accent text-success-ink'],
                    'status_note' => ['label' => 'Kredit: Rp 12M / Limit 40M', 'class' => 'text-ink-body'],
                    'actions' => [
                        ['label' => 'Setujui', 'tone' => 'ink', 'intent' => 'approve', 'message' => 'PO disetujui dan diteruskan ke tahap penugasan panen.'],
                        ['label' => 'Detail', 'tone' => 'muted', 'intent' => 'detail', 'message' => 'Rincian buffer gudang & limit kredit dimuat.'],
                    ],
                ],
                [
                    'po' => 'PO-2024-0942',
                    'po_chip' => ['label' => 'WA Manual', 'class' => 'bg-accent text-ink'],
                    'client' => 'Resto Dapur Sunda Cianjur',
                    'terms' => ['label' => 'Metode: Transfer BCA (DP 50%)', 'class' => 'text-warning'],
                    'items' => [
                        ['name' => 'Kol Putih', 'weight' => '250 kg', 'class' => 'text-ink'],
                        ['name' => 'Cabai Merah TW', 'weight' => '40 kg', 'class' => 'text-ink-muted'],
                    ],
                    'estimate' => 'Rp 4.150.000',
                    'status' => ['label' => 'Cek Bukti Bayar', 'icon' => 'banknote', 'class' => 'border-warning/30 bg-warning-soft text-warning-ink'],
                    'status_note' => ['label' => 'Stok Kol Gudang: 800 kg', 'class' => 'text-ink-body'],
                    'actions' => [
                        ['label' => 'Validasi', 'tone' => 'ink', 'intent' => 'approve', 'message' => 'Bukti transfer dicocokkan dengan mutasi rekening koran.'],
                        ['label' => 'Bukti', 'tone' => 'muted', 'intent' => 'proof', 'message' => 'Dokumen bukti transfer PO-2024-0942 dibuka.'],
                    ],
                ],
                [
                    'po' => 'PO-2024-0943',
                    'po_chip' => null,
                    'client' => 'Catering Melati Nusantara',
                    'terms' => ['label' => 'Warning: Limit Plafon 91%', 'class' => 'text-danger'],
                    'items' => [
                        ['name' => 'Brokoli Super', 'weight' => '150 kg', 'class' => 'text-ink'],
                        ['name' => 'Wortel Brastagi', 'weight' => '180 kg', 'class' => 'text-ink-muted'],
                    ],
                    'estimate' => 'Rp 9.420.000',
                    'status' => ['label' => 'Approval Finance', 'icon' => 'invoice', 'class' => 'border-danger/30 bg-danger-soft text-danger-ink'],
                    'status_note' => ['label' => 'Invoice JT: INV-0881 (H-2)', 'class' => 'text-ink-body'],
                    'actions' => [
                        ['label' => 'Eskalasi', 'tone' => 'warning', 'intent' => 'escalate', 'message' => 'Permintaan persetujuan kredit dikirim ke Finance & Pimpinan.'],
                        ['label' => 'Rekap', 'tone' => 'muted', 'intent' => 'detail', 'message' => 'Rekap piutasan & plafon Katering Melati dimuat.'],
                    ],
                ],
                [
                    'po' => 'PO-2024-0944',
                    'po_chip' => null,
                    'client' => 'Supermarket Segar Fresh (Cab. BSD)',
                    'terms' => ['label' => 'Kontrak EDI / PO Digital', 'class' => 'text-ink-body'],
                    'items' => [
                        ['name' => 'Stroberi Ciwidey A', 'weight' => '85 kg', 'class' => 'text-ink'],
                        ['name' => 'Buncis Baby Kenya', 'weight' => '60 kg', 'class' => 'text-ink-muted'],
                    ],
                    'estimate' => 'Rp 7.950.000',
                    'status' => ['label' => 'Stok Over-Order', 'icon' => 'alert-triangle', 'class' => 'border-danger/30 bg-danger-soft text-danger-ink'],
                    'status_note' => ['label' => 'Stroberi Defisit 15 kg', 'class' => 'text-danger'],
                    'actions' => [
                        ['label' => 'Split PO', 'tone' => 'ink', 'intent' => 'split', 'message' => 'PO dipecah: 70 kg alokasi gudang, 15 kg dialihkan ke Tani Hub.'],
                        ['label' => 'Tani Hub', 'tone' => 'muted', 'intent' => 'hub', 'message' => 'Permintaan penambahan pasokan dikirim ke Mitra Tani Lembang.'],
                    ],
                ],
            ],
            'footer_left' => 'Menampilkan 4 dari 4 antrean',
            'footer_right' => 'Buka Seluruh Antrean PO',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function pipeline(): array
    {
        return [
            'title' => 'Pipeline Siklus Pesanan Hari Ini',
            'description' => 'Alur progres terpadu dari Order Masuk sampai Proof of Delivery (PoD).',
            'chip' => 'Batch Logistik: B-02',
            'stages' => [
                [
                    'node' => '1',
                    'node_class' => 'bg-ink text-white',
                    'icon' => 'file-text',
                    'value' => '28',
                    'label' => 'Order Masuk',
                    'note' => '100% Terinput',
                    'note_class' => 'text-success-deep',
                ],
                [
                    'node' => '2',
                    'node_class' => 'bg-accent text-ink',
                    'icon' => 'shield',
                    'value' => '24',
                    'label' => 'Verifikasi',
                    'note' => '4 Butuh Review',
                    'note_class' => 'text-danger',
                    'active' => true,
                ],
                [
                    'node' => '3',
                    'node_class' => 'bg-surface-disabled text-ink',
                    'icon' => 'scale',
                    'value' => '18',
                    'label' => 'Timbang Actual',
                    'note' => 'Sortir Lapangan',
                    'note_class' => 'text-ink-body',
                ],
                [
                    'node' => '4',
                    'node_class' => 'bg-surface-disabled text-ink',
                    'icon' => 'truck',
                    'value' => '12',
                    'label' => 'SJ Terbit',
                    'note' => '6 Locked Ready',
                    'note_class' => 'text-ink-body',
                ],
                [
                    'node' => '5',
                    'node_class' => 'bg-accent text-success-ink',
                    'icon' => 'badge-check',
                    'value' => '8 / 4',
                    'label' => 'OTR / PoD',
                    'note' => '4 PoD Selesai',
                    'note_class' => 'text-success-ink',
                ],
            ],
            'footer_label' => 'Lead-Time Rata-rata Pemrosesan Sekretaris:',
            'footer_value' => '32 Menit / Order',
            'footer_sla' => 'Target SLA: 98.5% Tercapai',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function stock(): array
    {
        return [
            'title' => 'Kesiapan Stok & Buffer Panen',
            'description' => 'Real-time alokasi fisik di Gudang Transit Cibitung',
            'icon' => 'gauge',
            'rows' => [
                [
                    'name' => 'Selada Romaine',
                    'alert' => false,
                    'percent' => 85,
                    'card_class' => 'border-accent bg-accent/40',
                    'chip' => ['label' => '85% Terlokasi [Aman]', 'class' => 'bg-accent text-success-ink'],
                    'fill_class' => 'bg-success-deep',
                    'foot' => [
                        ['label' => 'Alokasi: 425 kg / Total: 500 kg', 'class' => 'text-ink-body'],
                        ['label' => 'Sisa Buffer: 75 kg', 'class' => 'text-success-ink'],
                    ],
                ],
                [
                    'name' => 'Tomat Beef Super',
                    'alert' => false,
                    'percent' => 92,
                    'card_class' => 'border-warning-border/40 bg-surface-shell',
                    'chip' => ['label' => '92% [Kritis]', 'class' => 'bg-warning-soft text-warning-ink'],
                    'fill_class' => 'bg-warning',
                    'foot' => [
                        ['label' => 'Sisa Buffer: 55 kg', 'class' => 'text-warning'],
                        ['label' => 'Hubungi Mitra Tani Lembang!', 'class' => 'text-danger'],
                    ],
                ],
                [
                    'name' => 'Brokoli Super Grade A',
                    'alert' => false,
                    'percent' => 60,
                    'card_class' => 'border-line-soft bg-surface-shell',
                    'chip' => ['label' => '60% Terlokasi [Normal]', 'class' => 'bg-surface-disabled text-ink-body'],
                    'fill_class' => 'bg-success-deep',
                    'foot' => [
                        ['label' => 'Alokasi: 360 kg / Total: 600 kg', 'class' => 'text-ink-body'],
                        ['label' => 'Sisa Buffer: 240 kg', 'class' => 'text-success-ink'],
                    ],
                ],
                [
                    'name' => 'Stroberi Ciwidey',
                    'alert' => true,
                    'percent' => 95,
                    'card_class' => 'border-danger/30 bg-danger-soft/40',
                    'chip' => ['label' => '95% Over-Order', 'class' => 'bg-danger text-white'],
                    'fill_class' => 'bg-danger',
                    'foot' => [
                        ['label' => 'Tutup Order Manual Baru', 'class' => 'text-danger'],
                        ['label' => 'Lock Komoditas', 'class' => 'text-ink'],
                    ],
                ],
                [
                    'name' => 'Kol Putih Medan',
                    'alert' => false,
                    'percent' => 40,
                    'card_class' => 'border-line-soft bg-surface-shell',
                    'chip' => ['label' => '40% Terlokasi [Melimpah]', 'class' => 'bg-surface-disabled text-ink-body'],
                    'fill_class' => 'bg-success-deep',
                    'foot' => [
                        ['label' => 'Alokasi: 320 kg / Total: 800 kg', 'class' => 'text-ink-body'],
                        ['label' => 'Sisa Buffer: 480 kg', 'class' => 'text-success-ink'],
                    ],
                ],
            ],
            'footer_left' => 'Update: 4 menit lalu',
            'footer_right' => 'Kelola Alokasi Stok',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function alerts(): array
    {
        return [
            'title' => 'Peringatan Operasional & Logistik',
            'items' => [
                [
                    'tone' => 'danger',
                    'card_class' => 'border-l-danger',
                    'icon' => 'alert-triangle',
                    'icon_class' => 'text-danger',
                    'title' => 'Deadline Cut-Off H-1 Jam 16:00 WIB',
                    'value' => 'Sisa 91 Menit',
                    'value_class' => 'text-danger',
                    'body' => 'Seluruh PO manual masuk via WhatsApp wajib terinput sebelum 16:00 WIB agar dapat masuk penugasan panen malam ini.',
                ],
                [
                    'tone' => 'warning',
                    'card_class' => 'border-l-warning-border',
                    'icon' => 'alert-circle',
                    'icon_class' => 'text-warning',
                    'title' => 'Limit Kredit TOP 30 Katering Melati',
                    'value' => 'Piutang Rp 45.8jt',
                    'value_class' => 'text-warning',
                    'body' => 'Total piutang berjalan mencapai Rp 45.800.000 (91% dari plafon Rp 50.000.000). Butuh otorisasi pimpinan sebelum approve PO-2024-0943.',
                ],
                [
                    'tone' => 'quiet',
                    'card_class' => 'border-l-ink-quiet',
                    'icon' => 'info',
                    'icon_class' => 'text-ink-quiet',
                    'title' => 'Notifikasi Selisih 14 Kg Kol Putih',
                    'value' => 'BA Tercatat',
                    'value_class' => 'text-ink-muted',
                    'body' => 'Supir Armada #03 (B-9021-UYX) melaporkan susut timbang basah di pos timbang sortir Ciwidey. Berita acara susut telah di-upload.',
                ],
            ],
            'footer_left' => 'Logistik Hub Cibitung: Operasional Normal',
            'footer_right' => 'Arsip Notifikasi',
        ];
    }

    private static function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        $initials = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $initials .= mb_strtoupper(mb_substr($part, 0, 1));

            if (mb_strlen($initials) === 2) {
                break;
            }
        }

        return $initials !== '' ? $initials : 'BS';
    }
}
