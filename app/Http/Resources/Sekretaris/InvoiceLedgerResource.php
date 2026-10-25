<?php

namespace App\Http\Resources\Sekretaris;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceLedgerResource extends JsonResource
{
    /**
     * Transformasi data Invoice ke baris Buku Besar Faktur Tagihan
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $inv = $this->resource;
        $amount = (float) $inv->grand_total;
        $isPaid = $inv->status === 'PAID';
        $clientName = $inv->user?->company_name ?: ($inv->user?->name ?: 'Mitra Klien GPA');
        $dueDate = $inv->due_date ?: now()->addDays(30);
        $daysRemaining = now()->diffInDays($dueDate, false);

        if ($isPaid) {
            $statusKey = 'paid';
            $statusLabel = 'Lunas';
            $statusClass = 'bg-accent text-success-deep outline outline-1 outline-success-deep';
            $remainingText = 'Telah Lunas';
            $remainingClass = 'text-success-deep';
            $remainingDot = 'bg-success-deep';
        } else {
            if ($daysRemaining < 0) {
                $statusKey = 'blocked';
                $statusLabel = 'Jatuh Tempo';
                $statusClass = 'bg-danger-soft text-danger outline outline-1 outline-danger/60';
                $remainingText = 'Lewat ' . abs($daysRemaining) . ' Hari';
                $remainingClass = 'text-danger';
                $remainingDot = 'bg-danger';
            } elseif ($daysRemaining <= 7) {
                $statusKey = 'follow_up';
                $statusLabel = 'Perlu Dihubungi';
                $statusClass = 'bg-warning-soft/60 text-warning-deep outline outline-1 outline-warning/50';
                $remainingText = "Sisa {$daysRemaining} Hari";
                $remainingClass = 'text-warning-deep';
                $remainingDot = 'bg-warning';
            } else {
                $statusKey = 'lancar';
                $statusLabel = 'Lancar';
                $statusClass = 'bg-accent text-success-deep outline outline-1 outline-success-deep';
                $remainingText = "Sisa {$daysRemaining} Hari";
                $remainingClass = 'text-success-deep';
                $remainingDot = 'bg-success-deep';
            }
        }

        return [
            'id'              => $inv->invoice_number,
            'issued'          => $inv->created_at->format('d M Y'),
            'issued_note'     => '(Faktur)',
            'client'          => $clientName,
            'contract'        => 'KONTRAK-' . ($inv->user_id ? str_pad((string) $inv->user_id, 3, '0', STR_PAD_LEFT) : '001'),
            'contract_note'   => null,
            'contract_class'  => 'text-ink-quiet',
            'sj_count'        => max(1, $inv->orders ? $inv->orders->count() : 1),
            'total'           => $amount,
            'total_label'     => 'Rp ' . number_format($amount, 0, ',', '.'),
            'total_class'     => 'text-ink font-bold',
            'terms_days'      => 30,
            'terms_label'     => 'Tempo 30',
            'terms_class'     => 'bg-surface-pill text-ink-body outline outline-1 outline-line-board/70',
            'due'             => $dueDate->format('d M Y'),
            'due_class'       => 'text-ink',
            'remaining'       => $remainingText,
            'remaining_class' => $remainingClass,
            'remaining_dot'   => $remainingDot,
            'status_key'      => $statusKey,
            'status_label'    => $statusLabel,
            'status_class'    => $statusClass,
            'row_class'       => '',
            'highlight'       => '',
            'paid'            => $isPaid,
        ];
    }
}
