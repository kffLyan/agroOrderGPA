<?php

namespace App\Http\Resources\Direktur;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VolumeKomoditasResource extends JsonResource
{
    // Konstruktor resource volume komoditas dengan parameter indeks urutan dan total panen
    public function __construct(
        $resource,
        protected int $index = 0,
        protected float $totalHarvestAll = 1.0
    ) {
        parent::__construct($resource);
    }

    // Mengubah entitas komoditas menjadi baris rekapitulasi volume pasokan panen dan neraca tonase untuk Direktur
    public function toArray(Request $request): array
    {
        $product = $this->resource;

        // Ambil data batch panen produk
        $batches = $product->relationLoaded('harvestBatches') ? $product->harvestBatches : $product->harvestBatches()->get();

        // Hitung total pasokan dari kebun binaan dan buffer mitra luar
        $internalFarmerHarvest = (float) $batches->where('source_type', 'PETANI_BINAAN')->sum('initial_quantity');
        $externalBufferHarvest = (float) $batches->where('source_type', 'BUFFER_LUAR')->sum('initial_quantity');
        $totalHarvest          = $internalFarmerHarvest + $externalBufferHarvest;
        $availableStock        = (float) $batches->sum('available_quantity');

        // Konversi kuantitas kg ke satuan ton
        $farmerTonnage    = round($internalFarmerHarvest / 1000, 1);
        $bufferTonnage    = round($externalBufferHarvest / 1000, 1);
        $totalTonnage     = round($totalHarvest / 1000, 1);
        $availableTonnage = round($availableStock / 1000, 1);

        // Hitung pangsa volume dan status ketersediaan komoditas
        $volumeSharePercent = $this->totalHarvestAll > 1 ? round(($totalHarvest / $this->totalHarvestAll) * 100) : 0;
        $supplyStatus       = $availableStock > 500 ? 'PASOKAN STABIL' : ($availableStock > 0 ? 'KUOTA TERBATAS' : 'STOK HABIS');
        $statusTone         = $availableStock > 500 ? 'success' : ($availableStock > 0 ? 'caution' : 'danger');

        return [
            'code'           => $product->sku ?: ('KMD-' . str_pad((string) ($this->index + 1), 2, '0', STR_PAD_LEFT)),
            'name'           => $product->name,
            'grade'          => $product->grade ?: 'Grade A Standard',
            'share'          => $volumeSharePercent,
            'value'          => $totalTonnage,
            'farmer'         => $farmerTonnage,
            'buffer'         => $bufferTonnage,
            'headroom'       => $availableTonnage,
            'status'         => $supplyStatus,
            'status_tone'    => $statusTone,
            'headroom_tone'  => $availableStock > 100 ? 'shell' : 'danger',
            'value_label'    => number_format($totalTonnage, 1, '.', ''),
            'farmer_label'   => number_format($farmerTonnage, 1, '.', '') . ' T',
            'buffer_label'   => number_format($bufferTonnage, 1, '.', '') . ' T',
            'headroom_label' => number_format($availableTonnage, 1, '.', '') . ' T',
            'share_label'    => $volumeSharePercent . '% VOL',
            'share_tone'     => $statusTone === 'caution' ? 'caution' : 'success',
        ];
    }
}
