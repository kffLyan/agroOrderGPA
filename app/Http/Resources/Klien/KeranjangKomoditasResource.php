<?php

namespace App\Http\Resources\Klien;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

// Resource untuk memetakan spesifikasi komoditas sayur, ketersediaan stok riil, dan harga kontrak efektif untuk interaktivitas keranjang belanja
class KeranjangKomoditasResource extends JsonResource
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

        $hasContract = $activeContracts->has($product->id);
        $contract = $activeContracts->get($product->id);
        $effectivePrice = $hasContract ? (float) $contract->fixed_price_per_kg : (float) $product->base_price;
        $stock = $stockMap[$product->id] ?? 0;
        $minimumOrder = (float) ($product->minimum_order ?? 10);

        return [
            'key'        => (string) $product->id,
            'id'         => $product->id,
            'sku'        => $product->sku ?? ('GPA-' . str_pad((string) $product->id, 3, '0', STR_PAD_LEFT)),
            'name'       => $product->name,
            'image_url'  => asset($product->image_path),
            'grade'      => $product->grade ?: 'Grade A',
            'packaging'  => "Krat Plastik Higienis 10 {$product->unit}",
            'origin'     => 'Lembang',
            'cold_chain' => '2-6°C',
            'price'      => $effectivePrice,
            'stock'      => $stock,
            'moq'        => $minimumOrder,
            'step'       => 1,
            'crate_kg'   => 10,
            'pack_label' => 'Krat',
        ];
    }
}

