<?php

namespace App\Http\Resources\Sekretaris;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StokDashboardResource extends JsonResource
{
    // Mengubah model Product menjadi data kartu kesiapan stok buffer komoditas pada dashboard Sekretaris
    public function toArray(Request $request): array
    {
        $product = $this->resource;

        // Ambil riwayat batch panen untuk komoditas ini
        $batches = $product->relationLoaded('harvestBatches') ? $product->harvestBatches : $product->harvestBatches()->get();

        // Hitung total stok fisik yang tersedia
        $availableStock = (float) $batches
            ->where('available_quantity', '>', 0)
            ->sum('available_quantity');

        $maxCapacity = 3000.0;
        $capacityPercent = min(100, (int) round(($availableStock / $maxCapacity) * 100));
        $isStockCritical = $availableStock < 500;
        $supplierName = $batches->sortByDesc('batch_date')->first()?->supplier_name ?? 'Petani Mitra GPA';

        return [
            'name'       => $product->name,
            'alert'      => $isStockCritical,
            'card_class' => $isStockCritical ? 'border-danger/30 bg-danger-wash/10' : 'border-line-soft bg-surface-shell',
            'chip'       => [
                'label' => $isStockCritical ? 'KRITIS (< 500 kg)' : 'AMAN (' . number_format($availableStock, 0, ',', '.') . ' kg)',
                'class' => $isStockCritical ? 'bg-danger-wash text-danger font-bold' : 'bg-accent/40 text-success-deep font-bold',
            ],
            'percent'    => max(5, $capacityPercent),
            'fill_class' => $isStockCritical ? 'bg-danger' : 'bg-success-deep',
            'foot'       => [
                [
                    'label' => 'Pasokan: ' . $supplierName,
                    'class' => 'text-ink-body',
                ],
                [
                    'label' => 'Kapasitas: ' . $capacityPercent . '%',
                    'class' => $isStockCritical ? 'font-bold text-danger' : 'font-bold text-success-deep',
                ],
            ],
        ];
    }
}
