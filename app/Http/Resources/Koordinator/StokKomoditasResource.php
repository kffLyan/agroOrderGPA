<?php

namespace App\Http\Resources\Koordinator;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class StokKomoditasResource extends JsonResource
{
    // Mengubah entitas produk menjadi format kartu pemantauan stok fisik dan alokasi PO untuk Koordinator
    public function toArray(Request $request): array
    {
        $product           = $this->resource;
        $productSlug       = $product->slug ?: Str::slug($product->name);
        $lowerProductName  = strtolower($product->name);

        // Tentukan kategori komoditas berdasarkan nama produk
        $category = 'daun';
        if (str_contains($lowerProductName, 'tomat') || str_contains($lowerProductName, 'stroberi') || str_contains($lowerProductName, 'cabai')) {
            $category = 'buah';
        } elseif (str_contains($lowerProductName, 'brokoli') || str_contains($lowerProductName, 'kol') || str_contains($lowerProductName, 'kubis')) {
            $category = 'cruciferae';
        }

        // Hitung stok tersedia dari petani binaan dan buffer mitra luar
        $internalFarmerStock = (float) $product->harvestBatches->where('source_type', 'PETANI_BINAAN')->sum('available_quantity');
        $externalBufferStock = (float) $product->harvestBatches->where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
        $totalAvailableStock = $internalFarmerStock + $externalBufferStock;

        // Ambil kuantitas PO dan hitung stok bebas siap kirim (Ready to Promise)
        $reservedPoQuantity  = (float) ($product->reserved_po_qty ?? 0);
        $readyToPromiseStock = $totalAvailableStock - $reservedPoQuantity;
        $utilizationPercent  = $totalAvailableStock > 0 ? round(($reservedPoQuantity / $totalAvailableStock) * 100, 1) : 0.0;
        $bufferRequirement   = $readyToPromiseStock < 0 ? abs($readyToPromiseStock) : 0.0;
        $isDeficit           = $readyToPromiseStock < 0;

        // Ambil nama pemasok dari batch panen
        $internalSupplierName = $product->harvestBatches->where('source_type', 'PETANI_BINAAN')->first()?->supplier_name ?: 'Poktan Binaan';
        $bufferSupplierName   = $product->harvestBatches->where('source_type', 'BUFFER_LUAR')->first()?->supplier_name ?: 'Mitra Buffer';

        return [
            'key'                     => $productSlug,
            'name'                    => $product->name,
            'grade'                   => strtoupper($product->grade ?: 'GRADE A'),
            'grade_tone'              => 'accent',
            'code'                    => 'CMD-' . strtoupper(substr(str_replace('-', '', $productSlug), 0, 3)) . '-' . str_pad((string) $product->id, 3, '0', STR_PAD_LEFT),
            'hub'                     => 'SENTRA CIWIDEY & LEMBANG',
            'icon'                    => str_contains($lowerProductName, 'selada') ? 'leaf' : 'package',
            'icon_tone'               => $isDeficit ? 'danger' : 'success',
            'total'                   => $totalAvailableStock,
            'rtp'                     => $readyToPromiseStock,
            'rtp_tone'                => $isDeficit ? 'danger' : 'success',
            'rtp_note'                => $isDeficit ? '(DEFISIT)' : ($readyToPromiseStock < 100 && $totalAvailableStock > 0 ? '(KETAT)' : null),
            'category'                => $category,
            'tiles'                   => [
                ['label' => 'PASOKAN BINAAN INTI', 'value' => $internalFarmerStock, 'unit' => 'KG', 'note' => $internalSupplierName, 'tone' => 'ink', 'note_tone' => 'success'],
                ['label' => 'BUFFER MITRA SIAGA', 'value' => $externalBufferStock, 'unit' => 'KG', 'note' => $bufferSupplierName, 'tone' => 'ink', 'note_tone' => 'muted'],
                ['label' => 'ALOKASI TERIKAT PO', 'value' => $reservedPoQuantity, 'unit' => 'KG', 'note' => 'PO Aktif Terverifikasi', 'tone' => 'danger', 'note_tone' => 'ink'],
                ['label' => 'KONDISI FISIK / MUTU', 'value' => 'SUHU 4°C | COLD CHAIN', 'unit' => null, 'note' => 'Toleransi Susut: 1.0%', 'tone' => 'success', 'note_tone' => 'ink'],
            ],
            'utilization'             => min(100, $utilizationPercent),
            'utilization_note'        => $utilizationPercent . '% Terjadwal',
            'utilization_status'      => $isDeficit ? 'Perlu Buffer +' . number_format($bufferRequirement, 0) . 'kg' : ($utilizationPercent > 80 ? 'Ketat' : 'Aman'),
            'utilization_status_tone' => $isDeficit ? 'warning' : 'ink',
            'utilization_tone'        => $isDeficit ? 'brand' : 'success',
            'buffer_need'             => $bufferRequirement,
            'injection'               => $isDeficit,
        ];
    }
}
