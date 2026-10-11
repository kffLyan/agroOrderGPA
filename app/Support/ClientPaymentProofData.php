<?php

namespace App\Support;

/**
 * Data preview untuk halaman "Unggah Bukti Pembayaran & Pelacakan Status
 * Verifikasi Tagihan" milik klien B2B.
 *
 * Seluruh angka mengikuti design formulir resmi: nilai tagihan kotor, kompensasi
 * retur hasil dock (Berita-Acara), nominal akhir yang wajib dtransfer, rekening
 * settlement GPA, dan empat tahap verifikasi manual PRD Rule 11.
 *
 * Halaman ini adalah turunan dari Pusat Dokumen & Faktur: daftar tagihan diambil
 * dari {@see ClientDocumentsData::invoices()} dan hanya menyisakan faktur yang
 * belum lunas, lalu setiap faktur diberi penyesuaian pembayaran khusus.
 */
final class ClientPaymentProofData
{
    /**
     * Penyesuaian pembayaran yang berlaku setelah faktur dicetak dan dikunci.
     *
     * Nilai negatif mengurangi tagihan owing dipindahkan ke faktur berikutnya.
     *
     * @var array<string, array{label: string, amount: int}>
     */
    private const ADJUSTMENTS = [
        'INV-GPA-202610-04' => [
            'label' => 'Kompensasi Retur (10 kg Kol Putih - Berita Acara #BA-991)',
            'amount' => -90000,
        ],
    ];

    /**
     * Kop halaman beserta chip PRD Rule 11.
     *
     * @return array<string, string>
     */
    public static function header(): array
    {
        return [
            'title' => 'Unggah Bukti Pembayaran & Pelacakan Status Verifikasi Tagihan',
            'subtitle' => 'Formulir pelaporan transfer pembayaran manual (BCA / Mandiri / QRIS Statis) untuk faktur tempo bulanan atau pesanan reguler sesuai PRD App-GPA.md Section 15 & Rule 11. Pembayaran diverifikasi oleh Staf Sekretariat/Keuangan GPA dalam maks 2 jam kerja.',
            'rule_chip' => 'Wajib PRD Rule 11: Verifikasi Manual Sekre Diperlukan',
            'source_label' => 'Turunan Pusat Dokumen & Faktur',
            'source_note' => 'Formulir ini berada di dalam modul Dokumen & Faktur GPA. Status tagihan dan rekening settlement selalu mengikuti master faktur konsolidasi yang sama.',
        ];
    }

    /**
     * Tagihan belum lunas yang dapat dipilih pada langkah pertama formulir.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function invoices(): array
    {
        return collect(ClientDocumentsData::invoices())
            ->reject(static fn (array $invoice): bool => str_contains($invoice['status'], 'Lunas'))
            ->map(static function (array $invoice): array {
                $adjustment = self::ADJUSTMENTS[$invoice['po']] ?? [
                    'label' => 'Kredit Penyesuaian Retur',
                    'amount' => 0,
                ];

                $payable = $invoice['total'] + $adjustment['amount'];

                return [
                    'po' => $invoice['po'],
                    'period_label' => $invoice['period_label'],
                    'due' => $invoice['due'],
                    'due_note' => $invoice['due_note'],
                    'status' => $invoice['status'],
                    'status_chip' => 'Status: Belum Lunas',
                    'gross' => $invoice['total'],
                    'gross_label' => self::rupiah($invoice['total']),
                    'credit_label' => $adjustment['label'],
                    'credit' => $adjustment['amount'],
                    'credit_value_label' => $adjustment['amount'] === 0
                        ? '- Rp 0'
                        : '-'.self::rupiah(abs($adjustment['amount'])),
                    'has_credit' => $adjustment['amount'] !== 0,
                    'payable' => $payable,
                    'payable_label' => self::rupiah($payable),
                    'payable_digits' => number_format($payable, 0, ',', '.'),
                    'option_label' => sprintf(
                        '%s (Periode %s) — Total: %s — Jatuh Tempo: %s',
                        $invoice['po'],
                        $invoice['period_label'],
                        self::rupiah($invoice['total']),
                        $invoice['due'],
                    ),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Rekening settlement resmi GPA untuk seluruh faktur tempo.
     *
     * @return array<string, string>
     */
    public static function account(): array
    {
        return [
            'eyebrow' => 'Rekening Resmi Settlement AgroOrder GPA',
            'bank' => 'Bank BCA KCU Bogor',
            'short' => 'BCA',
            'number' => '128-094-8891',
            'account_label' => '128-094-8891',
            'holder' => 'a/n PT Agro Pasti Ada (CV Gema Perkasa)',
            'action' => 'Salin No. Rek',
            'note' => 'Transfer hanya sah ke rekening di atas. Rekening lain tidak diproses sebagai pembayaran GPA.',
        ];
    }

