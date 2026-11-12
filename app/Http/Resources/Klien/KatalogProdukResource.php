<?php

namespace App\Http\Resources\Klien;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

// Resource untuk memetakan spesifikasi komoditas katalog sayur, status ketersediaan panen, dan harga kontrak klien
class KatalogProdukResource extends JsonResource
{
    protected ?Collection $activeContracts;
    protected array|Collection $stockMap;

    public function __construct($resource, $activeContracts = null, $stockMap = [])
    {
        parent::__construct($resource);
        $this->activeContracts = $activeContracts ? collect($activeContracts) : collect();
        $this->stockMap = $stockMap;
    }

    public function toArray(Request $request): array
    {
        /** @var Product $product */
        $product = $this->resource;
        $stockMap = $this->stockMap;
        $activeContracts = $this->activeContracts;

        $availableStock = $stockMap[$product->id] ?? 0;
        $hasContract = $activeContracts->has($product->id);
        $contractItem = $activeContracts->get($product->id);
        $effectivePrice = $hasContract ? (float) $contractItem->fixed_price_per_kg : (float) $product->base_price;
        $minimumOrder = (float) ($product->minimum_order ?: 1);

        return [
            'key'                => (string) $product->id,
            'id'                 => $product->id,
            'sku'                => $product->sku ?? ('GPA-' . str_pad((string) $product->id, 3, '0', STR_PAD_LEFT)),
            'code'               => $product->sku ?? ('GPA-' . str_pad((string) $product->id, 3, '0', STR_PAD_LEFT)),
            'name'               => $product->name,
            'image_path'         => $product->image_path,
            'image_url'          => asset($product->image_path),
            'subtitle'           => $product->description ?: "Komoditas hortikultura unggulan Pasundan Grade {$product->grade}",
            'category'           => 'sayur-daun',
            'category_label'     => 'Komoditas Pertanian GPA',
            'grade'              => $product->grade ?: 'Grade A',
            'packaging'          => "Krat Plastik Higienis 10 {$product->unit}",
            'price'              => $effectivePrice,
            'base_price'         => (float) $product->base_price,
            'has_contract'       => $hasContract,
            'price_note'         => $hasContract ? 'Harga Kontrak Terkunci' : 'Harga Pasar Reguler',
            'price_note_tone'    => $hasContract ? 'success' : 'default',
            'stock'              => $availableStock,
            'stock_label'        => 'Stok Bebas:',
            'availability'       => $availableStock > 0 ? 'Stok Tersedia' : 'Habis Terpesan',
            'availability_tone'  => $availableStock > 0 ? 'safe' : 'caution',
            'moq'                => $minimumOrder,
            'initial_qty'        => $minimumOrder,
            'step'               => 1,
            'crate_kg'           => 10,
            'pack_label'         => 'Keranjang',
            'cold_chain'         => '2-6°C Pre-Cooled',
            'critical'           => $availableStock < 50 && $availableStock > 0,
            'origin'             => 'Lembang',
            'specs'              => [
                [
                    'label' => 'Satuan',
                    'value' => strtoupper($product->unit ?? 'KG'),
                    'tone'  => 'default',
                ],
                [
                    'label' => 'Grade Mutu',
                    'value' => $product->grade ?: 'Grade A',
                    'tone'  => 'success',
                ],
                [
                    'label' => 'Stok Riil Panen',
                    'value' => number_format($availableStock, 0, ',', '.') . ' ' . $product->unit,
                    'tone'  => $availableStock > 0 ? 'success' : 'caution',
                ],
                [
                    'label' => 'Min Order (MOQ)',
                    'value' => number_format($minimumOrder, 0, ',', '.') . ' ' . $product->unit,
                    'tone'  => 'default',
                ],
            ],
        ];
    }
}

