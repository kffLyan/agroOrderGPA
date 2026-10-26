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
        $settings = $this->governanceService->getSettings();
        $storedLogs = $this->governanceService->getAuditLogs();
        $paymentChannels = $this->governanceService->getPaymentChannels();

        return view('direktur.governance', compact(
            'settings',
            'storedLogs',
            'paymentChannels'
        ));
    }

    /**
     * Perbarui Parameter Kebijakan Bisnis
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

        return back()->with('success', 'Konfigurasi parameter dan kebijakan bisnis berhasil diperbarui.');
    }

    /**
     * Mengaktifkan / Menonaktifkan Master Freeze Switch
     */
    public function toggleMasterFreeze(Request $request)
    {
        $reason = $request->input('reason', 'Audit Kepatuhan & Stock Opname Berkala Direksi');
        $directorName = Auth::user()?->name ?: 'Direktur Utama';
        $settings = $this->governanceService->getSettings();
        $currentlyFrozen = $settings['master_freeze_active'] ?? false;
        $freeze = $request->has('freeze') ? $request->boolean('freeze') : !$currentlyFrozen;

        $isActive = $this->governanceService->toggleMasterFreeze($freeze, $reason, $directorName);

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
            'plt_active'         => 'sometimes|boolean',
            'plt_delegate_name'  => 'nullable|string|max:255',
            'plt_ceiling_amount' => 'nullable|numeric|min:10000000',
            'plt_duration_hours' => 'nullable|integer|min:1|max:720',
            'mandate_number'     => 'nullable|string|max:255',
            'delegate_id'        => 'nullable|integer',
            'scope'              => 'nullable|string',
            'valid_until'        => 'nullable|date',
            'rationale_notes'    => 'nullable|string',
        ]);

        $pltActive = $request->has('plt_active')
            ? $request->boolean('plt_active')
            : ($request->filled('delegate_id') || $request->filled('plt_delegate_name'));

        $delegateName = $validated['plt_delegate_name'] ?? null;
        if (! $delegateName && ! empty($validated['delegate_id'])) {
            $delegateUser = \App\Models\User::find($validated['delegate_id']);
            $delegateName = $delegateUser ? "{$delegateUser->name} ({$delegateUser->role})" : null;
        }

        $ceiling = isset($validated['plt_ceiling_amount']) ? (float) $validated['plt_ceiling_amount'] : null;
        $durationHours = isset($validated['plt_duration_hours']) ? (int) $validated['plt_duration_hours'] : null;
        $directorName = Auth::user()?->name ?: 'Direktur Utama';

        $this->governanceService->configurePlt($pltActive, $delegateName, $ceiling, $durationHours, $directorName);
        $statusStr = $pltActive ? 'DIAKTIFKAN' : 'DINONAKTIFKAN';
        $this->governanceService->addAuditLog(
            'PLT_SUCCESSION_CONFIG',
            'SEC-PLT-MANDATE',
            "Mandat Plt Direktur {$statusStr}. Penerima: " . ($delegateName ?: '-') . ", Plafon: Rp " . number_format($ceiling ?? 0, 0, ',', '.'),
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
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['LOG AUDIT FORENSIK APPEND-ONLY SHA-256 - KOPERASI GPA']);
            fputcsv($handle, ['TANGGAL CETAK', date('d-m-Y H:i:s') . ' WIB']);
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

    /**
     * Memperbarui Kanal Pembayaran Resmi GPA (Bank & QRIS) oleh Direktur
     */
    public function updatePaymentChannels(Request $request)
    {
        $validated = $request->validate([
            'channels'                  => ['required', 'array', 'min:1'],
            'channels.*.id'             => ['required', 'string'],
            'channels.*.type'           => ['required', 'string', 'in:BANK,QRIS,EWALLET'],
            'channels.*.name'           => ['required', 'string', 'max:100'],
            'channels.*.bank_code'      => ['nullable', 'string', 'max:50'],
            'channels.*.account_number' => ['required', 'string', 'max:100'],
            'channels.*.account_name'   => ['required', 'string', 'max:150'],
            'channels.*.description'    => ['nullable', 'string', 'max:255'],
            'channels.*.instructions'   => ['nullable', 'string', 'max:500'],
            'channels.*.image_url'      => ['nullable', 'string', 'max:500'],
            'channels.*.logo_url'       => ['nullable', 'string', 'max:500'],
            'channels.*.image'          => ['nullable', 'file', 'image', 'max:10240'],
            'channels.*.is_active'      => ['nullable'],
        ]);

        $channels = [];
        foreach ($validated['channels'] as $idx => $ch) {
            $imageUrl = $ch['image_url'] ?? null;

            // Jika Direktur mengunggah gambar/foto baru untuk kanal ini (misal QRIS barcode foto atau logo)
            if ($request->hasFile("channels.{$idx}.image")) {
                $file = $request->file("channels.{$idx}.image");
                if ($file->isValid()) {
                    $ext = $file->getClientOriginalExtension() ?: 'png';
                    $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '_', $ch['id']);
                    $filename = "channel_{$safeId}_" . time() . "_{$idx}.{$ext}";
                    $path = $file->storeAs('payment-channels', $filename, 'public');
                    $imageUrl = '/storage/' . $path;
                }
            }

            // Fallback default image untuk QRIS jika belum ada foto khusus
            if ($ch['type'] === 'QRIS' && empty($imageUrl)) {
                $imageUrl = '/storage/payment-channels/qris-gpa-default.png';
            }

            $logoUrl = $ch['logo_url'] ?? null;
            if (empty($logoUrl)) {
                $candidate = '/images/payments/' . strtolower($ch['id']) . '.svg';
                if (file_exists(public_path(ltrim($candidate, '/')))) {
                    $logoUrl = $candidate;
                }
            }

            $channels[] = [
                'id'             => $ch['id'],
                'type'           => $ch['type'],
                'name'           => $ch['name'],
                'bank_code'      => $ch['bank_code'] ?? strtoupper($ch['id']),
                'account_number' => $ch['account_number'],
                'account_name'   => $ch['account_name'],
                'description'    => $ch['description'] ?? '',
                'instructions'   => $ch['instructions'] ?? '',
                'image_url'      => $imageUrl,
                'logo_url'       => $logoUrl,
                'is_active'      => !empty($ch['is_active']),
            ];
        }

        $this->governanceService->updatePaymentChannels($channels);

        $directorName = Auth::user()?->name ?: 'Direktur Utama';
        $activeCount = count(array_filter($channels, fn($c) => $c['is_active']));
        $this->governanceService->addAuditLog(
            'PAYMENT_CHANNELS_UPDATE',
            'CFG-PAYMENT-GATEWAY',
            "Direktur memperbarui konfigurasi kanal pembayaran resmi ({$activeCount} kanal aktif: Bank, QRIS, & Dompet Digital GPA).",
            $directorName,
            'DIR-01 [ROOT-ACCESS]',
            'SEALED'
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi kanal pembayaran resmi berhasil disimpan.',
                'channels' => $channels,
            ]);
        }

        return back()->with('success', 'Konfigurasi kanal pembayaran GPA (Bank, QRIS, & Dompet Digital) berhasil disimpan oleh Direktur.');
    }
}
