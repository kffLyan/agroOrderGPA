<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\HarvestBatch;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Menampilkan konsol pemantauan stok gudang & ketersediaan sayur (ATP).
     */
    public function index(Request $request): View
    {
        // Ambil data produk riil
        $products = Product::with(['harvestBatches', 'orderItems.order'])
            ->where('is_active', true)
            ->get();

        if ($products->isEmpty()) {
            $products = Product::with(['harvestBatches', 'orderItems.order'])->get();
        }

        $totalStock = 0;
        $totalLocked = 0;
        $totalAtp = 0;
        $totalBuffer = 0;
        $rows = [];

        foreach ($products as $prod) {
            // Pasokan riil dari kebun binaan (PETANI_BINAAN)
            $binawanQty = (float) $prod->harvestBatches
                ->filter(fn ($b) => $b->source_type !== 'BUFFER_LUAR')
                ->sum('available_quantity');

            // Pasokan riil cadangan mitra luar (BUFFER_LUAR)
            $bufferQty = (float) $prod->harvestBatches
                ->filter(fn ($b) => $b->source_type === 'BUFFER_LUAR')
                ->sum('available_quantity');

            $totalProdStock = $binawanQty + $bufferQty;

            // Stok terkunci murni dari pesanan aktif klien yang belum selesai/batal
            $lockedQty = (float) $prod->orderItems
                ->filter(fn ($item) => $item->order && in_array($item->order->status, ['MENUNGGU_VERIFIKASI', 'TERVERIFIKASI', 'SIAP_KIRIM']))
                ->sum('ordered_qty');

            // Available to Promise (ATP)
            $atpQty = max(0, $totalProdStock - $lockedQty);

            $totalStock += $totalProdStock;
            $totalLocked += $lockedQty;
            $totalAtp += $atpQty;
            $totalBuffer += $bufferQty;

            $binawanPercent = $totalProdStock > 0 ? round(($binawanQty / $totalProdStock) * 100, 1) : 0;
            $atpPercent = $totalProdStock > 0 ? round(($atpQty / $totalProdStock) * 100, 1) : 0;
            $isCritical = $totalProdStock > 0 ? ($atpPercent < 20) : ($atpQty <= 0);

            // Nama mitra supplier riil dari batch panen
            $bufferBatch = $prod->harvestBatches->firstWhere('source_type', 'BUFFER_LUAR');
            $partnerName = $bufferBatch?->supplier_name
                ?: ($prod->harvestBatches->first()?->supplier_name ?: ($bufferQty > 0 ? 'Mitra Petani' : 'Kebun Binaan'));

            // Sumber pasokan riil
            $sources = [];
            if ($binawanQty > 0 || $prod->harvestBatches->where('source_type', 'PETANI_BINAAN')->isNotEmpty()) {
                $sources[] = 'Kebun Binaan';
            }
            if ($bufferQty > 0 || $prod->harvestBatches->where('source_type', 'BUFFER_LUAR')->isNotEmpty()) {
                $sources[] = 'Mitra Petani';
            }
            if (empty($sources)) {
                $sources[] = 'Kebun Binaan';
            }

            $keywords = array_values(array_filter([
                $prod->name,
                $prod->sku,
                $prod->grade,
                $partnerName,
            ]));

            $statusLabel = 'Stok Aman';
            $statusClass = 'bg-accent text-success-ink outline outline-success-deep';

            if ($totalProdStock <= 0) {
                $statusLabel = 'Stok Kosong';
                $statusClass = 'bg-danger-soft text-danger outline outline-danger/30';
            } elseif ($isCritical) {
                $statusLabel = 'Perlu Tambahan';
                $statusClass = 'bg-warning-soft text-warning-ink outline outline-warning/30';
            }

            $atpNote = 'Siap Dipesan';
            $atpClass = 'text-success-deep font-bold';

            if ($atpQty <= 0) {
                $atpNote = 'Habis Dipesan';
                $atpClass = 'text-danger font-bold';
            } elseif ($isCritical) {
                $atpNote = 'Sisa Menipis (<20%)';
                $atpClass = 'text-warning-deep font-bold';
            }

            $rows[] = [
                'name'            => $prod->name,
                'sku'             => $prod->sku ? ('KODE: ' . $prod->sku) : '-',
                'grade'           => $prod->grade ? ('Mutu: ' . $prod->grade) : '-',
                'binawan_value'   => number_format($binawanQty, 0, ',', '.') . ' kg',
                'binawan_share'   => $binawanPercent . '% dari pasokan',
                'binawan_percent' => $binawanPercent,
                'buffer_value'    => number_format($bufferQty, 0, ',', '.') . ' kg',
                'buffer_partner'  => $partnerName,
                'buffer_class'    => $bufferQty > 0 ? 'text-ink' : 'text-ink-quiet',
                'total_value'     => number_format($totalProdStock, 0, ',', '.') . ' kg',
                'locked_value'    => number_format($lockedQty, 0, ',', '.') . ' kg',
                'locked_note'     => 'Terkunci di Pesanan',
                'atp_value'       => number_format($atpQty, 0, ',', '.') . ' kg',
                'atp_note'        => $atpNote,
                'atp_class'       => $atpClass,
                'status'          => [
                    'label' => $statusLabel,
                    'class' => $statusClass,
                ],
                'action'          => [
                    'label'  => '+ Tambah Stok',
                    'class'  => 'bg-ink text-accent',
                    'intent' => 'buffer',
                    'icon'   => 'package',
                ],
                'keywords'        => $keywords,
                'sources'         => $sources,
            ];
        }

        return view('sekretaris.inventory', [
            'rows'        => $rows,
            'totalStock'  => $totalStock,
            'totalLocked' => $totalLocked,
            'totalAtp'    => $totalAtp,
            'totalBuffer' => $totalBuffer,
        ]);
    }
}
