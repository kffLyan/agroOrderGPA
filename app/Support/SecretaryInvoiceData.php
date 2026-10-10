<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data konsol penerbitan faktur penagihan & monitoring tempo (TOP) B2B (Sekretaris).
 *
 * Seluruh nilai di bawah adalah data simulasi yang mencerminkan design
 * "Manajemen Faktur Penagihan & Monitoring Tempo (TOP) Klien B2B"
 * (Rule 14 konsolidasi multi-SJ + Rule 05 POD & netto sah).
 */
class SecretaryInvoiceData
{
    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => SecretaryDashboardData::operator($user),
            'heading' => self::heading(),
            'metrics' => self::metrics(),
            'generator' => self::generator(),
            'clients' => self::clients(),
            'ledger' => self::ledger(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function heading(): array
    {
        return [
            'title_before' => 'Manajemen Faktur Penagihan &',
            'title_after' => 'Monitoring Tempo (TOP) Klien B2B',
            'subtitle_before' => 'Konsolidasi Surat Jalan selesai kirim (PoD valid & timbangan netto terverifikasi) menjadi lembar',
            'subtitle_rule' => 'faktur tagihan berkala (PRD App-GPA.md Section 14 & 15.3: Term of Payment 14/30/45 Hari).',
            'export_label' => 'Ekspor CSV/Ledger',
            'manual_label' => '+ Faktur Manual Baru',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function metrics(): array
    {
        return [
            [
                'key' => 'receivable',
                'label' => 'Total Piutang Berjalan (AR)',
                'value' => 'Rp 248.500.000',
                'icon' => 'wallet',
                'icon_tile' => 'bg-surface-track',
                'icon_class' => 'text-success-deep',
                'value_class' => 'text-ink',
                'foot_label' => '18 Klien Kontrak B2B Aktif',
                'foot_class' => 'text-ink-body',
                'chip' => [
                    'label' => '100% Termonitor',
                    'class' => 'bg-surface-pill text-ink-body outline outline-1 outline-line-board/70',
                ],
            ],
            [
                'key' => 'due_soon',
                'label' => 'Faktur Tempo (< 7 Hari)',
                'value' => 'Rp 45.200.000',
                'icon' => 'clock',
                'icon_tile' => 'bg-warning-soft/40',
                'icon_class' => 'text-warning-deep',
                'value_class' => 'text-warning-deep',
                'foot_label' => '5 Faktur Butuh Follow-up Sekre',
                'foot_class' => 'text-warning-deep',
                'chip' => [
                    'label' => 'Perhatian Segera',
                    'class' => 'bg-warning-soft/60 text-warning-deep outline outline-1 outline-warning/50',
                ],
            ],
            [
                'key' => 'overdue',
                'label' => 'Faktur Overdue (> Jatuh Tempo)',
                'value' => 'Rp 18.000.000',
                'icon' => 'alert-triangle',
                'icon_tile' => 'bg-danger-soft/50',
                'icon_class' => 'text-danger',
                'value_class' => 'text-danger',
                'foot_label' => '1 Klien Auto-Blocked',
                'foot_class' => 'text-danger',
                'chip' => [
                    'label' => 'Auto-Freeze Aktif',
                    'class' => 'bg-danger-soft text-danger outline outline-1 outline-danger/60',
                ],
            ],
            [
                'key' => 'paid',
                'label' => 'Faktur Terbayar Bulan Ini',
                'value' => 'Rp 185.300.000',
                'icon' => 'check-circle',
                'icon_tile' => 'bg-accent',
                'icon_class' => 'text-success-deep',
                'value_class' => 'text-success-deep',
                'foot_label' => '74.6% TOP Cleared',
                'foot_class' => 'text-success-deep',
                'chip' => [
                    'label' => 'Status: Lancar',
                    'class' => 'bg-accent text-success-deep outline outline-1 outline-success-deep',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function generator(): array
    {
        return [
            'title' => 'Generator Faktur Konsolidasi Surat Jalan',
            'subtitle' => 'Otoritas Sekretariat Operasional // Automated Clearing Draft',
            'badge_label' => 'MODUL:',
            'badge' => 'MULTI-SJ BUNDLING V2.1',
            'client_label' => 'Pilih Klien',
            'credit_label' => 'Limit Plafon Kredit:',
            'credit_sisa_label' => 'SISA:',
            'documents_title' => 'Surat Jalan Siap Difakturkan',
            'documents_badge' => '3 Dokumen Valid',
            'engine_note' => 'Terfilter Otomatis oleh GPA-Engine',
            'columns' => [
                'sj' => 'No. Surat Jalan',
                'date' => 'Tanggal Kirim',
                'commodity' => 'Komoditas & Bobot Netto Timbangan',
                'pod' => 'Verifikasi Penerima / POD',
                'value' => 'Nilai Tagihan Netto',
            ],
            'summary' => [
                'documents_label' => 'Total Dokumen Terpilih',
                'due_label' => 'Syarat & Jatuh Tempo Konsolidasi',
                'total_label' => 'Total Tagihan Faktur',
                'generate_label' => 'Generate & Terbitkan Dokumen Faktur Konsolidasi',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function clients(): array
    {
        return [
            [
                'id' => 'aerofood',
                'name' => 'PT Aerofood ACS (Garuda Group)',
                'terms_label' => 'TOP 45',
                'terms_days' => 45,
                'npwp' => '01.324.981.4-051.000',
                'contract' => 'GRP-B2B-AERO-09',
                'plafon' => 250000000,
                'used' => 164200000,
                'issue_date' => '2024-10-24',
                'documents' => [
                    [
                        'sj' => 'SJ-0102',
                        'date' => '22 Okt 2024',
                        'commodity' => 'Sayur Segar Grade-A',
                        'weight' => 840,
                        'weight_label' => '840 kg',
                        'pod' => 'Diterima Chef Hendra (Cap Sah)',
                        'value' => 21400000,
                        'value_label' => 'Rp 21.400.000',
                    ],
                    [
                        'sj' => 'SJ-0108',
                        'date' => '23 Okt 2024',
                        'commodity' => 'Bumbu Olahan & Unggas Segar',
                        'weight' => 620,
                        'weight_label' => '620 kg',
                        'pod' => 'Timbangan Sesuai Faktur (QC Approved)',
                        'value' => 48300000,
                        'value_label' => 'Rp 48.300.000',
                    ],
                    [
                        'sj' => 'SJ-0115',
                        'date' => '24 Okt 2024',
                        'commodity' => 'Buah Tropis Pilihan Export',
                        'weight' => 510,
                        'weight_label' => '510 kg',
                        'pod' => 'Cap Basah Gudang Ok (Sdr. Rian)',
                        'value' => 44500000,
                        'value_label' => 'Rp 44.500.000',
                    ],
                ],
            ],
            [
                'id' => 'ambarrukmo',
                'name' => 'Royal Ambarrukmo Hotel',
                'terms_label' => 'TOP 30',
                'terms_days' => 30,
                'npwp' => '01.234.567.3-527.000',
                'contract' => 'HTL-YOG-AMB-02',
                'plafon' => 120000000,
                'used' => 60450000,
                'issue_date' => '2024-10-24',
                'documents' => [
                    [
                        'sj' => 'SJ-0098',
                        'date' => '15 Sep 2024',
                        'commodity' => 'Sayur Hotel Fresh Pack',
                        'weight' => 460,
                        'weight_label' => '460 kg',
                        'pod' => 'Diterima Chef Yuni (Cap Sah)',
                        'value' => 27300000,
                        'value_label' => 'Rp 27.300.000',
                    ],
                    [
                        'sj' => 'SJ-0101',
                        'date' => '18 Sep 2024',
                        'commodity' => 'Protein Premium Daging',
                        'weight' => 320,
                        'weight_label' => '320 kg',
                        'pod' => 'Timbangan Sesuai Faktur (QC Approved)',
                        'value' => 32250000,
                        'value_label' => 'Rp 32.250.000',
                    ],
                ],
            ],
            [
                'id' => 'segar-makmur',
                'name' => 'PT Segar Makmur Ritel',
                'terms_label' => 'TOP 14',
                'terms_days' => 14,
                'npwp' => '02.998.114.7-012.000',
                'contract' => 'RTL-SGM-JKT-11',
                'plafon' => 60000000,
                'used' => 42000000,
                'issue_date' => '2024-10-24',
                'documents' => [
                    [
                        'sj' => 'SJ-0089',
                        'date' => '08 Sep 2024',
                        'commodity' => 'Sayur Ritel Kemasan Konsumen',
                        'weight' => 300,
                        'weight_label' => '300 kg',
                        'pod' => 'Cap Basah Gudang Ok (Sdr. Rian)',
                        'value' => 18000000,
                        'value_label' => 'Rp 18.000.000',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function ledger(): array
    {
        return [
            'title' => 'Ledger Faktur & Monitoring Penagihan Tempo (TOP)',
            'subtitle' => 'Real-time status tracking piutang berjalan klien B2B terikat Term of Payment.',
            'total_invoices' => 42,
            'status_filter' => 'Filter Status',
            'status_filters' => [
                'all' => 'Filter Status',
                'outstanding' => 'Piutang Berjalan',
                'paid' => 'Sudah Lunas',
            ],
            'columns' => [
                'invoice' => 'No. Faktur & Tanggal',
                'client' => 'Nama Klien Korporat B2B',
                'sj' => 'SJ Tersambung',
                'total' => 'Total Tagihan (Rp)',
                'terms' => 'Syarat Bayar',
                'due' => 'Jatuh Tempo',
                'remaining' => 'Sisa Hari / Status',
                'status' => 'Status Tagihan',
                'action' => 'Aksi Dokumen',
            ],
            'rows' => self::ledgerRows(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function ledgerRows(): array
    {
        return [
            [
                'id' => 'INV-2024-10-0089',
                'issued' => '24 Okt 2024',
                'issued_note' => '(Konsolidasi)',
                'client' => 'PT Aerofood ACS (Garuda Group)',
                'contract' => 'GRP-B2B-AERO-09',
                'contract_note' => null,
                'contract_class' => 'text-ink-quiet',
                'sj_count' => 3,
                'total' => 114200000,
                'total_label' => 'Rp 114.200.000',
                'total_class' => 'text-ink',
                'terms_days' => 45,
                'terms_label' => 'TOP 45',
                'terms_class' => 'bg-surface-pill text-ink-body outline outline-1 outline-line-board/70',
                'due' => '08 Des 2024',
                'due_class' => 'text-ink',
                'remaining' => '45 Hari Lagi',
                'remaining_class' => 'text-success-deep',
                'remaining_dot' => 'bg-success-deep',
                'status_key' => 'lancar',
                'status_label' => 'Lancar',
                'status_class' => 'bg-accent text-success-deep outline outline-1 outline-success-deep',
                'row_class' => '',
                'highlight' => '',
                'paid' => false,
            ],
            [
                'id' => 'INV-2024-09-0214',
                'issued' => '28 Sep 2024',
                'issued_note' => null,
                'client' => 'Royal Ambarrukmo Hotel',
                'contract' => 'HTL-YOG-AMB-02',
                'contract_note' => null,
                'contract_class' => 'text-ink-quiet',
                'sj_count' => 4,
                'total' => 59550000,
                'total_label' => 'Rp 59.550.000',
                'total_class' => 'text-warning-deep',
                'terms_days' => 30,
                'terms_label' => 'TOP 30',
                'terms_class' => 'bg-surface-pill text-ink-body outline outline-1 outline-line-board/70',
                'due' => '28 Okt 2024',
                'due_class' => 'text-warning-deep',
                'remaining' => 'H-3 Jatuh Tempo',
                'remaining_class' => 'text-warning-deep',
                'remaining_dot' => 'bg-warning',
                'status_key' => 'follow_up',
                'status_label' => 'Perlu Follow-up',
                'status_class' => 'bg-warning-soft/60 text-warning-deep outline outline-1 outline-warning/50',
                'row_class' => '',
                'highlight' => 'bg-warning-soft/50 text-warning-deep hover:bg-warning-soft',
                'paid' => false,
            ],
            [
                'id' => 'INV-2024-09-0150',
                'issued' => '12 Sep 2024',
                'issued_note' => null,
                'client' => 'PT Segar Makmur Ritel',
                'contract' => 'RTL-SGM-JKT-11',
                'contract_note' => '// Suspended',
                'contract_class' => 'text-danger/80',
                'sj_count' => 2,
                'total' => 18000000,
                'total_label' => 'Rp 18.000.000',
                'total_class' => 'text-danger',
                'terms_days' => 14,
                'terms_label' => 'TOP 14',
                'terms_class' => 'bg-danger-soft text-danger outline outline-1 outline-danger/60',
                'due' => '26 Sep 2024',
                'due_class' => 'text-danger',
                'remaining' => 'Overdue 12 Hari',
                'remaining_class' => 'text-danger',
                'remaining_dot' => 'bg-danger',
                'status_key' => 'blocked',
                'status_label' => 'Auto-Blocked',
                'status_class' => 'bg-danger-soft text-danger outline outline-1 outline-danger/60',
                'row_class' => 'bg-danger-soft/20',
                'highlight' => 'bg-danger-soft text-danger hover:bg-danger-soft/70',
                'paid' => false,
            ],
            [
                'id' => 'INV-2024-10-0044',
                'issued' => '15 Okt 2024',
                'issued_note' => null,
                'client' => 'Restoran Padang Nusantara',
                'contract' => 'RST-PDN-BDG-04',
                'contract_note' => null,
                'contract_class' => 'text-ink-quiet',
                'sj_count' => 1,
                'total' => 28750000,
                'total_label' => 'Rp 28.750.000',
                'total_class' => 'text-ink',
                'terms_days' => 14,
                'terms_label' => 'TOP 14',
                'terms_class' => 'bg-surface-pill text-ink-body outline outline-1 outline-line-board/70',
                'due' => '29 Okt 2024',
                'due_class' => 'text-ink',
                'remaining' => '4 Hari Lagi',
                'remaining_class' => 'text-success-deep',
                'remaining_dot' => 'bg-success-deep',
                'status_key' => 'lancar',
                'status_label' => 'Lancar',
                'status_class' => 'bg-accent text-success-deep outline outline-1 outline-success-deep',
                'row_class' => '',
                'highlight' => '',
                'paid' => false,
            ],
            [
                'id' => 'INV-2024-10-0012',
                'issued' => '05 Okt 2024',
                'issued_note' => null,
                'client' => 'RS Akademik Logistik (Instalasi Gizi)',
                'contract' => 'RSA-GZ-SLM-01',
                'contract_note' => null,
                'contract_class' => 'text-ink-quiet',
                'sj_count' => 2,
                'total' => 28000000,
                'total_label' => 'Rp 28.000.000',
                'total_class' => 'text-ink-quiet',
                'terms_days' => 14,
                'terms_label' => 'TOP 14',
                'terms_class' => 'bg-surface-pill text-ink-body outline outline-1 outline-line-board/70',
                'due' => '19 Okt 2024',
                'due_class' => 'text-ink-quiet',
                'remaining' => 'Terbayar 18 Okt',
                'remaining_class' => 'text-ink-quiet',
                'remaining_dot' => 'bg-ink-quiet',
                'status_key' => 'lunas',
                'status_label' => 'Lunas',
                'status_class' => 'bg-surface-pill text-ink-quiet outline outline-1 outline-line-board/50',
                'row_class' => '',
                'highlight' => '',
                'paid' => true,
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function printDocument(string $invoiceId): ?array
    {
        $invoice = collect(self::ledgerRows())->firstWhere('id', $invoiceId);

        if ($invoice === null) {
            return null;
        }

        $client = collect(self::clients())->firstWhere('contract', $invoice['contract']);
        $documents = $client['documents'] ?? [];

        return [
            'brand' => 'PT AGRO PASTI ADA',
            'company_lines' => [
                'AgroOrder GPA Logistics & Supply Network',
                'Kawasan Industri Sentul, Jl. Raya Babakan Madang No. 88, Kab. Bogor, Jawa Barat 16810',
                'Telp: (021) 8792-4421 | NPWP: 42.819.330.1-914.000',
            ],
            'invoice' => $invoice,
            'client' => [
                'name' => $invoice['client'],
                'contract' => $invoice['contract'],
                'npwp' => $client['npwp'] ?? 'Data NPWP mengikuti dokumen kontrak',
            ],
            'delivery_documents' => $documents,
            'items' => array_map(static fn (array $document): array => [
                'code' => $document['sj'],
                'name' => $document['commodity'],
                'qty' => $document['weight'],
                'price' => $document['weight'] > 0
                    ? $document['value'] / $document['weight']
                    : 0,
                'amount' => $document['value'],
            ], $documents),
            'totals' => [
                'qty' => array_sum(array_column($documents, 'weight')),
                'amount' => $invoice['total'],
                'document_count' => $invoice['sj_count'],
            ],
            'payment' => [
                'bank' => 'Bank Central Asia (BCA)',
                'branch' => 'KCU Sentul',
                'account' => '8820-192-001',
                'name' => 'PT AGRO PASTI ADA',
            ],
        ];
    }
}
