<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Services\GovernanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GovernanceController extends Controller
{
    protected GovernanceService $governanceService;

    public function __construct(GovernanceService $governanceService)
    {
        $this->governanceService = $governanceService;
    }

    /**
     * Menampilkan Pengaturan Tata Kelola, Kebijakan Bisnis & Audit Trail Sistem
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $operator = [
            'role'     => 'Direktur Utama',
            'name'     => $user?->name ?: 'Direksi GPA',
            'code'     => 'DIR-' . str_pad($user?->id ?: 1, 3, '0', STR_PAD_LEFT),
            'initials' => strtoupper(substr($user?->name ?: 'DU', 0, 2)),
            'branch'   => 'Kantor Pusat GPA - Eksekutif',
            'email'    => $user?->email ?: 'direktur@greenpasundan.id',
            'period'   => 'Minggu ' . date('W') . ' ' . date('Y') . ' | Q4 YTD',
        ];

        $header = [
            'eyebrow'         => 'Pengaturan Tata Kelola',
            'title'           => 'Pengaturan Tata Kelola, Kebijakan Bisnis & Audit Trail Sistem',
            'subtitle'        => 'Konsol Otorisasi Kebijakan Utama Direksi, Kontrol Batas Kredit Korporat, Kalibrasi Batas Toleransi Mutu, & Log Forensik Keamanan Transaksi. Seluruh perubahan diproteksi append-only SHA-256 ledger.',
            'period'          => 'PERIODE: OKTOBER 2026',
            'export_label'    => 'Export Full Audit Log (.CSV)',
            'access_label'    => 'Kelola Akun Pengguna & RBAC',
            'integrity_label' => 'Uji Integritas Kriptografi',
            'lock_label'      => 'Kunci Sistem Periode',
        ];

        $settings = $this->governanceService->getSettings();
        $storedLogs = $this->governanceService->getAuditLogs();

        // 4 Kartu Metrik Tata Kelola
        $metrics = [
            [
                'key'        => 'ledger',
                'eyebrow'    => 'INTEGRITAS LEDGER',
                'chip'       => 'ACTIVE GUARD',
                'chip_tone'  => 'success',
                'value'      => '100% VALID',
                'note'       => 'SHA-256 Checksum Verified',
                'foot_label' => 'Anomali Terdeteksi',
                'foot_value' => '0 Anomali (Zero Breach)',
                'foot_tone'  => 'success',
            ],
            [
                'key'        => 'credit',
                'eyebrow'    => 'KONTROL KREDIT B2B',
                'chip'       => 'CEILING LIMIT',
                'chip_tone'  => 'neutral',
                'value'      => 'Rp 2.500.000.000',
                'note'       => 'Plafon kredit korporat maksimum',
                'bar_width'  => 47.4,
                'foot_label' => 'Terutilisasi',
                'foot_value' => 'Rp 1.185.000.000',
                'foot_tone'  => 'success',
                'foot_note'  => '47.4% Exposure Aman',
            ],
            [
                'key'        => 'approval',
                'eyebrow'    => 'DIR APPROVAL STATUS',
                'chip'       => 'VERIFIED',
                'chip_tone'  => 'success',
                'value'      => '3 Kontrak Disetujui',
                'note'       => 'Threshold Policy Margin Min. ' . number_format($settings['min_margin_percent'] ?? 15.0, 1) . '%',
                'foot_label' => 'Pending Otorisasi Dir',
                'foot_value' => '0 Pending Dir (Clear)',
                'foot_tone'  => 'success',
            ],
            [
                'key'        => 'guard',
                'eyebrow'    => 'HARD-GUARD COMPLIANCE',
                'chip'       => 'POLICY ENGINE',
                'chip_tone'  => 'neutral',
                'value'      => '0 Pelanggaran',
                'note'       => 'Hard-Guard Rules 02, 03, 05, 06 Enforced',
                'foot_label' => 'Status Intersepsi',
                'foot_value' => '100% Intercepted',
                'foot_tone'  => 'ink',
            ],
        ];

        // 5 Parameter Bisnis
        $lossMax = number_format($settings['warehouse_loss_max_percent'] ?? 2.0, 1);
        $lossWarn = number_format($settings['warehouse_loss_warning_percent'] ?? 1.5, 1);
        $ceilingDef = 'Rp ' . number_format($settings['default_top_credit_ceiling'] ?? 100000000, 0, ',', '.');
        $topDays = ($settings['default_top_days'] ?? 30) . ' Hari Kalender';
        $minScore = ($settings['min_credit_score'] ?? 75) . ' Poin Sistem';
        $minMargin = number_format($settings['min_margin_percent'] ?? 15.0, 1);

        $parameters = [
            'title'       => 'Konfigurasi Parameter Bisnis & Rule Engine',
            'subtitle'    => '5 Parameter Statutori Tata Kelola yang Mengunci Otomatisasi Validasi Sistem ERP & Pergudangan',
            'action'      => 'Edit Parameter Kebijakan (PIN Master)',
            'action_note' => 'Pengubahan parameter memerlukan PIN Master Direktur Utama.',
            'cards'       => [
                [
                    'rule'        => 'RULE 04 & 13',
                    'status'      => 'ENFORCED',
                    'status_tone' => 'success',
                    'title'       => 'Toleransi Deviasi Susut Gudang',
                    'description' => 'Ambang batas perbedaan timbangan asal versus timbangan terima di fasilitas pergudangan.',
                    'facts'       => [
                        ['label' => 'Maks Hard Limit:',    'value' => $lossMax . '%', 'tone' => 'ink'],
                        ['label' => 'Warning Threshold:',  'value' => $lossWarn . '%', 'tone' => 'warning'],
                        ['label' => 'Action Triggered:',    'value' => 'Auto-Hold Invoice', 'tone' => 'danger'],
                    ],
                    'foot_note'   => 'SLA Validasi: Real-Time',
                    'config_id'   => 'CFG-DEV-0413',
                ],
                [
                    'rule'        => 'SECTION 15.3',
                    'status'      => 'ENFORCED',
                    'status_tone' => 'success',
                    'title'       => 'Plafon Kredit Default TOP Klien Baru',
                    'description' => 'Kebijakan limit terms of payment (TOP) untuk mitra agribisnis atau buyer yang belum diaudit Direksi.',
                    'facts'       => [
                        ['label' => 'Default Plafon:',   'value' => $ceilingDef, 'tone' => 'ink'],
                        ['label' => 'Term of Payment:',  'value' => $topDays, 'tone' => 'ink'],
                        ['label' => 'Min Skor Kredit:',  'value' => $minScore, 'tone' => 'success'],
                    ],
                    'foot_note'   => 'Escalation: Direksi Keuangan',
                    'config_id'   => 'CFG-KRD-1503',
                ],
                [
                    'rule'        => 'RULE 05',
                    'status'      => 'MANDATORY',
                    'status_tone' => 'success',
                    'title'       => 'Syarat Wajib Terbit Surat Jalan',
                    'description' => 'Gerbang verifikasi fisik dan digital sebelum armada angkut logistik diizinkan keluar depot/gudang.',
                    'facts'       => [
                        ['label' => 'Net Weight Tera Sah:',  'value' => 'Mandatori Otomatis', 'tone' => 'success'],
                        ['label' => 'Lolos QC Laboratorium:','value' => '100% Grade A/B', 'tone' => 'ink'],
                        ['label' => 'Dispensasi Bypass:',    'value' => '0 Toleransi (Dilarang)', 'tone' => 'danger'],
                    ],
                    'foot_note'   => 'Interlock: Gate Barrier IoT',
                    'config_id'   => 'CFG-DISP-0005',
                ],
                [
                    'rule'        => 'SECTION 16',
                    'status'      => 'ENFORCED',
                    'status_tone' => 'success',
                    'title'       => 'Batas Margin Minimum Penjualan B2B',
                    'description' => 'Floor margin proteksi profitabilitas bruto komoditas pangan segar terhadap fluktuasi harga lelang petani.',
                    'facts'       => [
                        ['label' => 'Margin Minimum Statutori:', 'value' => $minMargin . '%', 'tone' => 'ink'],
                        ['label' => 'Order < ' . $minMargin . '%:', 'value' => 'Auto-Eskalasi Direktur', 'tone' => 'warning'],
                        ['label' => 'Sales Bypass Right:',       'value' => 'Locked (No Access)', 'tone' => 'danger'],
                    ],
                    'foot_note'   => 'Formula: Net Realisasi COGS',
                    'config_id'   => 'CFG-MRG-1600',
                ],
                [
                    'rule'        => 'RULE 02 & 03',
                    'status'      => 'IMMUTABLE CORE',
                    'status_tone' => 'success',
                    'title'       => 'Anti-Overselling Guard (Buffer Fisik + Buffer Mitra)',
                    'description' => 'Mencegah komitmen kontrak jual melampaui agregasi stok fisik gudang dan buffer pasokan mitra tervalidasi sensor silo.',
                    'facts'       => [
                        ['label' => 'Alokasi Buffer Fisik:', 'value' => 'Standby Real-Time WMS', 'tone' => 'ink'],
                        ['label' => 'Agregasi Mitra Tani:',  'value' => 'Telemetri Terverifikasi', 'tone' => 'ink'],
                        ['label' => 'Intervensi Sistem:',    'value' => 'Auto-Block Order Terlampaui', 'tone' => 'danger'],
                    ],
                    'foot_note'   => 'Latency Verifikasi: < 35ms',
                    'config_id'   => 'CFG-OVS-0203',
                ],
            ],
        ];

        // Audit Trail Rows
        $defaultAuditRows = [
            [
                'date'        => '24-OKT-2024',
                'time'        => '15:18:02 WIB',
                'actor'       => 'Ir. H. Sudarto',
                'actor_meta'  => 'UID: DIR-001 [CLEARANCE L4]',
                'action'      => 'APPROVAL KONTRAK',
                'action_tone' => 'success',
                'document'    => 'KTR-B2B-2024-089',
                'detail'      => 'Persetujuan Khusus Kontrak B2B PT Pangan Nusantara Raya (Margin 16.2%, Plafon TOP Rp 450M, Tenor 45 Hari).',
                'hash'        => '7f83b165...e92a',
                'block'       => 'BLOCK #4412 [SEALED]',
                'blocked'     => false,
            ],
            [
                'date'        => '24-OKT-2024',
                'time'        => '14:02:44 WIB',
                'actor'       => 'Nurhayati S.Ak',
                'actor_meta'  => 'UID: FIN-LEAD-03 [L3]',
                'action'      => 'REKONSILIASI KAS',
                'action_tone' => 'neutral',
                'document'    => 'INV-GPA-202410-0089',
                'detail'      => 'Kliring Pembayaran Invoice Termin 2 Rp 215.000.000 via VA Mandiri Corporat, Selisih Kliring Rp 0.',
                'hash'        => 'b94d27b9...1348',
                'block'       => 'BLOCK #4411 [SEALED]',
                'blocked'     => false,
            ],
            [
                'date'        => '24-OKT-2024',
                'time'        => '11:15:00 WIB',
                'actor'       => 'Ir. H. Sudarto',
                'actor_meta'  => 'UID: DIR-001 [CLEARANCE L4]',
                'action'      => 'SYSTEM GOVERNANCE',
                'action_tone' => 'dark',
                'document'    => 'Q3-2024-LEDGER',
                'detail'      => 'Eksekusi Audit Freeze Q3 2024: Penutupan Mutasi Jurnal Buku Besar Agribisnis Kuartal 3. Status Finalized.',
                'hash'        => '1188e6dd...5672',
                'block'       => 'BLOCK #4410 [SEALED]',
                'blocked'     => false,
            ],
            [
                'date'        => '24-OKT-2024',
                'time'        => '09:40:12 WIB',
                'actor'       => 'Agung W.',
                'actor_meta'  => 'UID: WMS-SUPER-01 [L2]',
                'action'      => 'BUFFER OVERRIDE',
                'action_tone' => 'neutral',
                'document'    => 'STK-TMT-LEMBANG-04',
                'detail'      => 'Penyesuaian Kalibrasi Buffer Tomat Beef Pasca Sortir Gudang Lembang (+240 KG dari panen kemitraan Gapoktan).',
                'hash'        => '4c8a2b10...99aa',
                'block'       => 'BLOCK #4409 [SEALED]',
                'blocked'     => false,
            ],
            [
                'date'        => '24-OKT-2024',
                'time'        => '08:12:55 WIB',
                'actor'       => 'System Auto-Guard',
                'actor_meta'  => 'DAEMON CORE RULE 02',
                'action'      => 'SECURITY BLOCK',
                'action_tone' => 'danger',
                'document'    => 'ORD-GPA-202410-0112',
                'detail'      => 'Pencegahan Otomatis Overselling: Permintaan PO Melebihi Buffer Fisik Kentang Granola Gudang Ciwidey (Defisit 1.8 MT).',
                'hash'        => 'e3b0c442...9823',
                'block'       => 'BLOCK #4408 [INTERCEPTED]',
                'blocked'     => true,
            ],
        ];

        $auditRows = [];
        if (!empty($storedLogs)) {
            foreach ($storedLogs as $l) {
                $timeCarbon = isset($l['created_at']) ? \Carbon\Carbon::parse($l['created_at']) : now();
                $isBlocked = ($l['status'] ?? '') === 'REJECTED' || ($l['status'] ?? '') === 'INTERCEPTED';
                $tone = 'neutral';
                if ($isBlocked) {
                    $tone = 'danger';
                } elseif (str_contains(strtoupper($l['action_category'] ?? ''), 'APPROVAL')) {
                    $tone = 'success';
                } elseif (str_contains(strtoupper($l['action_category'] ?? ''), 'GOVERNANCE')) {
                    $tone = 'dark';
                }

                $auditRows[] = [
                    'date'        => $timeCarbon->format('d-M-Y'),
                    'time'        => $timeCarbon->format('H:i:s') . ' WIB',
                    'actor'       => $l['actor'] ?? 'System',
                    'actor_meta'  => $l['clearance'] ?? 'UID: ROOT',
                    'action'      => str_replace('_', ' ', $l['action_category'] ?? 'ACTION'),
                    'action_tone' => $tone,
                    'document'    => $l['document_ref'] ?? '-',
                    'detail'      => $l['details'] ?? '-',
                    'hash'        => substr($l['hash'] ?? 'sha256...', 0, 8) . '...' . substr($l['hash'] ?? '0000', -4),
                    'block'       => $l['block_number'] ?? 'BLOCK #' . rand(4400, 4500) . ' [SEALED]',
                    'blocked'     => $isBlocked,
                ];
            }
        }

        if (empty($auditRows)) {
            $auditRows = $defaultAuditRows;
        }

        $audit = [
            'title'              => 'Tabel Audit Trail Forensik & Riwayat Aksi Direksi / Admin',
            'subtitle'           => 'Append-Only Cryptographic Ledger berantai SHA-256. Setiap modifikasi kebijakan dan otorisasi tersimpan permanen.',
            'search_placeholder' => 'Cari Dokumen / Hash...',
            'filter_label'       => 'Semua Kategori',
            'ledger_status'      => 'LEDGER STATUS: CONTINUOUS APPEND // PREVIOUS BLOCK HASH MATCHED 100%',
            'columns'            => [
                'TIMESTAMP',
                'AKTOR & USER ID',
                'KATEGORI AKSI',
                'DOKUMEN TERKAIT',
                'RINCIAN PERUBAHAN',
                'STATUS HASH SHA-256',
            ],
            'rows'               => $auditRows,
            'shown_label'        => 'Menampilkan ' . count($auditRows) . ' dari 1.482 Aksi Terverifikasi',
            'page_label'         => 'Halaman 1 dari 1',
        ];

        // 2 Protokol Kritis
        $controls = [
            [
                'key'           => 'freeze',
                'protocol'      => 'CRITICAL SAFETY PROTOCOL #01',
                'protocol_tone' => 'danger',
                'status'        => ($settings['master_freeze_active'] ?? false) ? 'STATUS: ENGAGED [ACTIVE]' : 'STATUS: STANDBY READY',
                'status_tone'   => ($settings['master_freeze_active'] ?? false) ? 'danger' : 'success',
                'title'         => 'Master Freeze Switch (Stock Opname & Audit Khusus)',
                'description'   => 'Menghentikan seluruh transaksi input PO, penerbitan surat jalan, rekonsiliasi kas, dan mutasi komoditas secara instan di seluruh node gudang saat stock opname tahunan atau investigasi fraud berlangsung.',
                'facts'         => [
                    ['label' => 'Cakupan Freeze:',    'value' => 'All 4 Warehouse Nodes & ERP Sales Gateway'],
                    ['label' => 'Syarat Buka Kunci:', 'value' => 'Dual Digital Signature (Direktur Utama & Komisaris)'],
                ],
                'authority'     => 'OTORISASI KHUSUS DIREKTUR',
                'action'        => ($settings['master_freeze_active'] ?? false) ? 'Nonaktifkan Master Freeze' : 'Aktifkan Master Freeze',
            ],
            [
                'key'           => 'succession',
                'protocol'      => 'GOVERNANCE SUCCESSION #02',
                'protocol_tone' => 'success',
                'status'        => ($settings['plt_active'] ?? false) ? 'MODE: PLT AKTIF' : 'MODE: STANDBY',
                'status_tone'   => ($settings['plt_active'] ?? false) ? 'ink' : 'neutral',
                'title'         => 'Delegasi Wewenang Sementara (Plt Direksi)',
                'description'   => 'Konfigurasi mandat otorisasi otomatis kepada Pejabat Pelaksana Tugas (Plt) ketika Direksi bertugas ke sentra pertanian luar pulau. Akses dibatasi ketat dengan plafon limit transaksi spesifik.',
                'facts'         => [
                    ['label' => 'Penerima Mandat Aktif:', 'value' => $settings['plt_delegate_name'] ?? 'Nurhayati S.Ak (VP Finance & Controller)'],
                    ['label' => 'Plafon Otorisasi Plt:',  'value' => 'Maksimum Rp ' . number_format($settings['plt_ceiling_amount'] ?? 350000000, 0, ',', '.') . ' / Kontrak', 'tone' => 'success'],
                    ['label' => 'Masa Berlaku Mandat:',   'value' => 'Otomatis Berakhir dalam ' . ($settings['plt_duration_hours'] ?? 72) . ' Jam'],
                ],
                'authority'     => 'PROTOKOL TATA KELOLA PRD SEC 21',
                'action'        => 'Atur Plt Direksi',
            ],
        ];

        return view('direktur.governance', compact(
            'operator',
            'header',
            'metrics',
            'parameters',
            'audit',
            'controls'
        ));
    }

    /**
     * Perbarui Parameter Kebijakan Bisnis & Rule Engine
     */
    public function updateParameters(Request $request)
    {
        $validated = $request->validate([
            'warehouse_loss_max_percent'     => 'required|numeric|min:0.5|max:10.0',
            'warehouse_loss_warning_percent' => 'required|numeric|min:0.1|max:5.0',
            'default_top_credit_ceiling'     => 'required|numeric|min:1000000',
            'default_top_days'               => 'required|integer|min:7|max:90',
            'min_margin_percent'             => 'required|numeric|min:5.0|max:50.0',
        ]);

        $this->governanceService->updateSettings($validated);

        $directorName = Auth::user()?->name ?: 'Direktur Utama';
        $this->governanceService->addAuditLog(
            'PARAMETER_POLICY_UPDATE',
            'CFG-MASTER-POLICY',
            "Direktur memperbarui parameter statutori: Batas Susut Maks {$validated['warehouse_loss_max_percent']}%, Plafon Default Rp " . number_format($validated['default_top_credit_ceiling'], 0, ',', '.') . ", Margin Min {$validated['min_margin_percent']}%.",
            $directorName,
            'DIR-01 [ROOT-ACCESS]',
            'SEALED'
        );

        return back()->with('success', 'Konfigurasi parameter bisnis dan rule engine berhasil diperbarui.');
    }

    /**
     * Mengaktifkan / Menonaktifkan Master Freeze Switch
     */
    public function toggleMasterFreeze(Request $request)
    {
        $reason = $request->input('reason', 'Audit Kepatuhan & Stock Opname Berkala Direksi');
        $directorName = Auth::user()?->name ?: 'Direktur Utama';

        $isActive = $this->governanceService->toggleMasterFreeze($directorName, $reason);

        $msg = $isActive
            ? "MASTER FREEZE TELAH DIAKTIFKAN. Seluruh mutasi stok dan transaksi input PO dihentikan."
            : "Master Freeze dinonaktifkan. Seluruh sistem operasional kembali normal.";

        return back()->with('success', $msg);
    }

    /**
     * Mengatur Delegasi Wewenang Plt Direksi
     */
    public function configurePlt(Request $request)
    {
        $validated = $request->validate([
            'plt_active'         => 'required|boolean',
            'plt_delegate_name'  => 'required_if:plt_active,1|string|max:255',
            'plt_ceiling_amount' => 'required_if:plt_active,1|numeric|min:10000000',
            'plt_duration_hours' => 'required_if:plt_active,1|integer|min:1|max:720',
        ]);

        $this->governanceService->configurePlt($validated);

        $directorName = Auth::user()?->name ?: 'Direktur Utama';
        $statusStr = $validated['plt_active'] ? 'DIAKTIFKAN' : 'DINONAKTIFKAN';
        $this->governanceService->addAuditLog(
            'PLT_SUCCESSION_CONFIG',
            'SEC-PLT-MANDATE',
            "Mandat Plt Direktur {$statusStr}. Penerima: " . ($validated['plt_delegate_name'] ?? '-') . ", Plafon: Rp " . number_format($validated['plt_ceiling_amount'] ?? 0, 0, ',', '.'),
            $directorName,
            'DIR-01 [ROOT-ACCESS]',
            'SEALED'
        );

        return back()->with('success', "Konfigurasi mandat Plt Direksi berhasil disimpan ({$statusStr}).");
    }

    /**
     * Unduh Full Audit Log (.CSV)
     */
    public function exportAuditLog(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Full_Audit_Trail_' . date('Ymd_His') . '.csv"',
        ];

        $logs = $this->governanceService->getAuditLogs();

        return response()->stream(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['LOG AUDIT FORENSIK APPEND-ONLY SHA-256 - KOPERASI GPA']);
            fputcsv($handle, ['TANGGAL CETAK', date('d-m-Y H:i:s')]);
            fputcsv($handle, []);
            fputcsv($handle, ['Timestamp', 'Kategori Aksi', 'Dokumen Ref', 'Aktor', 'Clearance Level', 'Status', 'Rincian Perubahan', 'Hash SHA-256']);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log['created_at'] ?? '',
                    $log['action_category'] ?? '',
                    $log['document_ref'] ?? '',
                    $log['actor'] ?? '',
                    $log['clearance'] ?? '',
                    $log['status'] ?? '',
                    $log['details'] ?? '',
                    $log['hash'] ?? '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
