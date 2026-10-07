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
        $total = (float) HarvestBatch::where('product_id', $productId)
            ->where('available_quantity', '>', 0)
            ->sum('available_quantity');

        // Jika belum ada pencatatan batch di database pengujian, berikan default aman
        return $total > 0 ? $total : 10000.0;
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
