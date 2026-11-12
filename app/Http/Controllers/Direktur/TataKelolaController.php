<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\User;
use App\Services\TataKelolaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Controller tata kelola, kebijakan bisnis, dan audit trail untuk Direktur Utama
class TataKelolaController extends Controller
{
    public function __construct(
        protected TataKelolaService $tataKelolaService
    ) {}

    // Menampilkan konsol tata kelola, log audit append-only SHA-256, dan saluran pembayaran resmi
    public function index(Request $request): View
    {
        $settings = $this->tataKelolaService->getSettings();
        $storedLogs = $this->tataKelolaService->getAuditLogs();
        $paymentChannels = $this->tataKelolaService->getPaymentChannels();

        // Hitung batas eksposur plafon kredit korporat dan total piutang berjalan mitra B2B
        $totalContractsPlafon = (float) Contract::where('status', 'ACTIVE')
            ->sum(DB::raw('fixed_price_per_kg * committed_volume_per_cycle * 2'));
        $defaultCeiling = (float) ($settings['default_top_credit_ceiling'] ?? 100000000);
        $clientCount = max(1, User::where('role', 'KLIEN')->count());
        $totalCeilingLimit = $totalContractsPlafon > 0 ? $totalContractsPlafon : ($defaultCeiling * $clientCount);

        $totalUtilizedCredit = (float) Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])->sum('grand_total');
        $exposurePercent = $totalCeilingLimit > 0 ? round(($totalUtilizedCredit / $totalCeilingLimit) * 100, 1) : 0;

        return view('direktur.tata-kelola', compact(
            'settings',
            'storedLogs',
            'paymentChannels',
            'totalCeilingLimit',
            'totalUtilizedCredit',
            'exposurePercent'
        ));
    }

    // Memperbarui parameter kebijakan operasional (toleransi susut gudang, plafon kredit TOP, tarif PPN, dan ongkos kirim)
    public function updateParameters(Request $request)
    {
        $validated = $request->validate([
            'warehouse_loss_max_percent'     => 'sometimes|numeric|min:0.5|max:10.0',
            'warehouse_loss_warning_percent' => 'sometimes|numeric|min:0.1|max:5.0',
            'default_top_credit_ceiling'     => 'sometimes|numeric|min:1000000',
            'default_top_days'               => 'sometimes|integer|min:7|max:90',
            'min_margin_percent'             => 'sometimes|numeric|min:5.0|max:50.0',
            'tax_ppn_enabled'                => 'sometimes|boolean',
            'tax_ppn_percent'                => 'sometimes|numeric|min:0|max:100',
            'default_shipping_fee'           => 'sometimes|numeric|min:0',
            'free_shipping_min_order'        => 'sometimes|numeric|min:0',
        ]);

        if ($request->has('tax_ppn_enabled')) {
            $validated['tax_ppn_enabled'] = $request->boolean('tax_ppn_enabled');
            $percent = $validated['tax_ppn_percent'] ?? 11.0;
            $validated['tax_ppn_note'] = $validated['tax_ppn_enabled']
                ? "Pajak Pertambahan Nilai (PPN {$percent}%)"
                : 'Fasilitas Bebas PPN Produk Pertanian DTP (PPN 0%)';
        }

        $this->tataKelolaService->updateSettings($validated);

        $directorName = Auth::user()?->name ?: 'Direktur Utama';
        $ppnStatus = !empty($validated['tax_ppn_enabled'])
            ? "PPN Aktif (" . ($validated['tax_ppn_percent'] ?? 11) . "%)"
            : 'Bebas PPN 0% (DTP Pertanian)';

        $this->tataKelolaService->addAuditLog(
            'PARAMETER_POLICY_UPDATE',
            'CFG-MASTER-POLICY',
            "Direktur memperbarui parameter kebijakan: {$ppnStatus}, Standar Ongkir Rp " . number_format($validated['default_shipping_fee'] ?? 75000, 0, ',', '.'),
            $directorName,
            'DIR-01',
            'UPDATED'
        );

        return back()->with('success', 'Konfigurasi parameter dan kebijakan bisnis (PPN & Distribusi) berhasil diperbarui.');
    }

    // Mengaktifkan atau menonaktifkan Master Freeze Switch untuk menghentikan transaksi darurat sistem
    public function toggleMasterFreeze(Request $request)
    {
        $currentlyFrozen = $this->tataKelolaService->isMasterFreezeActive();

        if ($request->has('freeze')) {
            $freezeValue = $request->input('freeze');
            $freeze = ($freezeValue === '1' || $freezeValue === 1 || $freezeValue === true || $freezeValue === 'true');
        } else {
            $freeze = !$currentlyFrozen;
        }

        $reason = $freeze
            ? $request->input('reason', 'Audit Kepatuhan & Stock Opname Berkala Direksi')
            : null;
        $directorName = Auth::user()?->name ?: 'Direktur Utama';

        $isActive = $this->tataKelolaService->toggleMasterFreeze($freeze, $reason, $directorName);

        $message = $isActive
            ? "MASTER FREEZE TELAH DIAKTIFKAN. Seluruh mutasi stok dan transaksi input PO dihentikan."
            : "Master Freeze berhasil dinonaktifkan. Seluruh sistem operasional kembali normal.";

        return back()->with('success', $message);
    }

    // Menetapkan delegasi wewenang operasional Pelaksana Tugas (Plt) Direksi beserta batasan plafon mandat
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
            $delegateUser = User::find($validated['delegate_id']);
            $delegateName = $delegateUser ? "{$delegateUser->name} ({$delegateUser->role})" : null;
        }

        $ceiling = isset($validated['plt_ceiling_amount']) ? (float) $validated['plt_ceiling_amount'] : null;
        $durationHours = isset($validated['plt_duration_hours']) ? (int) $validated['plt_duration_hours'] : null;
        $directorName = Auth::user()?->name ?: 'Direktur Utama';

        $this->tataKelolaService->configurePlt($pltActive, $delegateName, $ceiling, $durationHours, $directorName);
        $statusStr = $pltActive ? 'DIAKTIFKAN' : 'DINONAKTIFKAN';
        $this->tataKelolaService->addAuditLog(
            'PLT_SUCCESSION_CONFIG',
            'SEC-PLT-MANDATE',
            "Mandat Plt Direktur {$statusStr}. Penerima: " . ($delegateName ?: '-') . ", Plafon: Rp " . number_format($ceiling ?? 0, 0, ',', '.'),
            $directorName,
            'DIR-01',
            'UPDATED'
        );

        return back()->with('success', "Konfigurasi mandat Plt Direksi berhasil disimpan ({$statusStr}).");
    }

    // Mengunduh rekam jejak audit forensik lengkap (append-only audit trail) dalam format CSV
    public function exportAuditLog(Request $request): StreamedResponse
    {
        $filename = 'Full_Audit_Trail_' . date('Ymd_His') . '.csv';
        $logs = $this->tataKelolaService->getAuditLogs();

        return $this->streamCsv($filename, function ($handle) use ($logs) {
            fputcsv($handle, ['LOG AUDIT FORENSIK APPEND-ONLY SHA-256 - KOPERASI GPA'], ';');
            fputcsv($handle, ['TANGGAL CETAK', date('d-m-Y H:i:s') . ' WIB'], ';');
            fputcsv($handle, [], ';');
            fputcsv($handle, ['Timestamp', 'Kategori Aksi', 'Dokumen Ref', 'Aktor', 'Clearance Level', 'Status', 'Rincian Perubahan', 'Hash SHA-256'], ';');

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
                ], ';');
            }
        });
    }

    // Menyimpan konfigurasi rekening bank dan barcode QRIS resmi koperasi beserta bukti logo digital
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
        foreach ($validated['channels'] as $index => $channelItem) {
            $imageUrl = $channelItem['image_url'] ?? null;

            // Jika ada pengunggahan berkas gambar barcode QRIS baru
            if ($request->hasFile("channels.{$index}.image")) {
                $file = $request->file("channels.{$index}.image");
                if ($file->isValid()) {
                    $extension = $file->getClientOriginalExtension() ?: 'png';
                    $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '_', $channelItem['id']);
                    $filename = "channel_{$safeId}_" . time() . "_{$index}.{$extension}";
                    $path = $file->storeAs('payment-channels', $filename, 'public');
                    $imageUrl = '/storage/' . $path;
                }
            }

            if ($channelItem['type'] === 'QRIS' && empty($imageUrl)) {
                $imageUrl = '/storage/payment-channels/qris-gpa-default.png';
            }

            $logoUrl = $channelItem['logo_url'] ?? null;
            if (empty($logoUrl)) {
                $candidate = '/images/payments/' . strtolower($channelItem['id']) . '.svg';
                if (file_exists(public_path(ltrim($candidate, '/')))) {
                    $logoUrl = $candidate;
                }
            }

            $channels[] = [
                'id'             => $channelItem['id'],
                'type'           => $channelItem['type'],
                'name'           => $channelItem['name'],
                'bank_code'      => $channelItem['bank_code'] ?? strtoupper($channelItem['id']),
                'account_number' => $channelItem['account_number'],
                'account_name'   => $channelItem['account_name'],
                'description'    => $channelItem['description'] ?? '',
                'instructions'   => $channelItem['instructions'] ?? '',
                'image_url'      => $imageUrl,
                'logo_url'       => $logoUrl,
                'is_active'      => !empty($channelItem['is_active']),
            ];
        }

        $this->tataKelolaService->updatePaymentChannels($channels);

        $directorName = Auth::user()?->name ?: 'Direktur Utama';
        $activeCount = count(array_filter($channels, fn($channel) => $channel['is_active']));
        $this->tataKelolaService->addAuditLog(
            'PAYMENT_CHANNELS_UPDATE',
            'CFG-PAYMENT-GATEWAY',
            "Direktur memperbarui konfigurasi kanal pembayaran resmi ({$activeCount} kanal aktif: Bank, QRIS, & Dompet Digital GPA).",
            $directorName,
            'DIR-01',
            'UPDATED'
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Konfigurasi kanal pembayaran resmi berhasil disimpan.',
                'channels' => $channels,
            ]);
        }

        return back()->with('success', 'Konfigurasi kanal pembayaran GPA (Bank, QRIS, & Dompet Digital) berhasil disimpan oleh Direktur.');
    }
}
