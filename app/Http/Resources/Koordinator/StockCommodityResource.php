<?php

namespace App\Http\Resources\Koordinator;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class StockCommodityResource extends JsonResource
{
    /**
     * Transform the resource into an array for Coordinator Stock Monitor.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Product $product */
        $product   = $this->resource;
        $slug      = $product->slug ?: Str::slug($product->name);
        $lowerName = strtolower($product->name);

        $category = 'daun';
        if (str_contains($lowerName, 'tomat') || str_contains($lowerName, 'stroberi') || str_contains($lowerName, 'cabai')) {
            $category = 'buah';
        } elseif (str_contains($lowerName, 'brokoli') || str_contains($lowerName, 'kol') || str_contains($lowerName, 'kubis')) {
            $category = 'cruciferae';
        }

        $binaan = (float) $product->harvestBatches->where('source_type', 'PETANI_BINAAN')->sum('available_quantity');
        $buffer = (float) $product->harvestBatches->where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
        $total  = $binaan + $buffer;

        // Ambil kuantitas PO yang telah diagregasi via withSum() tanpa N+1 query
        $po          = (float) ($product->reserved_po_qty ?? 0);
        $rtp         = $total - $po;
        $utilization = $total > 0 ? round(($po / $total) * 100, 1) : 0.0;
        $bufferNeed  = $rtp < 0 ? abs($rtp) : 0.0;
        $isDeficit   = $rtp < 0;

        $supplierBinaan = $product->harvestBatches->where('source_type', 'PETANI_BINAAN')->first()?->supplier_name ?: 'Poktan Binaan';
        $supplierBuffer = $product->harvestBatches->where('source_type', 'BUFFER_LUAR')->first()?->supplier_name ?: 'Mitra Buffer';

        return [
            'key'                     => $slug,
            'name'                    => $product->name,
            'grade'                   => strtoupper($product->grade ?: 'GRADE A'),
            'grade_tone'              => 'accent',
            'code'                    => 'CMD-' . strtoupper(substr(str_replace('-', '', $slug), 0, 3)) . '-' . str_pad((string) $product->id, 3, '0', STR_PAD_LEFT),
            'hub'                     => 'SENTRA CIWIDEY & LEMBANG',
            'icon'                    => str_contains($lowerName, 'selada') ? 'leaf' : 'package',
            'icon_tone'               => $isDeficit ? 'danger' : 'success',
            'total'                   => $total,
            'rtp'                     => $rtp,
            'rtp_tone'                => $isDeficit ? 'danger' : 'success',
            'rtp_note'                => $isDeficit ? '(DEFISIT)' : ($rtp < 100 && $total > 0 ? '(KETAT)' : null),
            'category'                => $category,
            'tiles'                   => [
                ['label' => 'PASOKAN BINAAN INTI', 'value' => $binaan, 'unit' => 'KG', 'note' => $supplierBinaan, 'tone' => 'ink', 'note_tone' => 'success'],
                ['label' => 'BUFFER MITRA SIAGA', 'value' => $buffer, 'unit' => 'KG', 'note' => $supplierBuffer, 'tone' => 'ink', 'note_tone' => 'muted'],
                ['label' => 'ALOKASI TERIKAT PO', 'value' => $po, 'unit' => 'KG', 'note' => 'PO Aktif Terverifikasi', 'tone' => 'danger', 'note_tone' => 'ink'],
                ['label' => 'KONDISI FISIK / MUTU', 'value' => 'SUHU 4°C | COLD CHAIN', 'unit' => null, 'note' => 'Toleransi Susut: 1.0%', 'tone' => 'success', 'note_tone' => 'ink'],
            ],
            'utilization'             => min(100, $utilization),
            'utilization_note'        => $utilization . '% Terjadwal',
            'utilization_status'      => $isDeficit ? 'Perlu Buffer +' . number_format($bufferNeed, 0) . 'kg' : ($utilization > 80 ? 'Ketat' : 'Aman'),
            'utilization_status_tone' => $isDeficit ? 'warning' : 'ink',
            'utilization_tone'        => $isDeficit ? 'brand' : 'success',
            'buffer_need'             => $bufferNeed,
            'injection'               => $isDeficit,
        ];
    }
}
