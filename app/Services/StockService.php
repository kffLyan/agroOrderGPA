<?php

namespace App\Services;

use App\Models\HarvestBatch;
use Exception;
use Illuminate\Support\Facades\DB;

// Layanan untuk mengelola stok panen komoditas di gudang
class StockService
{
    // Hitung total sisa stok yang tersedia untuk satu produk
    public function getAvailableStock(int $productId): float
    {
        return (float) HarvestBatch::where('product_id', $productId)
            ->where('available_quantity', '>', 0)
            ->sum('available_quantity');
    }

    // Ambil stok banyak produk sekaligus (1 query, hemat & bebas N+1)
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

    // Cek apakah stok cukup untuk memenuhi pesanan
    public function isStockSufficient(int $productId, float $requestedQty): bool
    {
        return $this->getAvailableStock($productId) >= $requestedQty;
    }

    // Potong stok pakai metode FIFO (panen terlama dipotong duluan) & dikunci agar tidak bentrok
    public function allocateStock(int $productId, float $qty): void
    {
        $batches = HarvestBatch::where('product_id', $productId)
            ->where('available_quantity', '>', 0)
            ->orderBy('batch_date', 'asc') // Urutan FIFO
            ->orderBy('id', 'asc')
            ->lockForUpdate() // Kunci baris agar tidak berebut stok di detik yang sama
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
