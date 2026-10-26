<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class CatalogController extends Controller
{
    public function __invoke(): View
    {
        $products = Product::where('is_active', true)->with('harvestBatches')->get();

        $commodities = $products->map(function ($product) {
            $stock = (float) $product->harvestBatches->sum('available_quantity');
            $isAvailable = $stock > 0;
            $nameLower = strtolower($product->name);

            $category = 'veg_leaf';
            $categoryLabel = 'Sayuran Daun';
            $latin = 'Lactuca sativa L.';

            if (str_contains($nameLower, 'tomat') || str_contains($nameLower, 'beef')) {
                $category = 'veg_fruit';
                $categoryLabel = 'Sayuran Buah';
                $latin = 'Solanum lycopersicum';
            } elseif (str_contains($nameLower, 'brokoli') || str_contains($nameLower, 'kol') || str_contains($nameLower, 'kubis')) {
                $category = 'veg_flower';
                $categoryLabel = 'Sayuran Brasika / Bunga';
                $latin = 'Brassica oleracea';
            } elseif (str_contains($nameLower, 'stroberi') || str_contains($nameLower, 'strawberry') || str_contains($nameLower, 'berry')) {
                $category = 'fruit_fresh';
                $categoryLabel = 'Buah Segar';
                $latin = 'Fragaria × ananassa';
            }

            $image = 'gpaleaves.png';
            if (str_contains($nameLower, 'selada') || str_contains($nameLower, 'lettuce') || str_contains($nameLower, 'romaine')) {
                $image = 'lettuce.jpg';
            } elseif (str_contains($nameLower, 'brokoli') || str_contains($nameLower, 'broccoli')) {
                $image = 'broccoli.jpg';
            } elseif (str_contains($nameLower, 'tomat') || str_contains($nameLower, 'tomato')) {
                $image = 'tomato.jpg';
            } elseif (str_contains($nameLower, 'stroberi') || str_contains($nameLower, 'strawberry') || str_contains($nameLower, 'berry')) {
                $image = 'strawberry.jpg';
            } elseif (str_contains($nameLower, 'kol') || str_contains($nameLower, 'cabbage') || str_contains($nameLower, 'kubis')) {
                $image = 'cabbage.jpg';
            } elseif ($product->image_url && file_exists(public_path($product->image_url))) {
                $image = $product->image_url;
            }

            $moq = (float) ($product->minimum_order ?? 10);
            $step = max(1, $moq);

            return [
                'key'            => Str::slug($product->name, '_'),
                'code'           => $product->sku ?: ('KOM-' . $product->id),
                'hub_code'       => 'GPA-HUB-01',
                'sku'            => $product->sku ?: ('SKU-' . $product->id),
                'grade'          => $product->grade ?: 'Grade A',
                'packaging'      => "Krat Standar {$moq} {$product->unit}",
                'crate_kg'       => (int) $moq,
                'pack_label'     => 'Krat / Kemasan',
                'category'       => $category,
                'category_label' => $categoryLabel,
                'name'           => $product->name,
                'image_path'     => $image,
                'subtitle'       => "{$product->name} · Franco Gudang Ciwidey",
                'botanical'      => $product->name,
                'latin'          => $latin,
                'origin'         => 'Gudang Konsolidasi Ciwidey & Lembang',
                'franco'         => 'Franco Gudang Ciwidey',
                'badges'         => [$product->grade ?: 'Grade A', 'Horeca Standard'],
                'available'      => $isAvailable,
                'stock'          => $stock,
                'stock_label'    => number_format($stock, 0, ',', '.') . ' ' . $product->unit,
                'stock_note'     => ($stock < 500 && $isAvailable) ? 'Terbatas' : null,
                'stock_tone'     => $stock > 500 ? 'safe' : ($isAvailable ? 'caution' : 'muted'),
                'moq'            => $moq,
                'unit'           => $product->unit,
                'extra'          => ['label' => 'Standar Mutu', 'value' => $product->description ?: 'Bebas Residu & Timbangan Tera Sah'],
                'price'          => (float) $product->base_price,
                'price_label'    => 'Harga Satuan',
                'cold_chain'     => '4-7°C Cold Storage Room',
                'step'           => $step,
                'initial_qty'    => $step,
            ];
        })->values()->all();

        return view('public.catalog', compact('commodities'));
    }
}
