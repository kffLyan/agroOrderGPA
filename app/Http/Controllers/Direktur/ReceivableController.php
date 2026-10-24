<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use App\Services\GovernanceService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function index(Request $request): View
    {
        // 1. Ambil faktur B2B terbuka (UNPAID / OVERDUE)
        $openInvoices = Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])
            ->with(['user', 'orders'])
            ->orderBy('due_date', 'asc')
            ->get();

        $totalReceivable = (float) $openInvoices->sum('grand_total');

        // Klasifikasi Aging AR
        $stage1Val = 0; // 0 - 15 hari tersisa
        $stage2Val = 0; // 16 - 30 hari tersisa
        $stage3Val = 0; // 1 - 14 hari lewat
        $stage4Val = 0; // > 15 hari overdue

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

        // 2. Buku Besar Faktur Konsolidasi per Klien B2B
        $frozenClients = session('frozen_clients', []);

        // Ambil klien dari DB
        $dbClients = User::where('role', 'KLIEN')
            ->with(['invoices' => function ($q) {
                $q->with('orders');
            }, 'contracts'])
            ->get();

        $ledgerRows = [];
        $ledgerTotal = 0;

        foreach ($dbClients as $c) {
            $unpaidInvoices = $c->invoices->whereIn('status', ['UNPAID', 'OVERDUE']);
            $unpaid = (float) $unpaidInvoices->sum('grand_total');

            $contract = $c->contracts->where('status', 'ACTIVE')->first();
            $limit = (float) ($contract?->committed_volume_per_cycle ? ($contract->committed_volume_per_cycle * 25000) : 100000000);
            $topDays = $contract ? (int) $contract->top_days : 30;
            $exposure = $unpaid;
            $utilisation = $limit > 0 ? round(($exposure / $limit) * 100, 1) : 0;
            $isLocked = !empty($frozenClients[$c->id]) || $utilisation > 100;

            $sjList = [];
            $earliestDue = null;
            foreach ($unpaidInvoices as $inv) {
                if (!$earliestDue || ($inv->due_date && $inv->due_date < $earliestDue)) {
                    $earliestDue = $inv->due_date;
                }
                foreach ($inv->orders as $ord) {
                    if ($ord->surat_jalan_number) {
                        $sjList[] = $ord->surat_jalan_number;
                    }
                }
            }

            $dueDateCarbon = $earliestDue ? Carbon::parse($earliestDue) : now()->addDays($topDays);
            $days = (int) now()->diffInDays($dueDateCarbon, false);

            $statusVal = 'LANCAR';
            $statusTone = 'success';
            if ($isLocked || ($days < 0 && abs($days) > 7)) {
                $statusVal = 'AUTO-FREEZE PO';
                $statusTone = 'danger';
            } elseif ($days < 0) {
                $statusVal = 'WARNING TEMPO';
                $statusTone = 'warning';
            }

            $verificationTone = $isLocked ? 'danger' : ($days < 0 ? 'warning' : 'success');
            $verificationVal = $isLocked ? 'DEFISIT PLAFON' : ($days < 0 ? 'BUTUH KONFIRMASI WA' : 'TERVERIFIKASI FINANCE');

            $cleanName = preg_replace('/[^A-Za-z]/', '', $c->name) ?: 'KLI';
            $code = 'B2B-' . strtoupper(substr($cleanName, 0, 3)) . '-' . str_pad($c->id, 3, '0', STR_PAD_LEFT);

            $ledgerRows[] = [
                'id'                => $c->id,
                'name'              => $c->company_name ?: $c->name,
                'client_id'         => 'ID-KLIEN:' . $code,
                'client_code'       => $code,
                'locked'            => $isLocked,
                'sj_count'          => count($sjList),
                'sj_count_label'    => count($sjList) > 0 ? (count($sjList) . ' Surat Jalan') : 'Belum Ada SJ',
                'sj_label'          => !empty($sjList) ? implode(', ', array_slice($sjList, 0, 3)) : '—',
                'outstanding'       => $unpaid,
                'outstanding_label' => 'Rp ' . number_format($unpaid, 0, ',', '.'),
                'verification'      => $verificationVal,
                'verification_tone' => $verificationTone,
                'top_days'          => $topDays,
                'top_label'         => 'TOP ' . $topDays . ' HARI',
                'due_date'          => $dueDateCarbon,
                'due_label'         => $dueDateCarbon->translatedFormat('d M Y'),
                'days_remaining'    => $days,
                'days_label'        => $days < 0 ? '(! OVERDUE ' . abs($days) . ' HARI)' : '(' . $days . ' Hari Tersisa)',
                'status_label'      => $statusVal,
                'status_tone'       => $statusTone,
                'limit'             => $limit,
                'exposure'          => $exposure,
                'limit_label'       => 'Rp ' . round($exposure / 1000000) . 'M / ' . round($limit / 1000000) . 'M',
                'utilisation'       => $utilisation,
                'utilisation_label' => round($utilisation) . '%',
                'utilisation_note'  => $utilisation > 100 ? round($utilisation) . '% (OVER)' : round($utilisation) . '%',
                'utilisation_width' => min(100, $utilisation),
                'over_limit'        => $utilisation > 100,
                'actions'           => $isLocked ? ['Restrukturisasi Tagihan', 'Lepas Freeze'] : ['Detail Faktur', 'Kunci Plafon'],
            ];
            $ledgerTotal += $unpaid;
        }

        $activeContractsCount = \App\Models\Contract::where('status', 'ACTIVE')->count() ?: $dbClients->count();

        return view('direktur.receivables', [
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
     * Kirim Pengingat / Reminder Tagihan via WhatsApp (Simulasi Direksi)
     */
    public function sendReminder(Request $request, int $id)
    {
        $client = User::find($id);
        if (!$client) {
            $client = Invoice::find($id)?->user;
        }
        $name = $client ? ($client->company_name ?: $client->name) : 'Klien B2B #' . $id;
        $phone = $client?->phone ?: '—';

        $msg = "Surat Pengingat Jatuh Tempo (Notice Tagihan) berhasil diteruskan ke kontak PIC {$name}" . ($phone !== '—' ? " ({$phone})." : ".");
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Kunci / Bekukan Plafon Kredit Klien (Auto-Freeze PO)
     */
    public function toggleFreeze(Request $request, int $id)
    {
        $client = User::find($id);
        $name = $client ? ($client->company_name ?: $client->name) : 'Klien B2B #' . $id;

        $frozenClients = session('frozen_clients', []);

        if (!empty($frozenClients[$id])) {
            unset($frozenClients[$id]);
            $msg = "Status pembekuan PO untuk {$name} berhasil dicabut (Dispensasi Direktur).";
            $status = 'UNFREEZE';
            $isLocked = false;
        } else {
            $frozenClients[$id] = true;
            $msg = "Plafon kredit {$name} berhasil dikunci permanen. Penerbitan Surat Jalan baru ditolak otomatis.";
            $status = 'LOCKED';
            $isLocked = true;
        }

        session(['frozen_clients' => $frozenClients]);

        $this->governanceService->addAuditLog(
            'CREDIT_LIMIT_' . $status,
            'CLI-ID-' . $id,
            $msg,
            auth()->user()?->name ?: 'Direktur Utama',
            'DIR-01 [CLEARANCE L4]',
            'SEALED'
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg, 'locked' => $isLocked]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Restrukturisasi Tagihan / Perpanjangan Tenor oleh Direktur
     */
    public function restructure(Request $request, int $id)
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
            $invoices = Invoice::where('user_id', $client->id)->whereIn('status', ['UNPAID', 'OVERDUE'])->get();
            foreach ($invoices as $inv) {
                $inv->update([
                    'due_date' => now()->addDays($extendDays)->toDateString(),
                    'status'   => 'UNPAID',
                ]);
            }
        }

        $clientId = $client?->id ?: $id;
        $frozenClients = session('frozen_clients', []);
        unset($frozenClients[$clientId]);
        session(['frozen_clients' => $frozenClients]);

        $msg = "Restrukturisasi tagihan {$name} berhasil disahkan. Tenor TOP diperpanjang +{$extendDays} hari dan lockdown PO dicabut.";

        $this->governanceService->addAuditLog(
            'AR_RESTRUCTURE_AUTHORIZED',
            'CLI-ID-' . $clientId,
            $msg,
            auth()->user()?->name ?: 'Direktur Utama',
            'DIR-01 [CLEARANCE L4]',
            'SEALED'
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }
}