    /**
     * Aturan berkas slip transfer yang diterima modul unggah bukti.
     *
     * @return array<string, mixed>
     */
    public static function fileRules(): array
    {
        return [
            'accept' => ['jpg', 'jpeg', 'png', 'pdf'],
            'max_kb' => 5120,
            'max_label' => 'Maks 5 MB',
            'format_label' => 'Format: JPG, PNG, PDF (Maks 5 MB)',
            'mime' => 'image/jpeg,image/png,application/pdf',
        ];
    }

    /**
     * Isi form perangkons-transfer yang sudah dicontoh pada design.
     *
     * @return array<string, mixed>
     */
    public static function form(): array
    {
        return [
            'sender_bank' => 'Bank Central Asia (BCA) / PT Kuliner Prima Nusantara',
            'sender_account' => '014-992-1102',
            'transfer_at' => '24 Oktober 2026, 10:15 WIB',
            'reference' => 'TRX-BCA-20241024-99812',
            'note' => 'Pelunasan faktur konsolidasi termin 1 periode Oktober 2026. Mohon update limit kredit gudang utama.',
        ];
    }

    /**
     * Berkas slip transfer bawaan agar pratinjau formulir tidak kosong.
     *
     * @return array<string, string>
     */
    public static function file(): array
    {
        return [
            'name' => 'slip_transfer_bca_inv004_kpn.png',
            'size_label' => '840 KB',
            'time_label' => '24 Okt 2026, 10:15 WIB',
            'hash_label' => 'SHA-256 Valid',
        ];
    }

    /**
     * Pernyataan kepatuhan wajib sebelum bukti dikirim ke Sekretariat.
     *
     * @return array<string, string>
     */
    public static function compliance(): array
    {
        return [
            'title' => 'Pernyataan Kepatuhan:',
            'body' => 'Saya menyatakan bahwa dana telah berhasil didebet dari rekening resmi perusahaan dan slip pembayaran ini asli tanpa rekayasa. Saya memahami manipulasi berkas dapat berakibat pemblokiran fasilitas TOP B2B.',
        ];
    }

    /**
     * Empat tahap verifikasi manual sebelum kuitansi sah terbit.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function steps(): array
    {
        return [
            [
                'no' => '1',
                'title' => 'Bukti Pembayaran Terunggah',
                'meta' => '24 Okt 2026, 10:18 WIB • IP 182.253.110.4',
                'body' => 'Dokumen slip transfer telah diterima oleh gateway secure upload AgroOrder.',
                'tone' => 'done',
                'chip' => null,
            ],
            [
                'no' => '2',
                'title' => 'Menunggu Pemeriksaan Admin/Sekre',
                'meta' => 'Estimasi Antrean: ~45 Menit (Maks 2 Jam Kerja)',
                'body' => 'Staf keuangan GPA (PIC: Ibu Rina Kartika - Kasir Pusat) sedang memvalidasi data nominal & kesesuaian rekening.',
                'tone' => 'active',
                'chip' => 'Proses',
            ],
            [
                'no' => '3',
                'title' => 'Rekonsiliasi Mutasi Rekening Koran Bank GPA',
                'meta' => 'KlikBCA Bisnis Corporate Settlement Feed',
                'body' => 'Pengecekan entri kredit dana masuk pada buku besar perbankan agribisnis.',
                'tone' => 'pending',
                'chip' => null,
            ],
            [
                'no' => '4',
                'title' => 'Penerbitan Kuitansi Lunas & Pemulihan Plafon Kredit TOP',
                'meta' => 'Faktur Pajak & Kwitansi Resmi Elektronik',
                'body' => 'Otomatisasi pemulihan limit transaksi pemesanan batch berikutnya.',
                'tone' => 'pending',
                'chip' => null,
            ],
        ];
    }

    /**
     * Pemberitahuan wajib PRD Rule 11 & Section 15.2.
     *
     * @return array<string, string>
     */
    public static function notice(): array
    {
        return [
            'eyebrow' => 'Pemberitahuan PRD Rule 11 & Section 15.2',
            'body' => 'Pengunggahan bukti transfer BUKAN berarti tagihan otomatis lunas seketika. Staf Sekre/Admin akan mencocokkan mutasi rekening koran dan menyetujui (Approve) pembayaran sebelum kuitansi sah diterbitkan dan limit kredit dipulihkan.',
        ];
    }

