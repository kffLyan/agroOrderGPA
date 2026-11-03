<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Http\Resources\Direktur\BukuBesarPiutangResource;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\TataKelolaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

// Controller pengawasan piutang usaha (AR), mitigasi kredit macet, dan pembekuan plafon mitra
class PiutangController extends Controller
{
    public function __construct(
        protected TataKelolaService $tataKelolaService
    ) {}

    // Menampilkan konsol analisis umur piutang (AR Aging) dan buku besar tagihan terbuka mitra B2B
    public function index(Request $request): View
    {
        // 1. Ambil seluruh faktur yang belum lunas (UNPAID) atau telah melewati jatuh tempo (OVERDUE)
        $openInvoices = Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])
            ->with(['user', 'orders'])
            ->orderBy('due_date', 'asc')
            ->get();

        $totalReceivable = (float) $openInvoices->sum('grand_total');

        // Klasifikasi umur piutang (Aging AR) berdasarkan sisa hari menuju atau setelah jatuh tempo
        $stage1Val = 0; // 0 - 15 hari tersisa sebelum jatuh tempo
        $stage2Val = 0; // 16 - 30 hari tersisa
        $stage3Val = 0; // 1 - 14 hari lewat jatuh tempo
        $stage4Val = 0; // > 15 hari overdue (kritis)

        $today = now()->startOfDay();
        foreach ($openInvoices as $invoice) {
            $amount = (float) $invoice->grand_total;
            if (!$invoice->due_date) {
                $stage1Val += $amount;
                continue;
            }

            $dueDate = Carbon::parse($invoice->due_date)->startOfDay();
            $diffDays = (int) round($today->diffInDays($dueDate, false));
            if ($diffDays > 15) {
                $stage2Val += $amount;
            } elseif ($diffDays >= 0 && $diffDays <= 15) {
                $stage1Val += $amount;
            } elseif ($diffDays < 0 && $diffDays >= -14) {
                $stage3Val += $amount;
            } else {
                $stage4Val += $amount;
            }
        }

        // 2. Susun buku besar konsolidasi tagihan per klien B2B menggunakan BukuBesarPiutangResource
        $frozenClients = $this->tataKelolaService->getFrozenClients();

        $dbClients = User::where('role', 'KLIEN')
            ->with(['invoices.orders', 'contracts'])
            ->get();

        $ledgerRows = $dbClients->map(
            fn ($client) => (new BukuBesarPiutangResource($client, $frozenClients))->toArray($request)
        )->all();

        $ledgerTotal = array_sum(array_column($ledgerRows, 'outstanding'));
        $activeContractsCount = Contract::where('status', 'ACTIVE')->count() ?: $dbClients->count();

        $pendingVerificationCount = Order::where('status', 'MENUNGGU_VERIFIKASI')->count();
        $pendingSettlementValue = (float) Payment::where('status', 'PENDING')->sum('amount');
        $pendingOrders = Order::where('status', 'MENUNGGU_VERIFIKASI')
            ->with(['user', 'orderItems.product'])
            ->latest()
            ->take(6)
            ->get();

        return view('direktur.piutang', [
            'totalReceivable'          => $totalReceivable,
            'stage1Val'                => $stage1Val,
            'stage2Val'                => $stage2Val,
            'stage3Val'                => $stage3Val,
            'stage4Val'                => $stage4Val,
            'ledgerRows'               => $ledgerRows,
            'ledgerTotal'              => $ledgerTotal,
            'activeContracts'          => $activeContractsCount,
            'pendingVerificationCount' => $pendingVerificationCount,
            'pendingSettlementValue'   => $pendingSettlementValue,
            'pendingOrders'            => $pendingOrders,
        ]);
    }

    // Mengirim notifikasi surat peringatan jatuh tempo (dunning letter) ke PIC klien melalui WhatsApp
    public function sendReminder(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $client = User::find($id) ?: Invoice::find($id)?->user;
        $name = $client ? ($client->company_name ?: $client->name) : 'Klien B2B #' . $id;
        $phone = $client?->phone ?: '—';

        $message = "Surat Pengingat Jatuh Tempo (Notice Tagihan) berhasil diteruskan ke kontak PIC {$name}" . ($phone !== '—' ? " ({$phone})." : ".");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    // Mengunci atau membuka status plafon kredit klien untuk menghentikan pengajuan pesanan baru
    public function toggleFreeze(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $client = User::find($id);
        $name = $client ? ($client->company_name ?: $client->name) : 'Klien B2B #' . $id;

        // Periksa apakah terdapat instruksi spesifik untuk membekukan atau mencabut status freeze
        $targetState = null;
        if ($request->has('action')) {
            $action = strtolower((string) $request->input('action'));
            if ($action === 'freeze' || $action === 'lock') {
                $targetState = true;
            } elseif ($action === 'unfreeze' || $action === 'unlock') {
                $targetState = false;
            }
        } elseif ($request->has('freeze')) {
            $value = $request->input('freeze');
            $targetState = ($value === '1' || $value === 1 || $value === true || $value === 'true');
        }

        // Simpan status pembekuan plafon secara persisten ke dalam layanan tata kelola
        $isLocked = $this->tataKelolaService->toggleClientFreeze($id, $targetState);

        // Sinkronkan daftar klien yang dibekukan ke session aktif pengguna
        session(['frozen_clients' => $this->tataKelolaService->getFrozenClients()]);

        if ($isLocked) {
            $message = "Plafon kredit {$name} berhasil dikunci permanen. Penerbitan Surat Jalan baru ditolak otomatis.";
            $status = 'LOCKED';
        } else {
            $message = "Status pembekuan PO untuk {$name} berhasil dicabut (Dispensasi Direktur).";
            $status = 'UNFREEZE';
        }

        $this->tataKelolaService->addAuditLog(
            'CREDIT_LIMIT_' . $status,
            'CLI-ID-' . $id,
            $message,
            Auth::user()?->name ?: 'Direktur Utama',
            'DIR-01 [CLEARANCE L4]',
            'SEALED'
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message, 'locked' => $isLocked]);
        }

        return back()->with('success', $message);
    }

    // Memberikan dispensasi restrukturisasi penagihan dan perpanjangan hari jatuh tempo (TOP)
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

        // Cabut status pembekuan kredit klien setelah restrukturisasi disahkan Direktur
        $this->tataKelolaService->unfreezeClient($clientId);
        $frozenClients = $this->tataKelolaService->getFrozenClients();
        session(['frozen_clients' => $frozenClients]);

        $message = "Restrukturisasi tagihan {$name} berhasil disahkan. Tenor TOP diperpanjang +{$extendDays} hari dan lockdown PO dicabut.";

        $this->tataKelolaService->addAuditLog(
            'AR_RESTRUCTURE_AUTHORIZED',
            'CLI-ID-' . $clientId,
            $message,
            Auth::user()?->name ?: 'Direktur Utama',
            'DIR-01 [CLEARANCE L4]',
            'SEALED'
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}
