<?php

namespace App\Http\Resources\Sekretaris;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperationalReportResource extends JsonResource
{
    /**
     * @param Order $resource
     */
    public function __construct(
        $resource,
        protected int $index = 0
    ) {
        parent::__construct($resource);
    }

    /**
     * Transform the order resource into a journal row for secretary operational report.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Order $ord */
        $ord = $this->resource;

        $clientName    = $ord->user?->company_name ?: ($ord->user?->name ?: 'Mitra Klien');
        $address       = $ord->delivery_address ?: 'Gudang Pembeli';
        $firstItem     = $ord->orderItems->first();
        $commodityName = $firstItem?->product?->name ?: 'Sayur Segar';
        $unitPrice     = $firstItem ? (float) $firstItem->unit_price : 0.0;

        $est = (float) $ord->orderItems->sum('ordered_qty');
        $net = $est;
        $val = (float) ($ord->grand_total ?: ($net * $unitPrice));

        $diffKg  = $net - $est;
        $diffPct = $est > 0 ? round(($diffKg / $est) * 100, 2) : 0.0;

        $isPaid = $ord->status === 'SELESAI' || ($ord->invoice && $ord->invoice->status === 'PAID');

        return [
            'no'              => str_pad((string) ($this->index + 1), 2, '0', STR_PAD_LEFT),
            'invoice'         => $ord->order_number,
            'sj'              => $ord->surat_jalan_number ?: ('SJ-' . date('Ym') . '-' . str_pad((string) $ord->id, 4, '0', STR_PAD_LEFT)),
            'client'          => $clientName,
            'location'        => $address,
            'commodity'       => $commodityName,
            'estimate'        => $est,
            'estimate_label'  => number_format($est, 0, ',', '.') . ' kg',
            'net'             => $net,
            'net_label'       => number_format($net, 1, ',', '.') . ' kg',
            'unit_price'      => $unitPrice,
            'value'           => $val,
            'value_label'     => 'Rp ' . number_format($val, 0, ',', '.'),
            'deviation_value' => $diffPct,
            'deviation_label' => ($diffPct >= 0 ? '+' : '') . number_format($diffPct, 2, '.', '') . '%',
            'deviation_class' => $diffPct < -1.0 ? 'text-warning-caution' : 'text-success-deep',
            'blocked'         => false,
            'status'          => $isPaid ? 'lunas' : 'top30',
            'status_label'    => $isPaid ? 'Lunas' : 'Tempo 30',
            'status_class'    => $isPaid
                ? 'bg-accent text-success-deep outline outline-1 outline-success-deep'
                : 'bg-surface-pill text-ink-body outline outline-1 outline-line-board/70',
            'audit_files'     => 3,
        ];
    }
}
