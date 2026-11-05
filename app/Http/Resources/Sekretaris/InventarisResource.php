<?php

namespace App\Http\Resources\Sekretaris;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventarisResource extends JsonResource
{
    // Mengubah entitas Product menjadi data baris neraca inventaris fisik, alokasi FIFO, dan ketersediaan ATP untuk Sekretaris
    public function toArray(Request $request): array
    {
        $product = $this->resource;

        // Ambil pasokan dari kebun binaan dan buffer mitra luar
        $internalFarmerStock = (float) $product->harvestBatches
            ->filter(fn ($batch) => $batch->source_type !== 'BUFFER_LUAR')
            ->sum('available_quantity');

        $externalBufferStock = (float) $product->harvestBatches
            ->filter(fn ($batch) => $batch->source_type === 'BUFFER_LUAR')
            ->sum('available_quantity');

        $totalPhysicalStock = $internalFarmerStock + $externalBufferStock;

        // Hitung stok terkunci pada antrean pesanan yang belum diverifikasi
        $lockedQueueStock = (float) $product->orderItems
            ->filter(fn ($item) => $item->order && $item->order->status === 'MENUNGGU_VERIFIKASI')
            ->sum('ordered_qty');

        // Hitung stok bebas siap alokasi (Available to Promise)
        $availableToPromiseStock = max(0, $totalPhysicalStock - $lockedQueueStock);

        $internalFarmerPercent = $totalPhysicalStock > 0 ? round(($internalFarmerStock / $totalPhysicalStock) * 100, 1) : 0;
        $availableStockPercent = $totalPhysicalStock > 0 ? round(($availableToPromiseStock / $totalPhysicalStock) * 100, 1) : 0;
        $isStockCritical = $totalPhysicalStock > 0 ? ($availableStockPercent < 20) : ($availableToPromiseStock <= 0);

        // Ambil nama mitra pemasok dari batch panen
        $bufferBatch = $product->harvestBatches->firstWhere('source_type', 'BUFFER_LUAR');
        $partnerSupplierName = $bufferBatch?->supplier_name
            ?: ($product->harvestBatches->first()?->supplier_name ?: ($externalBufferStock > 0 ? 'Mitra Petani' : 'Kebun Binaan'));

        // Identifikasi daftar sumber pasokan
        $sourceList = [];
        if ($internalFarmerStock > 0 || $product->harvestBatches->where('source_type', 'PETANI_BINAAN')->isNotEmpty()) {
            $sourceList[] = 'Kebun Binaan';
        }
        if ($externalBufferStock > 0 || $product->harvestBatches->where('source_type', 'BUFFER_LUAR')->isNotEmpty()) {
            $sourceList[] = 'Mitra Petani';
        }
        if (empty($sourceList)) {
            $sourceList[] = 'Kebun Binaan';
        }

        $searchKeywords = array_values(array_filter([
            $product->name,
            $product->sku,
            $product->grade,
            $partnerSupplierName,
        ]));

        $statusLabel = 'Stok Aman';
        $statusClass = 'bg-accent text-success-ink outline outline-success-deep';

        if ($totalPhysicalStock <= 0) {
            $statusLabel = 'Stok Kosong';
            $statusClass = 'bg-danger-soft text-danger outline outline-danger/30';
        } elseif ($isStockCritical) {
            $statusLabel = 'Perlu Tambahan';
            $statusClass = 'bg-warning-soft text-warning-ink outline outline-warning/30';
        }

        $atpNote = 'Siap Dipesan';
        $atpClass = 'text-success-deep font-bold';

        if ($availableToPromiseStock <= 0) {
            $atpNote = 'Habis Dipesan';
            $atpClass = 'text-danger font-bold';
        } elseif ($isStockCritical) {
            $atpNote = 'Sisa Menipis (<20%)';
            $atpClass = 'text-warning-deep font-bold';
        }

        return [
            'name'            => $product->name,
            'sku'             => $product->sku ? ('KODE: ' . $product->sku) : '-',
            'grade'           => $product->grade ? ('Mutu: ' . $product->grade) : '-',
            'binawan_value'   => number_format($internalFarmerStock, 0, ',', '.') . ' kg',
            'binawan_share'   => $internalFarmerPercent . '% dari pasokan',
            'binawan_percent' => $internalFarmerPercent,
            'buffer_value'    => number_format($externalBufferStock, 0, ',', '.') . ' kg',
            'buffer_partner'  => $partnerSupplierName,
            'buffer_class'    => $externalBufferStock > 0 ? 'text-ink' : 'text-ink-quiet',
            'total_value'     => number_format($totalPhysicalStock, 0, ',', '.') . ' kg',
            'locked_value'    => number_format($lockedQueueStock, 0, ',', '.') . ' kg',
            'locked_note'     => 'Terkunci di Pesanan',
            'atp_value'       => number_format($availableToPromiseStock, 0, ',', '.') . ' kg',
            'atp_note'        => $atpNote,
            'atp_class'       => $atpClass,
            'status'          => [
                'label' => $statusLabel,
                'class' => $statusClass,
            ],
            'action'          => [
                'label'  => 'Pantau Neraca',
                'class'  => 'bg-surface-track text-ink',
                'intent' => 'view',
                'icon'   => 'eye',
            ],
            'keywords'        => $searchKeywords,
            'sources'         => $sourceList,
            // Nilai mentah untuk mempermudah kalkulasi agregat di frontend
            '_raw'            => [
                'total_stock' => $totalPhysicalStock,
                'locked'      => $lockedQueueStock,
                'atp'         => $availableToPromiseStock,
                'buffer'      => $externalBufferStock,
            ],
        ];
    }
}
