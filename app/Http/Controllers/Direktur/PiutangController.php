<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Http\Resources\Direktur\ReceivableLedgerResource;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\User;
use App\Services\GovernanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller Monitoring Piutang & Manajemen Risiko Kredit Klien B2B Direktur.
 *
 * Penjelasan UKK:
 * Modul ini berfungsi memantau tagihan tempo (Aging Accounts Receivable / AR),
 * penguncian plafon kredit klien yang menunggak (*Credit Freeze*),
 * pengiriman peringatan jatuh tempo via WhatsApp, dan otorisasi restrukturisasi tenor.
 */
class PiutangController extends Controller
{
    public function __construct(
        protected GovernanceService $governanceService
    ) {}

    /**
     * Menampilkan daftar piutang berjalan, klasifikasi umur piutang, dan buku besar klien.
     */
    public function index(Request $request): View
    {
        // 1. Ambil faktur B2B terbuka (UNPAID / OVERDUE)
        $openInvoices = Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])
            ->with(['user', 'orders'])
            ->orderBy('due_date', 'asc')
            ->get();

        $totalReceivable = (float) $openInvoices->sum('grand_total');

        // Klasifikasi Aging AR (Analisis Umur Piutang)
        $stage1Val = 0; // 0 - 15 hari tersisa sebelum jatuh tempo
        $stage2Val = 0; // 16 - 30 hari tersisa
        $stage3Val = 0; // 1 - 14 hari lewat jatuh tempo
        $stage4Val = 0; // > 15 hari overdue (kritis)

        foreach ($openInvoices as $inv) {
            $amount = (float) $inv->grand_total;
            if (!$inv->due_date) {
                $stage1Val += $amount;
                continue;
            }

            $diff = (int) now()->diffInDays($inv->due_date, false);
            if ($diff > 15) {
                $stage2Val += $amount;
            } elseif ($diff >= 0 && $diff <= 15) {
                $stage1Val += $amount;
            } elseif ($diff < 0 && $diff >= -14) {
                $stage3Val += $amount;
            } else {
                $stage4Val += $amount;
            }
        }

        // 2. Buku Besar Faktur Konsolidasi per Klien B2B via ReceivableLedgerResource
        $frozenClients = $this->governanceService->getFrozenClients();

        $dbClients = User::where('role', 'KLIEN')
            ->with(['invoices.orders', 'contracts'])
            ->get();

        $ledgerRows = $dbClients->map(
            fn ($c) => (new ReceivableLedgerResource($c, $frozenClients))->toArray($request)
        )->all();

        $ledgerTotal = array_sum(array_column($ledgerRows, 'outstanding'));
        $activeContractsCount = Contract::where('status', 'ACTIVE')->count() ?: $dbClients->count();

        return view('direktur.piutang', [
            'totalReceivable' => $totalReceivable,
            'stage1Val'       => $stage1Val,
            'stage2Val'       => $stage2Val,
            'stage3Val'       => $stage3Val,
            'stage4Val'       => $stage4Val,
            'ledgerRows'      => $ledgerRows,
            'ledgerTotal'     => $ledgerTotal,
            'activeContracts' => $activeContractsCount,
        ]);
    }

    /**
     * Kirim Pengingat / Reminder Tagihan via WhatsApp (Simulasi Direksi).
     */
    public function sendReminder(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $client = User::find($id) ?: Invoice::find($id)?->user;
        $name = $client ? ($client->company_name ?: $client->name) : 'Klien B2B #' . $id;
        $phone = $client?->phone ?: '—';

        $msg = "Surat Pengingat Jatuh Tempo (Notice Tagihan) berhasil diteruskan ke kontak PIC {$name}" . ($phone !== '—' ? " ({$phone})." : ".");
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Kunci / Bekukan Plafon Kredit Klien (Auto-Freeze PO).
     */
    public function toggleFreeze(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $client = User::find($id);
        $name = $client ? ($client->company_name ?: $client->name) : 'Klien B2B #' . $id;

        // Tentukan apakah ada instruksi eksplisit: 'freeze' / 'unfreeze'
        $targetState = null;
        if ($request->has('action')) {
            $act = strtolower((string) $request->input('action'));
            if ($act === 'freeze' || $act === 'lock') {
                $targetState = true;
            } elseif ($act === 'unfreeze' || $act === 'unlock') {
                $targetState = false;
            }
        } elseif ($request->has('freeze')) {
            $val = $request->input('freeze');
            $targetState = ($val === '1' || $val === 1 || $val === true || $val === 'true');
        }

        // Simpan status pembekuan secara persisten ke GovernanceService
        $isLocked = $this->governanceService->toggleClientFreeze($id, $targetState);

        // Sinkronkan ke session
        session(['frozen_clients' => $this->governanceService->getFrozenClients()]);

        if ($isLocked) {
            $msg = "Plafon kredit {$name} berhasil dikunci permanen. Penerbitan Surat Jalan baru ditolak otomatis.";
            $status = 'LOCKED';
        } else {
            $msg = "Status pembekuan PO untuk {$name} berhasil dicabut (Dispensasi Direktur).";
            $status = 'UNFREEZE';
        }

        $this->governanceService->addAuditLog(
            'CREDIT_LIMIT_' . $status,
            'CLI-ID-' . $id,
            $msg,
            Auth::user()?->name ?: 'Direktur Utama',
            'DIR-01 [CLEARANCE L4]',
            'SEALED'
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg, 'locked' => $isLocked]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Restrukturisasi Tagihan / Perpanjangan Tenor oleh Direktur.
     */
    public function restructure(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $client = User::find($id);
        $invoice = null;
        if (!$client) {
            $invoice = Invoice::find($id);
            $client = $invoice?->user;
        }

        $name = $client ? ($client->company_name ?: $client->name) : 'Klien B2B #' . $id;
        $extendDays = (int) ($request->input('extended_days') ?: $request->input('extend_days', 14));

        if ($invoice) {
            $invoice->update([
                'due_date' => now()->addDays($extendDays)->toDateString(),
                'status'   => 'UNPAID',
            ]);
        } elseif ($client) {
            Invoice::where('user_id', $client->id)
                ->whereIn('status', ['UNPAID', 'OVERDUE'])
                ->update([
                    'due_date' => now()->addDays($extendDays)->toDateString(),
                    'status'   => 'UNPAID',
                ]);
        }

        $clientId = $client?->id ?: $id;

        // Cabut status pembekuan kredit klien
        $this->governanceService->unfreezeClient($clientId);
        $frozenClients = $this->governanceService->getFrozenClients();
        session(['frozen_clients' => $frozenClients]);

        $msg = "Restrukturisasi tagihan {$name} berhasil disahkan. Tenor TOP diperpanjang +{$extendDays} hari dan lockdown PO dicabut.";

        $this->governanceService->addAuditLog(
            'AR_RESTRUCTURE_AUTHORIZED',
            'CLI-ID-' . $clientId,
            $msg,
            Auth::user()?->name ?: 'Direktur Utama',
            'DIR-01 [CLEARANCE L4]',
            'SEALED'
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }
}
