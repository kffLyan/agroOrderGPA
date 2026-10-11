<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Data preview untuk halaman "Pusat Dokumen & Faktur Konsolidasi Klien B2B".
 *
 * Seluruh angka mengikuti design dokumen resmi: faktur konsolidasi tempo bulanan
 * (TOP 30D), lampiran surat jalan, rincian PPN, dan rekening pembayaran GPA.
 */
final class ClientDocumentsData
{
    /**
     * Kop halaman beserta dua aksi utama.
     *
     * @return array<string, mixed>
     */
    public static function header(): array
    {
        return [
            'title' => 'Pusat Dokumen & Faktur Konsolidasi Klien B2B',
            'chip' => 'Enterprise Top 30D',
            'subtitle' => 'Arsip terpadu Surat Jalan digital, bukti serah terima (PoD), faktur konsolidasi tempo bulanan (TOP), dan tanda terima pembayaran sah berbasis actual net weight (PRD Rule 04, 05, 14, 15).',
            'actions' => [
                [
                    'label' => 'Unduh Rekap Pajak E-Faktur (.XML/.PDF)',
                    'short' => 'Rekap Pajak E-Faktur',
                    'icon' => 'download',
                    'variant' => 'outline',
                ],
                [
                    'label' => 'Ajukan Rekonsiliasi Tagihan',
                    'short' => 'Ajukan Rekonsiliasi',
                    'icon' => 'scale',
                    'variant' => 'solid',
                ],
            ],
        ];
    }

    /**
     * Empat kartu metrik treasury & audit.
     *
     * @return array<int, array<string, string>>
     */
    public static function metrics(): array
    {
        return [
            [
                'label' => 'Metrik 01 // Payable',
                'icon' => 'wallet',
                'tone' => 'warning',
                'title' => 'Tagihan Berjalan TOP 30D',
                'value' => 'Rp 52.400.000',
                'foot_left' => '2 Faktur Konsolidasi',
                'foot_right' => 'Jatuh Tempo: 15 Nov 2026',
            ],
            [
                'label' => 'Metrik 02 // Settled YTD',
                'icon' => 'check-circle',
                'tone' => 'accent',
                'title' => 'Total Terbayar YTD (2026)',
                'value' => 'Rp 418.920.000',
                'foot_left' => '18 Faktur Lunas',
                'foot_right' => '0 Sengketa / Disputa',
            ],
            [
                'label' => 'Metrik 03 // Audit Vault',
                'icon' => 'save',
                'tone' => 'neutral',
                'title' => 'Dokumen PoD Tersimpan',
                'value' => '142 Dokumen',
                'foot_left' => 'Vault Kriptografis',
                'foot_right' => '100% Stempel & TTD Digital',
            ],
            [
                'label' => 'Metrik 04 // Adjustment',
                'icon' => 'refresh',
                'tone' => 'accent-soft',
                'title' => 'Diskon & Kompensasi Retur',
                'value' => 'Rp 532.500',
                'foot_left' => '1 Kasus Fisik Dock',
                'foot_right' => 'Auto-offset Faktur Berikut',
            ],
        ];
    }

    /**
     * Empat arsip dokumen dengan jumlah lampiran.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function tabs(): array
    {
        return [
            [
                'value' => 'faktur',
                'label' => 'Faktur Konsolidasi TOP (Faktur Bulanan)',
                'icon' => 'invoice',
                'badge' => '2 Aktif',
                'archive' => 'faktur konsolidasi tempo bulanan',
            ],
            [
                'value' => 'sj',
                'label' => 'Arsip Surat Jalan Digital (DO / SJ-GPA)',
                'icon' => 'truck',
                'badge' => null,
                'archive' => 'surat jalan digital',
            ],
            [
                'value' => 'pod',
                'label' => 'Bukti Serah Terima Fisik (PoD & TTD Dapur)',
                'icon' => 'badge-check',
                'badge' => null,
                'archive' => 'bukti serah terima fisik',
            ],
            [
                'value' => 'bayar',
                'label' => 'Riwayat Pembayaran & Bukti Transfer',
                'icon' => 'banknote',
                'badge' => null,
                'archive' => 'bukti transfer pembayaran',
            ],
        ];
    }

    /**
     * Opsi filter periode rekap faktur.
     *
     * @return array<int, array<string, string>>
     */
    public static function periods(): array
    {
        return [
            ['value' => '2026-q4', 'label' => 'Kuartal 4 (Okt - Des 2026)'],
            ['value' => '2026-q3', 'label' => 'Kuartal 3 (Jul - Sep 2026)'],
            ['value' => '2026-q2', 'label' => 'Kuartal 2 (Apr - Jun 2026)'],
            ['value' => '2025-arsip', 'label' => 'Arsip Tahun 2025'],
        ];
    }

