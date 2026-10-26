<?php

namespace App\Http\Resources\Direktur;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommodityVolumeResource extends JsonResource
{
    /**
     * @param Product $resource
     */
    public function __construct(
        $resource,
        protected int $index = 0,
        protected float $totalHarvestAll = 1.0
    ) {
        parent::__construct($resource);
    }

    /**
     * Transform the commodity product entity into a volume balance row.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Product $p */
        $p = $this->resource;

        $batches = $p->relationLoaded('harvestBatches') ? $p->harvestBatches : $p->harvestBatches()->get();

        $prodBinaan = (float) $batches->where('source_type', 'PETANI_BINAAN')->sum('initial_quantity');
        $prodBuffer = (float) $batches->where('source_type', 'BUFFER_LUAR')->sum('initial_quantity');
        $prodTotal  = $prodBinaan + $prodBuffer;
        $prodAvail  = (float) $batches->sum('available_quantity');

        $farmerTon   = round($prodBinaan / 1000, 1);
        $bufferTon   = round($prodBuffer / 1000, 1);
        $valueTon    = round($prodTotal / 1000, 1);
        $headroomTon = round($prodAvail / 1000, 1);

        $sharePercent = $this->totalHarvestAll > 1 ? round(($prodTotal / $this->totalHarvestAll) * 100) : 0;
        $status = $prodAvail > 500 ? 'PASOKAN STABIL' : ($prodAvail > 0 ? 'KUOTA TERBATAS' : 'STOK HABIS');
        $statusTone = $prodAvail > 500 ? 'success' : ($prodAvail > 0 ? 'caution' : 'danger');

        return [
            'code'           => $p->sku ?: ('KMD-' . str_pad((string) ($this->index + 1), 2, '0', STR_PAD_LEFT)),
            'name'           => $p->name,
            'grade'          => $p->grade ?: 'Grade A Standard',
            'share'          => $sharePercent,
            'value'          => $valueTon,
            'farmer'         => $farmerTon,
            'buffer'         => $bufferTon,
            'headroom'       => $headroomTon,
            'status'         => $status,
            'status_tone'    => $statusTone,
            'headroom_tone'  => $prodAvail > 100 ? 'shell' : 'danger',
            'value_label'    => number_format($valueTon, 1, '.', ''),
            'farmer_label'   => number_format($farmerTon, 1, '.', '') . ' T',
            'buffer_label'   => number_format($bufferTon, 1, '.', '') . ' T',
            'headroom_label' => number_format($headroomTon, 1, '.', '') . ' T',
            'share_label'    => $sharePercent . '% VOL',
            'share_tone'     => $statusTone === 'caution' ? 'caution' : 'success',
        ];
    }
}
