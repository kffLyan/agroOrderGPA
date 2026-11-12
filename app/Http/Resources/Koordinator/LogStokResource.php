<?php

namespace App\Http\Resources\Koordinator;

use App\Models\HarvestBatch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogStokResource extends JsonResource
{
    // Mengubah riwayat batch panen masuk menjadi format entri log audit konsol stok Koordinator
    public function toArray(Request $request): array
    {
        $batch             = $this->resource;
        $isExternalBuffer  = $batch->source_type === 'BUFFER_LUAR';
        $productName       = $batch->product?->name ?? 'Komoditas Sayur';
        $batchDateString   = $batch->batch_date ? $batch->batch_date->format('Ymd') : now()->format('Ymd');

        return [
            'lot'   => 'LOT-' . strtoupper(substr(str_replace(' ', '', $productName), 0, 3)) . '-' . $batchDateString . '-' . str_pad((string) $batch->id, 2, '0', STR_PAD_LEFT),
            'time'  => $batch->created_at ? $batch->created_at->format('H:i:s') . ' WIB' : now()->format('H:i:s') . ' WIB',
            'entry' => '+' . number_format((float) $batch->initial_quantity, 0, ',', '.') . ' KG ' . $productName . ' (' . ($isExternalBuffer ? 'Buffer Mitra' : 'Binaan') . ' - ' . $batch->supplier_name . ')',
            'note'  => 'QC Pass: Mutu Terverifikasi',
            'audit' => 'AUDIT: SEC-OP-' . str_pad((string) $batch->inputted_by, 3, '0', STR_PAD_LEFT),
            'tone'  => $isExternalBuffer ? 'brand' : 'success',
        ];
    }
}
