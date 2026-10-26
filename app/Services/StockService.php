<?php

namespace App\Services;

use App\Models\HarvestBatch;
use Exception;
use Illuminate\Support\Facades\DB;

class StockService
{
    // Dapatkan sisa kuota stok panen tersedia untuk suatu produk.
    public function getAvailableStock(int $productId): float
    {
        return (float) HarvestBatch::where('product_id', $productId)
            ->where('available_quantity', '>', 0)
            ->sum('available_quantity');
    }

    /**
     * Dapatkan peta kuota stok panen tersedia untuk banyak produk dalam 1 query efisien (mencegah N+1).
     *
     * @param array<int> $productIds
     * @return array<int, float>
     */
    public function getAvailableStockMap(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $stocks = HarvestBatch::whereIn('product_id', $productIds)
            ->where('available_quantity', '>', 0)
            ->groupBy('product_id')
            ->selectRaw('product_id, SUM(available_quantity) as total_available')
            ->pluck('total_available', 'product_id')
            ->toArray();

        $result = [];
        foreach ($productIds as $id) {
            $result[$id] = (float) ($stocks[$id] ?? 0.0);
        }

        return $result;
    }

    // Periksa apakah kuota stok mencukupi untuk jumlah yang diminta
    public function isStockSufficient(int $productId, float $requestedQty): bool
    {
        return $this->getAvailableStock($productId) >= $requestedQty;
    }

    /**
     * Alokasikan (potong) stok panen secara FIFO dengan pessimistic lock.
     *
     * @throws Exception
     */
    public function allocateStock(int $productId, float $qty): void
    {
        $batches = HarvestBatch::where('product_id', $productId)
            ->where('available_quantity', '>', 0)
            ->orderBy('batch_date', 'asc')
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->get();

        $remainingToDeduct = $qty;

        foreach ($batches as $batch) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            $currentAvailable = (float) $batch->available_quantity;
            $deduction = min($currentAvailable, $remainingToDeduct);

            $batch->decrement('available_quantity', $deduction);
            $remainingToDeduct -= $deduction;
        }
    }
}
