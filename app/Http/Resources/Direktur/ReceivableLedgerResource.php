<?php

namespace App\Http\Resources\Direktur;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceivableLedgerResource extends JsonResource
{
    /**
     * @param User $resource
     */
    public function __construct(
        $resource,
        protected array $frozenClients = []
    ) {
        parent::__construct($resource);
    }

    /**
     * Transform the B2B client entity into an aging ledger record.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $client */
        $client = $this->resource;

        $unpaidInvoices = $client->invoices->whereIn('status', ['UNPAID', 'OVERDUE']);
        $unpaid = (float) $unpaidInvoices->sum('grand_total');

        $contract = $client->contracts->firstWhere('status', 'ACTIVE');
        $limit = (float) ($contract?->committed_volume_per_cycle
            ? ($contract->committed_volume_per_cycle * 25000)
            : 100000000);
        $topDays = $contract ? (int) $contract->top_days : 30;
        $exposure = $unpaid;
        $utilisation = $limit > 0 ? round(($exposure / $limit) * 100, 1) : 0;
        $isLocked = !empty($this->frozenClients[$client->id]) || $utilisation > 100;

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

        $cleanName = preg_replace('/[^A-Za-z]/', '', (string) $client->name) ?: 'KLI';
        $code = 'B2B-' . strtoupper(substr($cleanName, 0, 3)) . '-' . str_pad((string) $client->id, 3, '0', STR_PAD_LEFT);

        return [
            'id'                => $client->id,
            'name'              => $client->company_name ?: $client->name,
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
    }
}
