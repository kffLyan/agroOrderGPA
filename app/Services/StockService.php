<?php

namespace App\Services;

use App\Models\HarvestBatch;
use App\Models\Product;
use Exception;

// Layanan pengelolaan kuota stok panen komoditas di gudang dengan mekanisme First-In-First-Out (FIFO).
class StockService
{
    // Menghitung total akumulasi stok panen yang tersedia di gudang untuk satu komoditas spesifik.
    public function getAvailableStock(int $productId): float
    {
        return (float) HarvestBatch::where('product_id', $productId)
            ->where('available_quantity', '>', 0)
            ->sum('available_quantity');
    }

    // Memetakan ketersediaan stok panen untuk banyak komoditas sekaligus dalam satu kueri teragregasi.
    public function getAvailableStockMap(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $stockSummary = HarvestBatch::whereIn('product_id', $productIds)
            ->where('available_quantity', '>', 0)
            ->groupBy('product_id')
            ->selectRaw('product_id, SUM(available_quantity) as total_available')
            ->pluck('total_available', 'product_id')
            ->toArray();

        $stockMap = [];
        foreach ($productIds as $productId) {
            $stockMap[$productId] = (float) ($stockSummary[$productId] ?? 0.0);
        }

        return $stockMap;
    }

    // Memvalidasi apakah ketersediaan stok panen gudang mencukupi kuantitas pesanan yang diminta.
    public function isStockSufficient(int $productId, float $requestedQuantity): bool
    {
        return $this->getAvailableStock($productId) >= $requestedQuantity;
    }

    // Mengalokasikan pemotongan stok panen gudang berbasis FIFO dengan penguncian baris kueri.
    public function allocateStock(int $productId, float $quantityToDeduct): void
    {
        // Mengambil kumpulan batch panen dari tanggal terlama (FIFO) dengan penguncian pesimistik
        $harvestBatches = HarvestBatch::where('product_id', $productId)
            ->where('available_quantity', '>', 0)
            ->orderBy('batch_date', 'asc')
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->get();

        $remainingQuantityToDeduct = $quantityToDeduct;

        foreach ($harvestBatches as $batch) {
            if ($remainingQuantityToDeduct <= 0) {
                break;
            }

            $currentAvailable = (float) $batch->available_quantity;
            $deductionAmount = min($currentAvailable, $remainingQuantityToDeduct);

            // Mengurangi kuantitas batch panen aktif di database sesuai porsi alokasi terhitung
            $batch->decrement('available_quantity', $deductionAmount);
            $remainingQuantityToDeduct -= $deductionAmount;
        }

        // Memvalidasi integritas alokasi akhir dan melempar pengecualian bila fisik stok tidak mencukupi
        if ($remainingQuantityToDeduct > 0) {
            $product = Product::find($productId);
            $productName = $product?->name ?? "ID #{$productId}";
            throw new Exception("Stok fisik komoditas {$productName} di gudang tidak mencukupi untuk dialokasikan.");
        }
    }

    // Mengembalikan kuota alokasi stok komoditas ke batch panen terbaru saat pesanan dibatalkan.
    public function restoreStock(int $productId, float $quantityToRestore): void
    {
        $activeBatch = HarvestBatch::where('product_id', $productId)->latest('id')->first();
        if ($activeBatch) {
            $activeBatch->increment('available_quantity', $quantityToRestore);
        }
    }
}
