<?php

namespace App\Http\Resources\Sekretaris;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LaporanOperasionalResource extends JsonResource
{
    // Konstruktor resource laporan operasional dengan parameter nomor urut baris
    public function __construct(
        $resource,
        protected int $index = 0
    ) {
        parent::__construct($resource);
    }

    // Mengubah model Order menjadi baris jurnal rekapitulasi distribusi dan deviasi timbangan untuk Sekretaris
    public function toArray(Request $request): array
    {
        $order = $this->resource;

        $clientName        = $order->user?->company_name ?: ($order->user?->name ?: 'Mitra Klien');
        $deliveryAddress   = $order->delivery_address ?: 'Gudang Pembeli';
        $firstItem         = $order->orderItems->first();
        $commodityName     = $firstItem?->product?->name ?: 'Sayur Segar';
        $unitPrice         = $firstItem ? (float) $firstItem->unit_price : 0.0;

        $estimatedQuantity = (float) $order->orderItems->sum('ordered_qty');
        $netQuantity       = $estimatedQuantity;
        $totalOrderAmount  = (float) ($order->grand_total ?: ($netQuantity * $unitPrice));

        $deltaKg           = $netQuantity - $estimatedQuantity;
        $deltaPercent      = $estimatedQuantity > 0 ? round(($deltaKg / $estimatedQuantity) * 100, 2) : 0.0;

        $isPaid = $order->status === 'SELESAI' || ($order->invoice && $order->invoice->status === 'PAID');

        return [
            'no'              => str_pad((string) ($this->index + 1), 2, '0', STR_PAD_LEFT),
            'invoice'         => $order->order_number,
            'sj'              => $order->surat_jalan_number ?: ('SJ-' . date('Ym') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT)),
            'client'          => $clientName,
            'location'        => $deliveryAddress,
            'commodity'       => $commodityName,
            'estimate'        => $estimatedQuantity,
            'estimate_label'  => number_format($estimatedQuantity, 0, ',', '.') . ' kg',
            'net'             => $netQuantity,
            'net_label'       => number_format($netQuantity, 1, ',', '.') . ' kg',
            'unit_price'      => $unitPrice,
            'value'           => $totalOrderAmount,
            'value_label'     => 'Rp ' . number_format($totalOrderAmount, 0, ',', '.'),
            'deviation_value' => $deltaPercent,
            'deviation_label' => ($deltaPercent >= 0 ? '+' : '') . number_format($deltaPercent, 2, '.', '') . '%',
            'deviation_class' => $deltaPercent < -1.0 ? 'text-warning-caution' : 'text-success-deep',
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