    /**
     * Kontak darurat saat rekonsiliasi belum selesai dalam 2 jam kerja.
     *
     * @return array<string, string>
     */
    public static function hotline(): array
    {
        return [
            'title' => 'Butuh Konfirmasi Darurat?',
            'body' => 'Hotline Sekretariat GPA: (021) 8820-9912 ext 4',
            'action' => 'Hubungi PIC',
        ];
    }

    /**
     * Ringkasan plafon kredit TOP term sebelum dan sesudah verifikasi.
     *
     * @return array<string, mixed>
     */
    public static function plafon(): array
    {
        $approved = 150000000;
        $outstanding = 42100000;
        $payable = self::invoices()[0]['payable'] ?? 0;

        return [
            'title' => 'Ringkasan Plafon Kredit (TOP Term)',
            'approved_label' => 'Plafon Disetujui',
            'approved_value' => self::digits($approved),
            'outstanding_label' => 'Sisa Plafon Berjalan',
            'outstanding_value' => self::digits($outstanding),
            'projected_label' => self::digits($outstanding + $payable),
            'note' => sprintf(
                'Setelah verifikasi %s disetujui, plafon akan pulih menjadi %s.',
                self::rupiah($payable),
                self::digits($outstanding + $payable),
            ),
        ];
    }

    /**
     * Header tabel riwayat pembayaran & verifikasi.
     *
     * @return array<int, array<string, string>>
     */
    public static function historyColumns(): array
    {
        return [
            ['key' => 'id', 'label' => 'ID Pembayaran'],
            ['key' => 'invoice', 'label' => 'No. Faktur'],
            ['key' => 'date', 'label' => 'Tanggal Transfer'],
            ['key' => 'amount', 'label' => 'Nominal', 'align' => 'right'],
            ['key' => 'method', 'label' => 'Metode'],
            ['key' => 'verifier', 'label' => 'Verifikator Sekre'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'receipt', 'label' => 'Kuitansi Sah', 'align' => 'right'],
        ];
    }

    /**
     * Riwayat pelunasan transfer 60 hari terakhir.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function history(): array
    {
        return [
            [
                'id' => 'PAY-20261024-009',
                'invoice' => 'INV-GPA-202610-04',
                'date' => '24 Okt 2026, 10:15 WIB',
                'amount' => 38560000,
                'amount_label' => self::rupiah(38560000),
                'method' => 'BCA Corp',
                'verifier' => 'Antrean Sekre GPA',
                'verifier_pending' => true,
                'status' => 'Menunggu Verifikasi',
                'status_tone' => 'warning',
                'receipt' => 'Draf Pending',
                'receipt_pending' => true,
                'row_tone' => 'muted',
            ],
            [
                'id' => 'PAY-20260914-041',
                'invoice' => 'INV-GPA-202609-02',
                'date' => '14 Sep 2026, 14:22 WIB',
                'amount' => 42150000,
                'amount_label' => self::rupiah(42150000),
                'method' => 'Mandiri H2H',
                'verifier' => 'Bambang S. (Fin-02)',
                'verifier_pending' => false,
                'status' => 'Lunas / Terverifikasi',
                'status_tone' => 'success',
                'receipt' => 'KWT-0914.PDF',
                'receipt_pending' => false,
                'row_tone' => 'plain',
            ],
            [
                'id' => 'PAY-20260829-018',
                'invoice' => 'INV-GPA-202608-20',
                'date' => '29 Agu 2026, 09:10 WIB',
                'amount' => 19800000,
                'amount_label' => self::rupiah(19800000),
                'method' => 'BCA Corp',
                'verifier' => 'Rina K. (Kasir Pusat)',
                'verifier_pending' => false,
                'status' => 'Lunas / Terverifikasi',
                'status_tone' => 'success',
                'receipt' => 'KWT-0829.PDF',
                'receipt_pending' => false,
                'row_tone' => 'plain',
            ],
        ];
    }

    /**
     * Label aksi sekunder pada kartu riwayat.
     *
     * @return array<string, string>
     */
    public static function exportReport(): array
    {
        return [
            'label' => 'Unduh Laporan Rekonsiliasi (.XLSX)',
            'short' => 'Unduh Laporan Rekonsiliasi (.XLSX)',
            'body' => 'Daftar transaksi pelunasan transfer bank PT Kuliner Prima Nusantara dalam 60 hari terakhir.',
        ];
    }

    private static function rupiah(int $value): string
    {
        return 'Rp '.self::digits($value);
    }

    private static function digits(int $value): string
    {
        return number_format($value, 0, ',', '.');
    }
}
