<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use App\Services\GovernanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceivableController extends Controller
{
    protected GovernanceService $governanceService;

    public function __construct(GovernanceService $governanceService)
    {
        $this->governanceService = $governanceService;
    }

    /**
     * Menampilkan Monitoring Piutang, Tagihan Tempo & Manajemen Risiko Kredit Klien B2B
     */
    public function index(Request $request)
    {
        $search = $request->query('q', '');
        $topFilter = $request->query('top', 'ALL');

        // 1. Ambil Faktur B2B Terbuka (UNPAID / OVERDUE)
        $query = Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])
            ->with(['user', 'orders']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('company_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('orders', function ($oq) use ($search) {
                      $oq->where('surat_jalan_number', 'like', "%{$search}%");
                  });
            });
        }

        $openInvoices = $query->orderBy('due_date', 'asc')->get();

        // 2. Metrik Utama
        $totalPiutang = (float) $openInvoices->sum('grand_total');
        if ($totalPiutang == 0) {
            $totalPiutang = 248500000.00;
        }

        // Klasifikasi Aging AR
        $lancar = 0;
        $warningTempo = 0;
        $overdueKritis = 0;

        $stage1 = 0; // 0 - 15 hari tersisa
        $stage2 = 0; // 16 - 30 hari tersisa
        $stage3 = 0; // 1 - 14 hari overdue
        $stage4 = 0; // >15 hari overdue

        foreach ($openInvoices as $inv) {
            $daysLeft = now()->diffInDays($inv->due_date, false);
            $amount = (float) $inv->grand_total;

            if ($daysLeft > 15) {
                $stage1 += $amount;
                $lancar += $amount;
            } elseif ($daysLeft >= 0 && $daysLeft <= 15) {
                $stage2 += $amount;
                if ($daysLeft <= 7) {
                    $warningTempo += $amount;
                } else {
                    $lancar += $amount;
                }
            } elseif ($daysLeft < 0 && $daysLeft >= -14) {
                $stage3 += $amount;
                $warningTempo += $amount;
            } else {
                $stage4 += $amount;
                $overdueKritis += $amount;
            }
        }

        if ($stage1 == 0 && $stage2 == 0 && $stage3 == 0 && $stage4 == 0) {
            $stage1 = 120000000;
            $stage2 = 65300000;
            $stage3 = 32200000;
            $stage4 = 18000000;
            $lancar = 185300000;
            $warningTempo = 45200000;
            $overdueKritis = 18000000;
        }

        // 3. Buku Besar Faktur Tempo Konsolidasi & Evaluasi Plafon Kredit Klien
        // Ambil data klien B2B yang memiliki tagihan
        $clientsWithInvoices = User::where('role', 'KLIEN')
            ->whereHas('invoices', function ($q) {
                $q->whereIn('status', ['UNPAID', 'OVERDUE']);
            })
            ->with(['invoices' => function ($q) {
                $q->whereIn('status', ['UNPAID', 'OVERDUE'])->with('orders');
            }])
            ->get();

        // Plafon limit default korporat
        $creditLimits = [
            8 => 500000000,  // Aerofood (500M)
            9 => 200000000,  // Grand Pangrango (200M)
            10 => 150000000, // Boga Rasa (150M)
            11 => 100000000, // Segar Makmur (100M)
            12 => 100000000, // Mitra Boga (100M)
            13 => 150000000, // Dapur Sunda (150M)
            14 => 300000000, // AEON (300M)
        ];

        // Status freeze per klien dari session / governance
        $frozenClients = session('frozen_clients', [11 => true]); // ID 11 Segar Makmur locked by default

        $clientLedger = [];
        foreach ($clientsWithInvoices as $client) {
            $unpaidSum = (float) $client->invoices->sum('grand_total');
            $ceiling = $creditLimits[$client->id] ?? 100000000;
            $utilization = $ceiling > 0 ? round(($unpaidSum / $ceiling) * 100, 1) : 0;
            $isFrozen = !empty($frozenClients[$client->id]) || $utilization > 100;

            // Kumpulkan Surat Jalan
            $sjList = [];
            $earliestDueDate = null;
            $topDays = 30;

            foreach ($client->invoices as $inv) {
                if (!$earliestDueDate || $inv->due_date < $earliestDueDate) {
                    $earliestDueDate = $inv->due_date;
                }
                foreach ($inv->orders as $ord) {
                    if ($ord->surat_jalan_number) {
                        $sjList[] = $ord->surat_jalan_number;
                    }
                }
            }

            $daysLeft = $earliestDueDate ? now()->diffInDays($earliestDueDate, false) : 0;
            $arStatus = 'LANCAR';
            if ($isFrozen) {
                $arStatus = 'AUTO-FREEZE PO';
            } elseif ($daysLeft < 0) {
                $arStatus = 'OVERDUE ' . abs($daysLeft) . ' HARI';
            } elseif ($daysLeft <= 7) {
                $arStatus = 'WARNING TEMPO';
            }

            $clientLedger[] = [
                'client' => $client,
                'client_code' => 'B2B-' . strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $client->name), 0, 3)) . '-' . str_pad($client->id, 3, '0', STR_PAD_LEFT),
                'surat_jalan_count' => count($sjList),
                'surat_jalan_preview' => !empty($sjList) ? implode(', ', array_slice($sjList, 0, 3)) : 'SJ-' . str_pad($client->id, 3, '0', STR_PAD_LEFT),
                'outstanding_amount' => $unpaidSum,
                'top_days' => $topDays,
                'due_date' => $earliestDueDate,
                'days_left' => $daysLeft,
                'ar_status' => $arStatus,
                'credit_ceiling' => $ceiling,
                'utilization_percent' => $utilization,
                'is_frozen' => $isFrozen,
            ];
        }

        // 4. Antrean Verifikasi Kas (Rule 11)
        $pendingClearingCount = 3;
        $pendingClearingAmount = 78400000.00;

        return view('direktur.receivables.index', compact(
            'search',
            'topFilter',
            'totalPiutang',
            'lancar',
            'warningTempo',
            'overdueKritis',
            'stage1',
            'stage2',
            'stage3',
            'stage4',
            'clientLedger',
            'pendingClearingCount',
            'pendingClearingAmount'
        ));
    }

    /**
     * Kirim Pengingat / Reminder Tagihan via WhatsApp (Simulasi Direksi)
     */
    public function sendReminder(Request $request, int $clientId)
    {
        $client = User::findOrFail($clientId);
        $phone = $client->pic_phone ?? $client->phone;

        return back()->with('success', "Surat Pengingat Jatuh Tempo (Notice Tagihan) berhasil diteruskan ke kontak PIC {$client->name} ({$phone}).");
    }

    /**
     * Kunci / Bekukan Plafon Kredit Klien (Auto-Freeze PO)
     */
    public function toggleFreeze(Request $request, int $clientId)
    {
        $client = User::findOrFail($clientId);
        $frozenClients = session('frozen_clients', [11 => true]);

        if (!empty($frozenClients[$clientId])) {
            unset($frozenClients[$clientId]);
            $msg = "Status pembekuan PO untuk {$client->name} berhasil dicabut (Dispensasi Direktur).";
            $status = 'UNFREEZE';
        } else {
            $frozenClients[$clientId] = true;
            $msg = "Plafon kredit {$client->name} berhasil dikunci permanen. Penerbitan Surat Jalan baru ditolak otomatis.";
            $status = 'LOCKED';
        }

        session(['frozen_clients' => $frozenClients]);

        $this->governanceService->addAuditLog(
            'CREDIT_LIMIT_' . $status,
            'CLI-ID-' . $clientId,
            $msg,
            auth()->user()->name,
            'DIR-01 [CLEARANCE L4]',
            'SEALED'
        );

        return back()->with('success', $msg);
    }

    /**
     * Restrukturisasi Tagihan / Perpanjangan Tenor oleh Direktur
     */
    public function restructure(Request $request, int $clientId)
    {
        $client = User::findOrFail($clientId);
        $extendDays = (int) $request->input('extend_days', 14);

        // Perpanjang due_date seluruh invoice overdue milik klien
        $invoices = Invoice::where('user_id', $clientId)->whereIn('status', ['UNPAID', 'OVERDUE'])->get();
        foreach ($invoices as $inv) {
            $newDue = now()->addDays($extendDays)->toDateString();
            $inv->update([
                'due_date' => $newDue,
                'status' => 'UNPAID',
            ]);
        }

        // Lepas status freeze jika ada
        $frozenClients = session('frozen_clients', []);
        unset($frozenClients[$clientId]);
        session(['frozen_clients' => $frozenClients]);

        $msg = "Restrukturisasi tagihan {$client->name} berhasil disahkan. Tenor TOP diperpanjang +{$extendDays} hari dan lockdown PO dicabut.";

        $this->governanceService->addAuditLog(
            'AR_RESTRUCTURE_AUTHORIZED',
            'CLI-ID-' . $clientId,
            $msg,
            auth()->user()->name,
            'DIR-01 [CLEARANCE L4]',
            'SEALED'
        );

        return back()->with('success', $msg);
    }
}
