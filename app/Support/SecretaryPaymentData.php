<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data konsol verifikasi pembayaran manual & rekonsiliasi kas (Sekretaris).
 *
 * Seluruh nilai di bawah adalah data simulasi yang mencerminkan design
 * "Konsol Verifikasi Pembayaran Manual & Rekonsiliasi Kas"
 * (Rule 11 verifikasi bukti bayar + Rule 15 rekonsiliasi buku kas).
 */
class SecretaryPaymentData
{
    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => SecretaryDashboardData::operator($user),
            'heading' => self::heading(),
            'policy' => self::policy(),
            'metrics' => self::metrics(),
            'queue' => self::queue(),
            'inspection' => self::inspection(),
            'audit' => self::audit(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function heading(): array
    {
        return [
            'eyebrow' => 'Sub-05 // Modul Verifikasi Pembayaran & Rekonsiliasi Kas',
            'title_before' => 'Konsol Verifikasi Pembayaran Manual &',
            'title_after' => 'Rekonsiliasi Kas',
            'subtitle_before' => 'Pemeriksaan dan validasi bukti transfer bank manual serta QRIS statis oleh Admin/Sekre Keuangan sesuai',
            'subtitle_rule' => 'PRD App-GPA.md Section 15 & Rule 11 (Non-Gateway MVP Phase).',
            'badge' => 'BCA / Mandiri Giro',
            'badge_label' => 'Live Ingestion: Bank',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function policy(): array
    {
        return [
            'label' => 'Kebijakan Kontrol Finansial PRD Rule 11 & Section 15.2:',
            'chip' => 'Mandatory Admin',
            'body_before' => 'Unggah bukti pembayaran ',
            'body_emphasis' => 'BUKAN berarti otomatis lunas',
            'body_after' => '. Pembayaran baru sah dan status transaksi diperbarui setelah Admin/Sekre memeriksa mutasi rekening bank dan menyetujui (Approve) bukti fisik rekening koran harian.',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function metrics(): array
    {
        return [
            [
                'key' => 'waiting',
                'label' => 'Menunggu Verifikasi Admin',
                'icon' => 'clock',
                'icon_tile' => 'bg-warning-soft/40',
                'icon_class' => 'text-warning-caution',
                'value' => '5',
                'unit' => 'Bukti Bayar',
                'value_class' => 'text-ink',
                'detail' => 'Rp 38.450.000',
                'detail_class' => 'font-mono text-sm font-bold tracking-[0.88px] text-ink',
                'foot_label' => 'Prioritas Tinggi',
                'foot_class' => 'text-ink-quiet',
                'chip' => [
                    'label' => 'Urgent (Antrean: 47 Menit)',
                    'class' => 'bg-danger-soft text-danger-ink outline outline-1 outline-danger/60',
                ],
            ],
            [
                'key' => 'verified',
                'label' => 'Terverifikasi Hari Ini',
                'icon' => 'check-circle',
                'icon_tile' => 'bg-accent/60',
                'icon_class' => 'text-success-deep',
                'value' => '14',
                'unit' => 'Transaksi',
                'value_class' => 'text-success-deep',
                'detail' => 'Rp 76.200.000',
                'detail_class' => 'font-mono text-sm font-bold tracking-[0.88px] text-success-deep',
                'foot_label' => 'Buku Kas Sah',
                'foot_class' => 'text-success-deep',
                'chip' => [
                    'label' => 'Otorisor: OP-4091',
                    'class' => 'bg-accent text-success-deep outline outline-1 outline-success-deep',
                ],
            ],
            [
                'key' => 'rejected',
                'label' => 'Ditolak / Selisih Bukti',
                'icon' => 'alert-triangle',
                'icon_tile' => 'bg-danger-soft/60',
                'icon_class' => 'text-danger',
                'value' => '1',
                'unit' => 'Transaksi',
                'value_class' => 'text-danger',
                'detail' => 'Butuh konfirmasi ulang klien (Nominal beda)',
                'detail_class' => 'gpa-note text-danger',
                'foot_label' => 'ID PO: PO-202410-0089',
                'foot_class' => 'text-warning-caution',
                'chip' => [
                    'label' => 'Tertahan',
                    'class' => 'bg-surface-pill text-ink-quiet outline outline-1 outline-line-board',
                ],
            ],
            [
                'key' => 'settlement',
                'label' => 'Total Settlement Pekan Ini',
                'icon' => 'banknote',
                'icon_tile' => 'bg-surface-track',
                'icon_class' => 'text-ink',
                'value' => 'Rp 260.631.000',
                'unit' => null,
                'value_class' => 'font-mono text-2xl text-brand',
                'detail' => 'Bank Transfer: 78% | QRIS: 14% | COD: 8%',
                'detail_class' => 'gpa-note text-ink-body',
                'foot_label' => 'Recon-Pass',
                'foot_class' => 'text-success-deep',
                'chip' => [
                    'label' => '100% Balanced',
                    'class' => 'bg-accent/60 text-success-deep outline outline-1 outline-accent-deep',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function queue(): array
    {
        return [
            'title' => 'Daftar Antrean Pembayaran Masuk',
            'subtitle' => '5 Transaksi Pending Review',
            'sync_label' => 'Auto-Sync',
            'sync_duration' => '(30s)',
            'filters' => [
                ['key' => 'all', 'label' => 'Semua Antrean'],
                ['key' => 'bank', 'label' => 'Transfer Bank BCA/Mandiri'],
                ['key' => 'qris', 'label' => 'QRIS Statis GPA'],
                ['key' => 'giro', 'label' => 'Giro / TOP B2B'],
            ],
            'rows' => self::queueRows(),
            'total' => 38450000,
            'total_label' => 'Rp 38.450.000',
            'footer_left' => 'Antrean: 5 dari 5 Ditampilkan',
            'footer_right' => 'FIFO Queue Priority: Strict',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function queueRows(): array
    {
        return [
            [
                'id' => 'PAY-0921',
                'invoice' => 'INV-202410-0091',
                'channel' => 'bank',
                'channel_label' => 'BCA Transfer Manual',
                'time' => '10:20',
                'client' => 'PT Kuliner Makmur Sentosa',
                'client_short' => 'PT Kuliner Makmur',
                'amount' => 18550000,
                'amount_label' => 'Rp 18.550.000',
                'evidence' => 'Slip Transfer Unggahan',
                'evidence_type' => 'JPG (640KB)',
                'evidence_file' => 'SLIP_TRANSFER_BCA_18550000.JPG',
                'evidence_note' => 'Status Metadata: Lengkap',
                'slip_sender' => 'PT KULINER MAKMUR S.',
                'slip_account' => 'BCA 5420-991-002',
                'slip_nominal' => 'RP 18.550.000,-',
                'slip_stamp' => '24/10/2024 10:15:22 WIB',
                'giro_account' => 'BCA 8820-192-001',
                'giro_time' => '24/10/2024 10:15 WIB',
                'giro_time_short' => '24/10 10:15 WIB',
                'giro_entry' => '[CR] Dana Masuk',
                'giro_nominal' => '+Rp 18.550.000',
                'match' => true,
                'due' => '28 Okt (TOP 7)',
                'note' => 'Mutasi BCA jam 10:15 WIB valid dan klop. Disetujui untuk cetak kuitansi sah.',
                'status' => 'inspect',
            ],
            [
                'id' => 'PAY-0920',
                'invoice' => 'INV-202410-0090',
                'channel' => 'giro',
                'channel_label' => 'Mandiri Giro B2B',
                'time' => '09:55',
                'client' => 'UD Berkah Pangan Abadi',
                'client_short' => 'UD Berkah Pangan Abadi',
                'amount' => 9400000,
                'amount_label' => 'Rp 9.400.000',
                'evidence' => 'Warkat Kliring',
                'evidence_type' => 'PDF (412KB)',
                'evidence_file' => 'WARKAT_KLIRING_9400000.PDF',
                'evidence_note' => 'Status Metadata: Lengkap',
                'slip_sender' => 'UD BERKAH PANGAN A.',
                'slip_account' => 'MANDIRI 1180-0042-7',
                'slip_nominal' => 'RP 9.400.000,-',
                'slip_stamp' => '24/10/2024 09:52:41 WIB',
                'giro_account' => 'MANDIRI 0088-112-334',
                'giro_time' => '24/10/2024 09:52 WIB',
                'giro_time_short' => '24/10 09:52 WIB',
                'giro_entry' => '[CR] Dana Masuk',
                'giro_nominal' => '+Rp 9.400.000',
                'match' => true,
                'due' => '23 Nov (TOP 30)',
                'note' => 'Warkat kliring original serta mutasi giro menunggu pencocokan teller.',
                'status' => 'pending',
            ],
            [
                'id' => 'PAY-0919',
                'invoice' => 'INV-202410-0088',
                'channel' => 'qris',
                'channel_label' => 'QRIS Statis GPA',
                'time' => '09:41',
                'client' => 'Koperasi Tani Mitra Mandiri',
                'client_short' => 'Koperasi Tani Mitra Mandiri',
                'amount' => 2850000,
                'amount_label' => 'Rp 2.850.000',
                'evidence' => 'Screenshot QR',
                'evidence_type' => 'PNG (280KB)',
                'evidence_file' => 'QRIS_MID_GPA_9012.PNG',
                'evidence_note' => 'Status Metadata: Lengkap',
                'slip_sender' => 'KOPERASI TANI MITRA M.',
                'slip_account' => 'QRIS MID-GPA-9012',
                'slip_nominal' => 'RP 2.850.000,-',
                'slip_stamp' => '24/10/2024 09:40:12 WIB',
                'giro_account' => 'BCA 8820-192-001',
                'giro_time' => '24/10/2024 09:40 WIB',
                'giro_time_short' => '24/10 09:40 WIB',
                'giro_entry' => '[CR] Dana Masuk',
                'giro_nominal' => '+Rp 2.850.000',
                'match' => true,
                'due' => '07 Nov (TOP 14)',
                'note' => 'Scan QRIS valid, menunggu konfirmasi jenis entry pada rekap kas harian.',
                'status' => 'pending',
            ],
            [
                'id' => 'PAY-0918',
                'invoice' => 'INV-202410-0087',
                'channel' => 'bank',
                'channel_label' => 'BCA Transfer Manual',
                'time' => '09:12',
                'client' => 'Dapur Katering Sejahtera',
                'client_short' => 'Dapur Katering Sejahtera',
                'amount' => 4650000,
                'amount_label' => 'Rp 4.650.000',
                'evidence' => 'Resi ATM Fisik',
                'evidence_type' => 'JPG (512KB)',
                'evidence_file' => 'RESI_ATM_4650000.JPG',
                'evidence_note' => 'Status Metadata: Lengkap',
                'slip_sender' => 'DAPUR KATERING SEJAHTERA',
                'slip_account' => 'BCA 2209-114-780',
                'slip_nominal' => 'RP 4.650.000,-',
                'slip_stamp' => '24/10/2024 09:10:02 WIB',
                'giro_account' => 'BCA 8820-192-001',
                'giro_time' => '24/10/2024 09:09 WIB',
                'giro_time_short' => '24/10 09:09 WIB',
                'giro_entry' => '[CR] Dana Masuk',
                'giro_nominal' => '+Rp 4.650.000',
                'match' => true,
                'due' => '07 Nov (TOP 14)',
                'note' => 'Resi ATM fisik perlu dicocokkan dengan mutasi rekening koran harian.',
                'status' => 'pending',
            ],
            [
                'id' => 'PAY-0917',
                'invoice' => 'INV-202410-0086',
                'channel' => 'giro',
                'channel_label' => 'Mandiri Giro',
                'time' => '08:30',
                'client' => 'CV Sayur Segar Lestari',
                'client_short' => 'CV Sayur Segar Lestari',
                'amount' => 3000000,
                'amount_label' => 'Rp 3.000.000',
                'evidence' => 'Bukti Setor Bank',
                'evidence_type' => 'JPG (388KB)',
                'evidence_file' => 'SETOR_MANDIRI_3000000.JPG',
                'evidence_note' => 'Status Metadata: Lengkap',
                'slip_sender' => 'CV SAYUR SEGAR LESTARI',
                'slip_account' => 'MANDIRI 0091-556-223',
                'slip_nominal' => 'RP 3.000.000,-',
                'slip_stamp' => '24/10/2024 08:28:35 WIB',
                'giro_account' => 'MANDIRI 0088-112-334',
                'giro_time' => '24/10/2024 08:28 WIB',
                'giro_time_short' => '24/10 08:28 WIB',
                'giro_entry' => '[CR] Dana Masuk',
                'giro_nominal' => '+Rp 3.000.000',
                'match' => true,
                'due' => '07 Nov (TOP 14)',
                'note' => 'Bukti setor bank menunggu konfirmasi teller dan cetak rekening koran.',
                'status' => 'pending',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function inspection(): array
    {
        return [
            'title' => 'Inspeksi Bukti & Aksi Otorisasi',
            'operator_code' => 'OP-4091',
            'ref_suffix' => ' // Tiket Rekonsiliasi OP-4091',
            'chip' => 'Siap Diaudit',
            'meta' => [
                'client' => 'Nama Klien / PT',
                'invoice' => 'Nomor Invoice',
                'amount' => 'Nilai Tagihan',
                'due' => 'Jatuh Tempo',
            ],
            'slip_title' => 'Slip Transfer Unggahan',
            'slip_footer' => 'Perbesar Dokumen',
            'mutation_title' => 'Mutasi Giro Bank GPA (Live)',
            'mutation_chip' => 'Match 100%',
            'mutation_note' => 'Nominal slip & rekening koran identik.',
            'mutation_fields' => [
                'account' => 'Rek Giro',
                'time' => 'Waktu Mutasi',
                'entry' => 'Jenis Entry',
                'amount' => 'Nominal Mutasi',
            ],
            'checklist_title' => 'Checklist Protokol Audit (PRD Rule 11.2):',
            'checklist' => [
                [
                    'key' => 'amount_label',
                    'before' => 'Nominal slip sama persis dengan mutasi bank (',
                    'after' => ')',
                ],
                [
                    'key' => 'giro_account',
                    'before' => 'Rekening tujuan sah sesuai Giro GPA Perusahaan (',
                    'after' => ')',
                ],
                [
                    'key' => 'giro_time_short',
                    'before' => 'Tanggal & jam mutasi valid di perbankan (',
                    'after' => ')',
                ],
            ],
            'note_label' => 'Catatan Verifikasi Sekre / Rekap Audit:',
            'note_stamp' => 'Stamp Audit Log',
            'approve_label' => 'Approve & Settled Lunas',
            'reject_label' => 'Tolak Bukti Bayar',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function audit(): array
    {
        return [
            'title' => 'Riwayat Log Verifikasi Pembayaran Terakhir',
            'subtitle' => 'Ledger Buku Kas Realtime • Dilindungi Hash Kriptografi Immutable',
            'hash_label' => 'SHA-256: 7f8a9e4d01b92a3c8e54c03b (Immutable)',
            'columns' => [
                'timestamp' => 'Timestamp',
                'payment' => 'ID Bayar',
                'invoice' => 'No. Faktur',
                'client' => 'Klien',
                'amount' => 'Nominal',
                'action' => 'Aksi & Hasil',
                'operator' => 'Petugas Sekre',
                'note' => 'Catatan Audit',
            ],
            'rows' => self::auditRows(),
            'total_transactions' => 42,
            'pagination' => [
                'previous' => 'Sebelumnya',
                'pages' => [1, 2, 3],
                'next' => 'Selanjutnya',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function auditRows(): array
    {
        return [
            [
                'timestamp' => '24/10 10:04:12',
                'id' => 'PAY-0916',
                'invoice' => 'INV-202410-0085',
                'client' => 'PT Agro Boga Utama',
                'amount' => 22400000,
                'amount_label' => 'Rp 22.400.000',
                'amount_class' => 'text-ink',
                'status' => 'approved',
                'status_label' => 'Approved / Lunas',
                'status_class' => 'bg-accent text-success-deep outline outline-1 outline-accent-deep',
                'operator' => 'OP-4091',
                'note' => 'Mutasi Mandiri Giro matched. Kuitansi K-089 terbit.',
            ],
            [
                'timestamp' => '24/10 09:32:05',
                'id' => 'PAY-0915',
                'invoice' => 'INV-202410-0084',
                'client' => 'CV Prima Buah Sejahtera',
                'amount' => 14150000,
                'amount_label' => 'Rp 14.150.000',
                'amount_class' => 'text-ink',
                'status' => 'approved',
                'status_label' => 'Approved / Lunas',
                'status_class' => 'bg-accent text-success-deep outline outline-1 outline-accent-deep',
                'operator' => 'OP-4091',
                'note' => 'Slip transfer bank BCA terverifikasi valid jam 09:30.',
            ],
            [
                'timestamp' => '24/10 08:50:22',
                'id' => 'PAY-0914',
                'invoice' => 'INV-202410-0080',
                'client' => 'Resto Nusantara Megah',
                'amount' => 8200000,
                'amount_label' => 'Rp 8.200.000',
                'amount_class' => 'text-danger',
                'status' => 'rejected',
                'status_label' => 'Rejected',
                'status_class' => 'bg-danger text-white outline outline-1 outline-danger',
                'operator' => 'OP-4091',
                'note' => 'Nominal transfer kurang Rp 500rb. Eskalasi ke sales invoice.',
            ],
            [
                'timestamp' => '24/10 08:15:40',
                'id' => 'PAY-0913',
                'invoice' => 'INV-202410-0078',
                'client' => 'Katering Melati Harmoni',
                'amount' => 5750000,
                'amount_label' => 'Rp 5.750.000',
                'amount_class' => 'text-ink',
                'status' => 'approved',
                'status_label' => 'Approved / Lunas',
                'status_class' => 'bg-accent text-success-deep outline outline-1 outline-accent-deep',
                'operator' => 'OP-4091',
                'note' => 'QRIS scan valid dari dashboard MID-GPA-9012.',
            ],
        ];
    }
}
