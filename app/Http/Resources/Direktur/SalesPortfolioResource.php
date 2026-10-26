<?php

namespace App\Http\Resources\Direktur;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesPortfolioResource extends JsonResource
{
    /**
     * @param Product $resource
     */
    public function __construct(
        $resource,
        protected float $totalRevenueAll = 0.0
    ) {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array for executive sales monitoring.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Product $prod */
        $prod = $this->resource;

        // Ambil akumulasi kuantitas dan pendapatan item pesanan produk ini
        $items = $prod->relationLoaded('orderItems')
            ? $prod->orderItems
            : OrderItem::where('product_id', $prod->id)->get();

        $vol = (float) $items->sum('ordered_qty');
        $rev = (float) $items->sum('subtotal');
        $costPerKg = (float) ($prod->base_price * 0.75); // Standar COGS margin Koperasi 25%
        $cost = $vol * $costPerKg;
        $gross = max(0, $rev - $cost);
        $price = $vol > 0 ? round($rev / $vol) : (float) $prod->base_price;
        $listPrice = (float) $prod->base_price;
        $margin = $rev > 0 ? round(($gross / $rev) * 100, 1) : 25.0;

        $statusTone = match (true) {
            $margin >= 23.0 => 'success',
            $margin >= 19.5 => 'ink',
            $margin >= 17.5 => 'deep',
            default         => 'caution',
        };

        $statusLabel = match (true) {
            $margin >= 23.0 => 'PRIMA',
            $margin >= 19.5 => 'NOMINAL',
            $margin >= 17.5 => 'STABIL',
            default         => 'TIGHT',
        };

        $share = $this->totalRevenueAll > 0 ? round(($rev / $this->totalRevenueAll) * 100, 1) : 0.0;

        return [
            'name'             => $prod->name,
            'sku'              => $prod->sku ?: ('KMD-' . str_pad((string) $prod->id, 3, '0', STR_PAD_LEFT)),
            'volume'           => $vol,
            'volume_label'     => number_format($vol, 0, ',', '.'),
            'list_price_label' => 'Rp ' . number_format($listPrice, 0, ',', '.'),
            'price_label'      => 'Rp ' . number_format($price, 0, ',', '.'),
            'revenue_label'    => 'Rp ' . number_format($rev, 0, ',', '.'),
            'gross_label'      => 'Rp ' . number_format($gross, 0, ',', '.'),
            'margin'           => $margin,
            'margin_label'     => $margin . '%',
            'share'            => $share,
            'share_label'      => $share . '%',
            'status'           => $statusLabel,
            'status_tone'      => $statusTone,
            'bar_percent'      => round(min($margin, 30) / 30 * 100, 1),
            '_rev'             => $rev,
            '_gross'           => $gross,
            '_vol'             => $vol,
        ];
    }
}
