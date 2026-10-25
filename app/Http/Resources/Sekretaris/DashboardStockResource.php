<?php

namespace App\Http\Resources\Sekretaris;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardStockResource extends JsonResource
{
    /**
     * Transform the product resource into a buffer stock readiness card row.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Product $p */
        $p = $this->resource;

        $batches = $p->relationLoaded('harvestBatches') ? $p->harvestBatches : $p->harvestBatches()->get();

        $currentStock = (float) $batches
            ->where('available_quantity', '>', 0)
            ->sum('available_quantity');

        $maxCapacity = 3000.0;
        $percent = min(100, (int) round(($currentStock / $maxCapacity) * 100));
        $isLow = $currentStock < 500;
        $supplierName = $batches->sortByDesc('batch_date')->first()?->supplier_name ?? 'Petani Mitra GPA';

        return [
            'name'       => $p->name,
            'alert'      => $isLow,
            'card_class' => $isLow ? 'border-danger/30 bg-danger-wash/10' : 'border-line-soft bg-surface-shell',
            'chip'       => [
                'label' => $isLow ? 'KRITIS (< 500 kg)' : 'AMAN (' . number_format($currentStock, 0, ',', '.') . ' kg)',
                'class' => $isLow ? 'bg-danger-wash text-danger font-bold' : 'bg-accent/40 text-success-deep font-bold',
            ],
            'percent'    => max(5, $percent),
            'fill_class' => $isLow ? 'bg-danger' : 'bg-success-deep',
            'foot'       => [
                [
                    'label' => 'Pasokan: ' . $supplierName,
                    'class' => 'text-ink-body',
                ],
                [
                    'label' => 'Kapasitas: ' . $percent . '%',
                    'class' => $isLow ? 'font-bold text-danger' : 'font-bold text-success-deep',
                ],
            ],
        ];
    }
}
