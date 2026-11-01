<?php

namespace App\Http\Resources\Direktur;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortofolioPenjualanResource extends JsonResource
{
    // Konstruktor resource portofolio penjualan dengan parameter total omzet seluruh komoditas
    public function __construct(
        $resource,
        protected float $totalRevenueAll = 0.0
    ) {
        parent::__construct($resource);
    }

    // Mengubah entitas komoditas menjadi baris metrik omzet, laba kotor, dan pangsa pasar untuk Direktur
    public function toArray(Request $request): array
    {
        $product = $this->resource;

        // Ambil data item pesanan untuk produk ini
        $orderItems = $product->relationLoaded('orderItems')
            ? $product->orderItems
            : OrderItem::where('product_id', $product->id)->get();

        // Hitung total volume, pendapatan, biaya modal, dan margin keuntungan
        $totalVolume = (float) $orderItems->sum('ordered_qty');
        $totalRevenue = (float) $orderItems->sum('subtotal');
        $cogsPerKg = (float) ($product->base_price * 0.75); // Standar estimasi COGS margin 25%
        $totalCogs = $totalVolume * $cogsPerKg;
        $grossProfit = max(0, $totalRevenue - $totalCogs);
        $averagePrice = $totalVolume > 0 ? round($totalRevenue / $totalVolume) : (float) $product->base_price;
        $catalogPrice = (float) $product->base_price;
        $marginPercent = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 25.0;

        // Tentukan warna dan label status performa margin
        $statusTone = match (true) {
            $marginPercent >= 23.0 => 'success',
            $marginPercent >= 19.5 => 'ink',
            $marginPercent >= 17.5 => 'deep',
            default                => 'caution',
        };

        $statusLabel = match (true) {
            $marginPercent >= 23.0 => 'PRIMA',
            $marginPercent >= 19.5 => 'NOMINAL',
            $marginPercent >= 17.5 => 'STABIL',
            default                => 'TIGHT',
        };

        // Hitung persentase kontribusi omzet terhadap total penjualan seluruh produk
        $marketSharePercent = $this->totalRevenueAll > 0 ? round(($totalRevenue / $this->totalRevenueAll) * 100, 1) : 0.0;

        return [
            'name'             => $product->name,
            'sku'              => $product->sku ?: ('KMD-' . str_pad((string) $product->id, 3, '0', STR_PAD_LEFT)),
            'volume'           => $totalVolume,
            'volume_label'     => number_format($totalVolume, 0, ',', '.'),
            'list_price_label' => 'Rp ' . number_format($catalogPrice, 0, ',', '.'),
            'price_label'      => 'Rp ' . number_format($averagePrice, 0, ',', '.'),
            'revenue_label'    => 'Rp ' . number_format($totalRevenue, 0, ',', '.'),
            'gross_label'      => 'Rp ' . number_format($grossProfit, 0, ',', '.'),
            'margin'           => $marginPercent,
            'margin_label'     => $marginPercent . '%',
            'share'            => $marketSharePercent,
            'share_label'      => $marketSharePercent . '%',
            'status'           => $statusLabel,
            'status_tone'      => $statusTone,
            'bar_percent'      => round(min($marginPercent, 30) / 30 * 100, 1),
            '_rev'             => $totalRevenue,
            '_gross'           => $grossProfit,
            '_vol'             => $totalVolume,
        ];
    }
}
