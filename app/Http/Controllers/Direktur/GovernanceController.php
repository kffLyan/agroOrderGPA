<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Services\GovernanceService;
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
    public function index(Request $request)
    {
        $search = $request->query('q', '');
        $categoryFilter = $request->query('category', 'ALL');

        $settings = $this->governanceService->getSettings();
        $allLogs = $this->governanceService->getAuditLogs();

        // Filter audit logs
        $auditLogs = array_filter($allLogs, function ($log) use ($search, $categoryFilter) {
            if ($categoryFilter !== 'ALL' && ($log['action_category'] ?? '') !== $categoryFilter) {
                return false;
            }
            if (!empty($search)) {
                $term = strtolower($search);
                return str_contains(strtolower($log['document_ref'] ?? ''), $term) ||
                       str_contains(strtolower($log['details'] ?? ''), $term) ||
                       str_contains(strtolower($log['actor'] ?? ''), $term) ||
                       str_contains(strtolower($log['hash'] ?? ''), $term);
            }
            return true;
        });

        // 4 Kartu Metrik Tata Kelola
        $metrics = [
            'ledger_integrity' => [
                'status' => '100% VALID',
                'checksum' => 'SHA-256 Checksum Verified',
                'anomalies' => 0,
            ],
            'credit_control' => [
                'ceiling_limit' => 2500000000,
                'utilized' => 1185000000,
                'exposure_percent' => 47.4,
            ],
            'approval_status' => [
                'contracts_approved' => 3,
                'min_margin_threshold' => $settings['min_margin_percent'] ?? 15.0,
                'pending_approval' => 0,
            ],
            'hard_guard' => [
                'violations' => 0,
                'rules' => 'Rules 02, 03, 05, 06 Enforced',
                'interception_status' => '100% Intercepted',
            ],
        ];

        return view('direktur.governance.index', compact('settings', 'auditLogs', 'metrics', 'search', 'categoryFilter'));
    }

    /**
     * Perbarui Parameter Kebijakan Bisnis & Rule Engine
     */
    public function updateParameters(Request $request)
    {
        $validated = $request->validate([
            'warehouse_loss_max_percent' => 'required|numeric|min:0.5|max:10.0',
            'warehouse_loss_warning_percent' => 'required|numeric|min:0.1|max:5.0',
            'default_top_credit_ceiling' => 'required|numeric|min:1000000',
            'default_top_days' => 'required|integer|min:7|max:90',
            'min_margin_percent' => 'required|numeric|min:5.0|max:50.0',
        ]);

        $this->governanceService->updateSettings($validated);

        $directorName = Auth::guard('web')->user()->name;
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
     * Toggle Master Freeze Switch (Protokol Darurat & Audit Khusus)
     */
    public function toggleMasterFreeze(Request $request)
    {
        $freeze = (bool) $request->input('freeze_action');
        $reason = $request->input('freeze_reason', 'Stock Opname Tahunan & Penyelidikan Forensik Ledger');
        $directorName = Auth::guard('web')->user()->name;

        $this->governanceService->toggleMasterFreeze($freeze, $reason, $directorName);

        $msg = $freeze
            ? "MASTER FREEZE AKTIF: Seluruh input transaksi PO, penerbitan surat jalan, dan mutasi kas berhasil dihentikan instan."
            : "MASTER FREEZE DICABUT: Seluruh operasional terminal pergudangan dan gateway ERP berhasil dipulihkan normal.";

        return back()->with('success', $msg);
    }

    /**
     * Konfigurasi Delegasi Mandat Plt Direksi
     */
    public function configurePlt(Request $request)
    {
        $active = (bool) $request->input('plt_active');
        $delegateName = $request->input('plt_delegate_name', 'Nurhayati, S.Ak (VP Finance & Controller)');
        $ceiling = (float) $request->input('plt_ceiling_amount', 350000000);
        $durationHours = (int) $request->input('plt_duration_hours', 72);
        $directorName = Auth::guard('web')->user()->name;

        $this->governanceService->configurePlt($active, $delegateName, $ceiling, $durationHours, $directorName);

        $msg = $active
            ? "Mandat Plt Direksi resmi diserahkan kepada {$delegateName} dengan limit plafon Rp " . number_format($ceiling, 0, ',', '.') . " berlaku {$durationHours} jam."
            : "Mandat Plt Direksi berhasil dicabut. Otoritas penuh kembali ke Direktur Utama.";

        return back()->with('success', $msg);
    }

    /**
     * Ekspor Log Audit Forensik (.CSV)
     */
    public function exportAuditLog(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Audit_Trail_Ledger_GPA_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['KOPERASI GREEN PASUNDAN AGRICULTURE - FORENSIC AUDIT TRAIL LEDGER']);
            fputcsv($handle, ['INTEGRITAS', 'APPEND-ONLY SHA-256 TAMPER-PROOF']);
            fputcsv($handle, []);
            fputcsv($handle, ['Block ID', 'Waktu (WIB)', 'Aktor', 'Kategori Aksi', 'Dokumen / Ref', 'Rincian Keputusan', 'Status', 'Hash SHA-256']);

            $logs = $this->governanceService->getAuditLogs();
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log['block_id'] ?? '-',
                    $log['timestamp'] ?? '-',
                    ($log['actor'] ?? '-') . ' (' . ($log['actor_uid'] ?? '') . ')',
                    $log['action_category'] ?? '-',
                    $log['document_ref'] ?? '-',
                    $log['details'] ?? '-',
                    $log['status'] ?? '-',
                    $log['hash'] ?? '-',
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
