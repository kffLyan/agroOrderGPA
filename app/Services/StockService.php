<?php

namespace App\Services;

use App\Models\HarvestBatch;
use App\Models\Product;
use Exception;

// Layanan pengelolaan kuota total stok komoditas di gudang konsolidasi.
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
    public function isStockSufficient(int $productId, float $neededQty): bool
    {
        return $this->getAvailableStock($productId) >= $neededQty;
    }

    // Mengalokasikan pemotongan total stok komoditas di gudang saat pesanan disetujui.
    public function allocateStock(int $productId, float $neededQty): void
    {
        // Mengambil data stok pasokan yang tersedia di gudang dengan penguncian pesimistik
        $batches = HarvestBatch::where('product_id', $productId)
            ->where('available_quantity', '>', 0)
            ->orderBy('batch_date', 'asc')
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->get();

        $remainingQty = $neededQty;

        foreach ($batches as $batch) {
            if ($remainingQty <= 0) {
                break;
            }

            $available = (float) $batch->available_quantity;
            $deductQty = min($available, $remainingQty);

            // Mengurangi kuantitas batch panen aktif di database sesuai porsi alokasi terhitung
            $batch->decrement('available_quantity', $deductQty);
            $remainingQty -= $deductQty;
        }

        // Memvalidasi integritas alokasi akhir dan melempar pengecualian bila fisik stok tidak mencukupi
        if ($remainingQty > 0) {
            $product = Product::find($productId);
            $productName = $product?->name ?? "ID #{$productId}";
            throw new Exception("Stok fisik komoditas {$productName} di gudang tidak mencukupi untuk dialokasikan.");
        }
    }

    // Mengembalikan kuota alokasi stok komoditas ke batch panen saat pesanan dibatalkan.
    public function restoreStock(int $productId, float $qty): void
    {
        $batch = HarvestBatch::where('product_id', $productId)->latest('id')->first();
        if ($batch) {
            $batch->increment('available_quantity', $qty);
        } else {
            HarvestBatch::create([
                'product_id'         => $productId,
                'source_type'        => 'PETANI_BINAAN',
                'supplier_name'      => 'Pengembalian Batal Pesanan',
                'batch_date'         => now()->toDateString(),
                'initial_quantity'   => $qty,
                'available_quantity' => $qty,
                'grade'              => 'A',
                'qc_notes'           => 'Stok otomatis dipulihkan dari pembatalan pesanan.',
            ]);
        }
    }
}
