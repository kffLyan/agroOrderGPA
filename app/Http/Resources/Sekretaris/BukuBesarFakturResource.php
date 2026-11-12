<?php

namespace App\Http\Resources\Sekretaris;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BukuBesarFakturResource extends JsonResource
{
    // Mengubah data Invoice menjadi representasi baris Buku Besar Faktur Tagihan dan umur piutang untuk Sekretaris
    public function toArray(Request $request): array
    {
        $invoice = $this->resource;
        $grandTotal = (float) $invoice->grand_total;
        $isPaid = $invoice->status === 'PAID';
        $clientName = $invoice->user?->company_name ?: ($invoice->user?->name ?: 'Mitra Klien GPA');
        $today = now()->startOfDay();
        $dueDate = $invoice->due_date ? Carbon::parse($invoice->due_date)->startOfDay() : $today->copy()->addDays(30);
        $daysRemaining = (int) round($today->diffInDays($dueDate, false));

        // Tentukan status pembayaran dan teks sisa waktu
        if ($isPaid) {
            $statusKey = 'paid';
            $statusLabel = 'Lunas';
            $statusClass = 'bg-accent text-success-deep outline outline-1 outline-success-deep';
            $remainingText = 'Telah Lunas';
            $remainingClass = 'text-success-deep';
            $indicatorDot = 'bg-success-deep';
        } else {
            if ($daysRemaining < 0) {
                $statusKey = 'blocked';
                $statusLabel = 'Jatuh Tempo';
                $statusClass = 'bg-danger-soft text-danger outline outline-1 outline-danger/60';
                $remainingText = 'Lewat ' . abs($daysRemaining) . ' Hari';
                $remainingClass = 'text-danger';
                $indicatorDot = 'bg-danger';
            } elseif ($daysRemaining === 0) {
                $statusKey = 'follow_up';
                $statusLabel = 'Perlu Dihubungi';
                $statusClass = 'bg-warning-soft/60 text-warning-deep outline outline-1 outline-warning/50';
                $remainingText = 'Jatuh Tempo Hari Ini';
                $remainingClass = 'text-warning-deep';
                $indicatorDot = 'bg-warning';
            } elseif ($daysRemaining <= 7) {
                $statusKey = 'follow_up';
                $statusLabel = 'Perlu Dihubungi';
                $statusClass = 'bg-warning-soft/60 text-warning-deep outline outline-1 outline-warning/50';
                $remainingText = "Sisa {$daysRemaining} Hari";
                $remainingClass = 'text-warning-deep';
                $indicatorDot = 'bg-warning';
            } else {
                $statusKey = 'lancar';
                $statusLabel = 'Lancar';
                $statusClass = 'bg-accent text-success-deep outline outline-1 outline-success-deep';
                $remainingText = "Sisa {$daysRemaining} Hari";
                $remainingClass = 'text-success-deep';
                $indicatorDot = 'bg-success-deep';
            }
        }

        $activeContract = $invoice->user?->contracts ? $invoice->user->contracts->firstWhere('status', 'ACTIVE') : null;
        $isContractClient = ($invoice->user?->client_type === 'B2B_KONTRAK' || $activeContract !== null);
        $contractNumber = $activeContract?->contract_number;
        $topDays = (int) ($activeContract?->top_days ?: 30);
        if ($invoice->due_date && $invoice->created_at) {
            $diff = (int) round($invoice->created_at->diffInDays($invoice->due_date));
            if ($diff > 0) {
                $topDays = $diff;
            }
        }

        return [
            'id'              => $invoice->invoice_number,
            'issued'          => $invoice->created_at->format('d M Y'),
            'issued_note'     => '(Faktur)',
            'client'          => $clientName,
            'is_contract'     => $isContractClient,
            'contract'        => $contractNumber ?: 'Non-Kontrak (Reguler)',
            'contract_note'   => null,
            'contract_class'  => $contractNumber ? 'text-success-deep font-semibold' : 'text-ink-quiet',
            'sj_count'        => max(1, $invoice->orders ? $invoice->orders->count() : 1),
            'total'           => $grandTotal,
            'total_label'     => 'Rp ' . number_format($grandTotal, 0, ',', '.'),
            'total_class'     => 'text-ink font-bold',
            'terms_days'      => $topDays,
            'terms_label'     => 'Tempo ' . $topDays . 'D',
            'terms_class'     => 'bg-surface-pill text-ink-body outline outline-1 outline-line-board/70',
            'due'             => $dueDate->format('d M Y'),
            'due_class'       => 'text-ink',
            'remaining'       => $remainingText,
            'remaining_class' => $remainingClass,
            'remaining_dot'   => $indicatorDot,
            'status_key'      => $statusKey,
            'status_label'    => $statusLabel,
            'status_class'    => $statusClass,
            'row_class'       => '',
            'highlight'       => '',
            'paid'            => $isPaid,
        ];
    }
}
