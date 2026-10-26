<?php

namespace App\Http\Resources\Koordinator;

use App\Models\HarvestBatch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockLogResource extends JsonResource
{
    /**
     * Transform harvest batch log item for Coordinator Stock console.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var HarvestBatch $batch */
        $batch       = $this->resource;
        $isBuffer    = $batch->source_type === 'BUFFER_LUAR';
        $productName = $batch->product?->name ?? 'Komoditas Sayur';
        $batchDate   = $batch->batch_date ? $batch->batch_date->format('Ymd') : now()->format('Ymd');

        return [
            'lot'   => 'LOT-' . strtoupper(substr(str_replace(' ', '', $productName), 0, 3)) . '-' . $batchDate . '-' . str_pad((string) $batch->id, 2, '0', STR_PAD_LEFT),
            'time'  => $batch->created_at ? $batch->created_at->format('H:i:s') . ' WIB' : now()->format('H:i:s') . ' WIB',
            'entry' => '+' . number_format((float) $batch->initial_quantity, 0, ',', '.') . ' KG ' . $productName . ' (' . ($isBuffer ? 'Buffer Mitra' : 'Binaan') . ' - ' . $batch->supplier_name . ')',
            'note'  => 'QC Pass: Mutu Terverifikasi',
            'audit' => 'AUDIT: SEC-OP-' . str_pad((string) $batch->inputted_by, 3, '0', STR_PAD_LEFT),
            'tone'  => $isBuffer ? 'brand' : 'success',
        ];
    }
}