    /**
     * Header tabel faktur konsolidasi.
     *
     * @return array<int, array<string, string>>
     */
    public static function columns(): array
    {
        return [
            ['key' => 'no', 'label' => 'No', 'align' => 'left'],
            ['key' => 'invoice', 'label' => 'No. Faktur & Tgl Cetak', 'align' => 'left'],
            ['key' => 'period', 'label' => 'Periode Rekap', 'align' => 'left'],
            ['key' => 'netto', 'label' => 'Netto Riil', 'align' => 'right'],
            ['key' => 'total', 'label' => 'Total Tagihan', 'align' => 'right'],
            ['key' => 'due', 'label' => 'Jatuh Tempo', 'align' => 'left'],
            ['key' => 'status', 'label' => 'Status', 'align' => 'left'],
            ['key' => 'actions', 'label' => 'Aksi Dokumen', 'align' => 'center'],
        ];
    }

    /**
     * Tiga faktur konsolidasi beserta lampiran surat jalannya.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function invoices(): array
    {
        $invoices = [
            [
                'po' => 'INV-GPA-202610-04',
                'no' => '01',
                'printed' => '16 Okt 2026',
                'period_label' => '01 Okt - 15 Okt 2026',
                'period' => '2026-q4',
                'sj_count' => 5,
                'sj_chip' => '5 Surat Jalan',
                'sj_chip_muted' => false,
                'netto' => 4120.5,
                'netto_weight' => 600,
                'total' => 38650000,
                'total_weight' => 700,
                'due' => '15 Nov 2026',
                'due_struck' => false,
                'due_muted' => false,
                'due_note' => 'Sisa 21 Hari TOP 30D',
                'due_note_tone' => 'success',
                'status' => 'Menunggu Pembayaran',
                'status_tone' => 'warning',
                'status_kind' => 'dot',
                'row_tone' => 'active',
                'tempo_days' => 30,
                'actions' => [
                    ['label' => 'Unduh PDF Faktur', 'icon' => 'download', 'variant' => 'ghost', 'shape' => 'icon'],
                    ['label' => 'Buka E-Faktur XML', 'icon' => 'external-link', 'variant' => 'ghost', 'shape' => 'icon'],
                    ['label' => 'Bayar', 'icon' => null, 'variant' => 'solid', 'shape' => 'label'],
                ],
                'attachments' => [
                    ['sj' => 'SJ-GPA-202610-0081', 'commodity' => 'Tomat Beef Grade A', 'hub' => 'Gudang Utama Dapur Prima', 'ttd' => 'Spv. Dwi', 'amount' => 5784000, 'weight' => 482.0],
                    ['sj' => 'SJ-GPA-202610-0084', 'commodity' => 'Selada Kriting Hidroponik', 'hub' => 'Gudang Barat Cold Hub', 'ttd' => 'Staff Agung', 'amount' => 12750000, 'weight' => 850.0],
                    ['sj' => 'SJ-GPA-202610-0086', 'commodity' => 'Brokoli Manis Lembang', 'hub' => 'Central Processing Unit', 'ttd' => 'Chef Ridwan', 'amount' => 7200000, 'weight' => 300.0],
                    ['sj' => 'SJ-GPA-202610-0089', 'commodity' => 'Kol Putih Dataran Tinggi', 'hub' => 'Gudang Utama Dapur Prima', 'ttd' => 'Staff Agung', 'amount' => 3200000, 'weight' => 400.0],
                    ['sj' => 'SJ-GPA-202610-0091', 'commodity' => 'Selada Romaine Resto Pack', 'hub' => 'Gudang Barat Cold Hub', 'ttd' => 'Spv. Dwi', 'amount' => 9716000, 'weight' => 650.0],
                ],
            ],
            [
                'po' => 'INV-GPA-202609-02',
                'no' => '02',
                'printed' => '16 Sep 2026',
                'period_label' => '16 Sep - 30 Sep 2026',
                'period' => '2026-q3',
                'sj_count' => 4,
                'sj_chip' => '4 Surat Jalan',
                'sj_chip_muted' => true,
                'netto' => 4890.0,
                'netto_weight' => 400,
                'total' => 42100000,
                'total_weight' => 600,
                'due' => '15 Okt 2026',
                'due_struck' => true,
                'due_muted' => false,
                'due_note' => 'Lunas Tgl 15 Okt',
                'due_note_tone' => 'success',
                'status' => 'Lunas & Terverifikasi',
                'status_tone' => 'accent',
                'status_kind' => 'check',
                'row_tone' => 'plain',
                'tempo_days' => 30,
                'actions' => [
                    ['label' => 'Lihat Lampiran', 'icon' => 'eye', 'variant' => 'ghost', 'shape' => 'icon'],
                    ['label' => 'Unduh PDF Faktur', 'icon' => 'download', 'variant' => 'ghost', 'shape' => 'icon'],
                ],
                'attachments' => [
                    ['sj' => 'SJ-GPA-202609-0052', 'commodity' => 'Brokoli Manis Lembang', 'hub' => 'Central Processing Unit', 'ttd' => 'Chef Ridwan', 'amount' => 9450000, 'weight' => 640.0],
                    ['sj' => 'SJ-GPA-202609-0055', 'commodity' => 'Selada Kriting Hidroponik', 'hub' => 'Gudang Barat Cold Hub', 'ttd' => 'Staff Agung', 'amount' => 12750000, 'weight' => 850.0],
                    ['sj' => 'SJ-GPA-202609-0058', 'commodity' => 'Tomat Beef Grade A', 'hub' => 'Gudang Utama Dapur Prima', 'ttd' => 'Spv. Dwi', 'amount' => 11200000, 'weight' => 1250.0],
                    ['sj' => 'SJ-GPA-202609-0061', 'commodity' => 'Kol Putih Dataran Tinggi', 'hub' => 'Gudang Utama Dapur Prima', 'ttd' => 'Staff Agung', 'amount' => 8700000, 'weight' => 2150.0],
                ],
            ],
            [
                'po' => 'INV-GPA-202609-01',
                'no' => '03',
                'printed' => '01 Sep 2026',
                'period_label' => '01 Sep - 15 Sep 2026',
                'period' => '2026-q3',
                'sj_count' => 6,
                'sj_chip' => '6 Surat Jalan',
                'sj_chip_muted' => true,
                'netto' => 5320.0,
                'netto_weight' => 400,
                'total' => 46250000,
                'total_weight' => 500,
                'due' => '30 Sep 2026',
                'due_struck' => true,
                'due_muted' => true,
                'due_note' => 'Lunas Tgl 28 Sep',
                'due_note_tone' => 'quiet',
                'status' => 'Arsip Lunas',
                'status_tone' => 'muted',
                'status_kind' => 'none',
                'row_tone' => 'muted',
                'tempo_days' => 30,
                'actions' => [
                    ['label' => 'Unduh PDF Faktur', 'icon' => 'download', 'variant' => 'ghost', 'shape' => 'icon'],
                ],
                'attachments' => [
                    ['sj' => 'SJ-GPA-202609-0011', 'commodity' => 'Selada Romaine Resto Pack', 'hub' => 'Gudang Barat Cold Hub', 'ttd' => 'Spv. Dwi', 'amount' => 8950000, 'weight' => 950.0],
                    ['sj' => 'SJ-GPA-202609-0014', 'commodity' => 'Tomat Beef Grade A', 'hub' => 'Gudang Utama Dapur Prima', 'ttd' => 'Spv. Dwi', 'amount' => 9100000, 'weight' => 1020.0],
                    ['sj' => 'SJ-GPA-202609-0017', 'commodity' => 'Kol Putih Dataran Tinggi', 'hub' => 'Gudang Utama Dapur Prima', 'ttd' => 'Staff Agung', 'amount' => 3200000, 'weight' => 400.0],
                    ['sj' => 'SJ-GPA-202609-0020', 'commodity' => 'Selada Kriting Hidroponik', 'hub' => 'Gudang Barat Cold Hub', 'ttd' => 'Staff Agung', 'amount' => 10500000, 'weight' => 1150.0],
                    ['sj' => 'SJ-GPA-202609-0023', 'commodity' => 'Brokoli Manis Lembang', 'hub' => 'Central Processing Unit', 'ttd' => 'Chef Ridwan', 'amount' => 7200000, 'weight' => 880.0],
                    ['sj' => 'SJ-GPA-202609-0026', 'commodity' => 'Timun Korea Segar', 'hub' => 'Gudang Utama Dapur Prima', 'ttd' => 'Spv. Dwi', 'amount' => 7300000, 'weight' => 920.0],
                ],
            ],
        ];

        return array_map(static fn (array $invoice): array => self::decorate($invoice), $invoices);
    }

    /**
     * Menyederhanakan label rupiah, bobot, dan rincian lampiran.
     *
     * @param  array<string, mixed>  $invoice
     * @return array<string, mixed>
     */
    private static function decorate(array $invoice): array
    {
        $subtotal = array_sum(array_column($invoice['attachments'], 'amount'));
        $invoice['subtotal'] = $subtotal;
        $invoice['netto_value'] = number_format($invoice['netto'], 1, ',', '.');
        $invoice['netto_unit'] = 'kg';
        $invoice['netto_net_label'] = $invoice['netto_value'].' KG NET';
        $invoice['total_label'] = self::rupiah($invoice['total']);
        $invoice['subtotal_label'] = self::rupiah($subtotal);
        $invoice['ppn_label'] = 'PPN Agribisnis Komoditas Segar (0%)';
        $invoice['ppn_value'] = 'Rp 0 (Dibebaskan)';
        $invoice['credit_label'] = 'Kredit Penyesuaian Retur';
        $invoice['credit_value'] = '- Rp 0';
        $invoice['grand_total_label'] = self::rupiah($invoice['total']);
        $invoice['attachments'] = array_map(static function (array $item, int $index): array {
            $item['no'] = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
            $item['amount_label'] = self::rupiah($item['amount']);
            $item['weight_label'] = number_format($item['weight'], 1, '.', '').' kg';
            $item['pod_chip'] = 'PoD Ada';

            return $item;
        }, $invoice['attachments'], array_keys($invoice['attachments']));

        return $invoice;
    }

