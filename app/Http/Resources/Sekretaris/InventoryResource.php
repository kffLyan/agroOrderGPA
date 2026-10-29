<?php

namespace App\Http\Resources\Sekretaris;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    /**
     * Transformasi data persediaan komoditas untuk konsol monitoring inventaris
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $prod = $this->resource;

        // Pasokan riil dari kebun binaan (PETANI_BINAAN)
        $binawanQty = (float) $prod->harvestBatches
            ->filter(fn ($b) => $b->source_type !== 'BUFFER_LUAR')
            ->sum('available_quantity');

        // Pasokan riil cadangan mitra luar (BUFFER_LUAR)
        $bufferQty = (float) $prod->harvestBatches
            ->filter(fn ($b) => $b->source_type === 'BUFFER_LUAR')
            ->sum('available_quantity');

        $totalProdStock = $binawanQty + $bufferQty;

        // Stok terkunci dari antrean pesanan masuk yang belum diverifikasi Sekretaris (mencegah pengurangan ganda)
        $lockedQty = (float) $prod->orderItems
            ->filter(fn ($item) => $item->order && $item->order->status === 'MENUNGGU_VERIFIKASI')
            ->sum('ordered_qty');

        // Available to Promise (ATP): Sisa stok fisik gudang dikurangi antrean pesanan yang belum disetujui
        $atpQty = max(0, $totalProdStock - $lockedQty);

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

        return [
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
                'label'  => 'Pantau Neraca',
                'class'  => 'bg-surface-track text-ink',
                'intent' => 'view',
                'icon'   => 'eye',
            ],
            'keywords'        => $keywords,
            'sources'         => $sources,
            // Raw values for summing aggregates easily
            '_raw'            => [
                'total_stock' => $totalProdStock,
                'locked'      => $lockedQty,
                'atp'         => $atpQty,
                'buffer'      => $bufferQty,
            ],
        ];
    }
}
