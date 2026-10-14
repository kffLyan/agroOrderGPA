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
        if ($totalReceivable <= 0) {
            $totalReceivable = 248500000;
        }

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

            $diff = now()->diffInDays($inv->due_date, false);
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

        // Fallback jika database belum ada transaksi beragam
        if ($stage1Val == 0 && $stage2Val == 0 && $stage3Val == 0 && $stage4Val == 0) {
            $stage1Val = 120000000;
            $stage2Val = 65300000;
            $stage3Val = 45200000;
            $stage4Val = 18000000;
            $totalReceivable = 248500000;
        }

        // 2. Buku Besar Faktur Konsolidasi per Klien B2B
        $frozenClients = session('frozen_clients', [11 => true]);

        $defaultLedger = [
            [
                'name'              => 'PT Aerofood ACS',
                'client_id'         => 'B2B-ACS-091',
                'locked'            => false,
                'sj_count'          => 4,
                'sj_label'          => 'SJ-088 s/d SJ-091',
                'outstanding'       => 114200000,
                'verification'      => 'TERVERIFIKASI FINANCE',
                'verification_tone' => 'success',
                'top_days'          => 45,
                'due_date'          => '2026-11-15',
                'limit'             => 500000000,
                'exposure'          => 314000000,
                'actions'           => ['Detail Faktur', 'Kunci Plafon'],
            ],
            [
                'name'              => 'Royal Ambarrukmo Hotel',
                'client_id'         => 'B2B-RAH-042',
                'locked'            => false,
                'sj_count'          => 3,
                'sj_label'          => 'SJ-064, SJ-071, SJ-079',
                'outstanding'       => 59550000,
                'verification'      => 'BUTUH KONFIRMASI WA',
                'verification_tone' => 'warning',
                'top_days'          => 30,
                'due_date'          => '2026-10-21',
                'limit'             => 200000000,
                'exposure'          => 120000000,
                'actions'           => ['Kirim Reminder WA', 'Kunci Plafon'],
            ],
            [
                'name'              => 'PT Boga Rasa Kulina Prima',
                'client_id'         => 'B2B-BRK-108',
                'locked'            => false,
                'sj_count'          => 2,
                'sj_label'          => 'SJ-082, SJ-085',
                'outstanding'       => 56750000,
                'verification'      => 'KLIRING AMAN',
                'verification_tone' => 'success',
                'top_days'          => 30,
                'due_date'          => '2026-11-08',
                'limit'             => 150000000,
                'exposure'          => 95000000,
                'actions'           => ['Detail Faktur', 'Kunci Plafon'],
            ],
            [
                'name'              => 'PT Segar Makmur Ritelindo',
                'client_id'         => 'B2B-SMR-019',
                'locked'            => true,
                'sj_count'          => 5,
                'sj_label'          => 'SJ-032 s/d SJ-036',
                'outstanding'       => 18000000,
                'verification'      => 'DEFISIT PLAFON',
                'verification_tone' => 'danger',
                'top_days'          => 14,
                'due_date'          => '2026-10-12',
                'limit'             => 100000000,
                'exposure'          => 105000000,
                'actions'           => ['Restrukturisasi Tagihan', 'Lepas Freeze'],
            ],
        ];

        // Ambil klien dari DB jika ada
        $dbClients = User::where('role', 'KLIEN')->whereHas('invoices', function ($q) {
            $q->whereIn('status', ['UNPAID', 'OVERDUE']);
        })->with(['invoices' => function ($q) {
            $q->whereIn('status', ['UNPAID', 'OVERDUE'])->with('orders');
        }])->get();

        $creditLimits = [
            8 => 500000000,
            9 => 200000000,
            10 => 150000000,
            11 => 100000000,
            12 => 100000000,
            13 => 150000000,
            14 => 300000000,
        ];

        $ledgerRows = [];
        $ledgerTotal = 0;

        if ($dbClients->count() > 0) {
            foreach ($dbClients as $c) {
                $unpaid = (float) $c->invoices->sum('grand_total');
                $limit = $creditLimits[$c->id] ?? 100000000;
                $exposure = $unpaid;
                $utilisation = $limit > 0 ? round(($exposure / $limit) * 100, 1) : 0;
                $isLocked = !empty($frozenClients[$c->id]) || $utilisation > 100;

                $sjList = [];
                $earliestDue = null;
                foreach ($c->invoices as $inv) {
                    if (!$earliestDue || ($inv->due_date && $inv->due_date < $earliestDue)) {
                        $earliestDue = $inv->due_date;
                    }
                    foreach ($inv->orders as $ord) {
                        if ($ord->surat_jalan_number) {
                            $sjList[] = $ord->surat_jalan_number;
                        }
                    }
                }

                $dueDateCarbon = $earliestDue ? Carbon::parse($earliestDue) : now()->addDays(14);
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

                $code = 'B2B-' . strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $c->name), 0, 3)) . '-' . str_pad($c->id, 3, '0', STR_PAD_LEFT);

                $ledgerRows[] = [
                    'name'              => $c->name,
                    'client_id'         => 'ID-KLIEN:' . $code,
                    'client_code'       => $code,
                    'locked'            => $isLocked,
                    'sj_count'          => count($sjList) ?: 1,
                    'sj_count_label'    => (count($sjList) ?: 1) . ' Surat Jalan',
                    'sj_label'          => !empty($sjList) ? implode(', ', array_slice($sjList, 0, 3)) : 'SJ-' . str_pad($c->id, 3, '0', STR_PAD_LEFT),
                    'outstanding'       => $unpaid,
                    'outstanding_label' => 'Rp ' . number_format($unpaid, 0, ',', '.'),
                    'verification'      => $verificationVal,
                    'verification_tone' => $verificationTone,
                    'top_days'          => 30,
                    'top_label'         => 'TOP 30 HARI',
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
        }

        if (empty($ledgerRows)) {
            foreach ($defaultLedger as $def) {
                $dueDate = Carbon::parse($def['due_date']);
                $days = (int) now()->diffInDays($dueDate, false);
                $utilisation = round(($def['exposure'] / $def['limit']) * 100, 1);
                $overLimit = $utilisation > 100;

                $statusVal = 'LANCAR';
                $statusTone = 'success';
                if ($def['locked'] || ($days < 0 && abs($days) > 7)) {
                    $statusVal = 'AUTO-FREEZE PO';
                    $statusTone = 'danger';
                } elseif ($days < 0) {
                    $statusVal = 'WARNING TEMPO';
                    $statusTone = 'warning';
                }

                $ledgerRows[] = [
                    'name'              => $def['name'],
                    'client_id'         => 'ID-KLIEN:' . $def['client_id'],
                    'client_code'       => $def['client_id'],
                    'locked'            => $def['locked'],
                    'sj_count'          => $def['sj_count'],
                    'sj_count_label'    => $def['sj_count'] . ' Surat Jalan',
                    'sj_label'          => $def['sj_label'],
                    'outstanding'       => $def['outstanding'],
                    'outstanding_label' => 'Rp ' . number_format($def['outstanding'], 0, ',', '.'),
                    'verification'      => $def['verification'],
                    'verification_tone' => $def['verification_tone'],
                    'top_days'          => $def['top_days'],
                    'top_label'         => 'TOP ' . $def['top_days'] . ' HARI',
                    'due_date'          => $dueDate,
                    'due_label'         => $dueDate->translatedFormat('d M Y'),
                    'days_remaining'    => $days,
                    'days_label'        => $days < 0 ? '(! OVERDUE ' . abs($days) . ' HARI)' : '(' . $days . ' Hari Tersisa)',
                    'status_label'      => $statusVal,
                    'status_tone'       => $statusTone,
                    'limit'             => $def['limit'],
                    'exposure'          => $def['exposure'],
                    'limit_label'       => 'Rp ' . round($def['exposure'] / 1000000) . 'M / ' . round($def['limit'] / 1000000) . 'M',
                    'utilisation'       => $utilisation,
                    'utilisation_label' => round($utilisation) . '%',
                    'utilisation_note'  => $overLimit ? round($utilisation) . '% (OVER)' : round($utilisation) . '%',
                    'utilisation_width' => min(100, $utilisation),
                    'over_limit'        => $overLimit,
                    'actions'           => $def['actions'],
                ];
                $ledgerTotal += $def['outstanding'];
            }
        }

        return view('direktur.receivables', [
            'totalReceivable' => $totalReceivable,
            'stage1Val'       => $stage1Val,
            'stage2Val'       => $stage2Val,
            'stage3Val'       => $stage3Val,
            'stage4Val'       => $stage4Val,
            'ledgerRows'      => $ledgerRows,
            'ledgerTotal'     => $ledgerTotal,
            'activeContracts' => 18,
        ]);
    }

    /**
     * Kirim Pengingat / Reminder Tagihan via WhatsApp (Simulasi Direksi)
     */
    public function sendReminder(Request $request, int $id)
    {
        $client = User::find($id);
        $name = $client ? $client->name : 'Klien B2B #' . $id;
        $phone = $client?->phone ?: '0812-xxxx-xxxx';

        return back()->with('success', "Surat Pengingat Jatuh Tempo (Notice Tagihan) berhasil diteruskan ke kontak PIC {$name} ({$phone}).");
    }

    /**
     * Kunci / Bekukan Plafon Kredit Klien (Auto-Freeze PO)
     */
    public function toggleFreeze(Request $request, int $id)
    {
        $client = User::find($id);
        $name = $client ? $client->name : 'Klien B2B #' . $id;

        $frozenClients = session('frozen_clients', [11 => true]);

        if (!empty($frozenClients[$id])) {
            unset($frozenClients[$id]);
            $msg = "Status pembekuan PO untuk {$name} berhasil dicabut (Dispensasi Direktur).";
            $status = 'UNFREEZE';
        } else {
            $frozenClients[$id] = true;
            $msg = "Plafon kredit {$name} berhasil dikunci permanen. Penerbitan Surat Jalan baru ditolak otomatis.";
            $status = 'LOCKED';
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

        return back()->with('success', $msg);
    }

    /**
     * Restrukturisasi Tagihan / Perpanjangan Tenor oleh Direktur
     */
    public function restructure(Request $request, int $id)
    {
        $client = User::find($id);
        $name = $client ? $client->name : 'Klien B2B #' . $id;
        $extendDays = (int) $request->input('extend_days', 14);

        if ($client) {
            $invoices = Invoice::where('user_id', $id)->whereIn('status', ['UNPAID', 'OVERDUE'])->get();
            foreach ($invoices as $inv) {
                $inv->update([
                    'due_date' => now()->addDays($extendDays)->toDateString(),
                    'status'   => 'UNPAID',
                ]);
            }
        }

        $frozenClients = session('frozen_clients', []);
        unset($frozenClients[$id]);
        session(['frozen_clients' => $frozenClients]);

        $msg = "Restrukturisasi tagihan {$name} berhasil disahkan. Tenor TOP diperpanjang +{$extendDays} hari dan lockdown PO dicabut.";

        $this->governanceService->addAuditLog(
            'AR_RESTRUCTURE_AUTHORIZED',
            'CLI-ID-' . $id,
            $msg,
            auth()->user()?->name ?: 'Direktur Utama',
            'DIR-01 [CLEARANCE L4]',
            'SEALED'
        );

        return back()->with('success', $msg);
    }
}