    /**
     * Isi dropdown "Unggah Bukti" pada kop halaman Dokumen & Faktur.
     *
     * Satu-satunya halaman navigasi adalah modul Unggah Bukti Pembayaran; dua
     * item lain tetap berupa simulasi aksi dokumen GPA.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function proofMenu(): array
    {
        return [
            [
                'label' => 'Unggah Bukti Pembayaran',
                'note' => 'Formulir slip transfer & pelacakan verifikasi tagihan',
                'icon' => 'upload',
                'href' => route('documents.payment-proof'),
                'variant' => 'primary',
            ],
            [
                'label' => 'Salin Rekening Settlement GPA',
                'note' => '128-094-8891 • a/n PT Agro Pasti Ada (CV Gema Perkasa)',
                'icon' => 'copy',
                'href' => null,
                'variant' => 'secondary',
            ],
            [
                'label' => 'Ajukan Rekonsiliasi Tagihan',
                'note' => 'Discrepancy gross invoice vs bukti transfer',
                'icon' => 'scale',
                'href' => null,
                'variant' => 'secondary',
            ],
        ];
    }

    /**
     * Rekening pembayaran resmi GPA untuk seluruh faktur tempo.
     *
     * @return array<string, string>
     */
    public static function bank(): array
    {
        return [
            'label' => 'Rekening Pembayaran Resmi GPA',
            'bank' => 'Bank BCA KCU Bogor',
            'account' => 'No. Rek: 128-094-8891',
            'holder' => 'a/n PT Agro Pasti Ada',
            'action' => 'Unggah Bukti',
        ];
    }

    /**
     * Judul panel lampiran faktur terpilih.
     *
     * @return array<string, string>
     */
    public static function detail(): array
    {
        return [
            'title' => 'Detail Lampiran Faktur Resmi',
            'badge' => 'Aktif Terpilih',
            'strip' => 'Bobot Netto Dokumen',
        ];
    }

    /**
     * Jaminan validasi hukum di footer halaman.
     *
     * @return array<string, mixed>
     */
    public static function assurance(): array
    {
        return [
            'title' => 'Jaminan Validasi Hukum & Timbangan Riil GPA PRD 15.3',
            'body' => 'Semua berkas faktur tempo telah terkunci secara kriptografis (SHA-256 Digest). Segala selisih timbangan pada dock telah diselesaikan melalui Berita Acara Rekonsiliasi sebelum faktur dicetak.',
            'chips' => ['Kebijakan TOP 30D', 'Kontak Tim Finance'],
        ];
    }

    /**
     * Ringkasan footer tabel.
     *
     * @return array<string, int>
     */
    public static function pagination(): array
    {
        return [
            'total' => 18,
            'per_page' => 3,
            'last_page' => 6,
        ];
    }

    /**
     * Jumlah baris per tab arsip dokumen.
     *
     * @return Collection<string, int>
     */
    public static function archiveCounts(): Collection
    {
        return collect([
            'faktur' => 18,
            'sj' => 96,
            'pod' => 142,
            'bayar' => 18,
        ]);
    }

    private static function rupiah(int $value): string
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }
}
