<?php

namespace App\Http\Resources\Sekretaris;

use App\Services\TataKelolaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VerifikasiPesananResource extends JsonResource
{
    // Mengubah model Order menjadi representasi data verifikasi pesanan, rencana alokasi, dan rincian harga untuk UI Sekretaris
    public function toArray(Request $request): array
    {
        $order = $this->resource;
        $clientName = $order->user?->company_name ?: ($order->user?->name ?: 'Mitra Klien GPA');
        $isContractClient = ($order->user?->client_type === 'B2B_KONTRAK');
        $totalQuantity = (float) $order->orderItems->sum('ordered_qty');
        $totalOrderAmount = (float) $order->grand_total;

        // Siapkan daftar ringkas item pesanan untuk tampilan antrean
        $queueCommodities = $order->orderItems->map(fn ($item) => [
            'name'         => $item->product?->name ?: 'Komoditas Sayur',
            'weight'       => number_format((float) $item->ordered_qty, 0, ',', '.') . ' kg',
            'weight_class' => 'text-ink-body',
            'class'        => 'text-ink font-semibold',
        ])->all();

        // Susun rencana alokasi panen dan cadangan sortir
        $harvestAllocations = $order->orderItems->map(function ($item) {
            $quantity = (float) $item->ordered_qty;
            return [
                'name'         => ($item->product?->name ?: 'Komoditas') . ' Grade A Pilihan',
                'sku'          => 'Kode: ' . ($item->product?->sku ?: 'GPA-001') . ' • Toleransi Sortir max 3%',
                'request'      => number_format($quantity, 0, ',', '.'),
                'request_unit' => 'KG',
                'system'       => number_format($quantity * 1.2, 0, ',', '.'),
                'system_unit'  => 'KG',
                'system_class' => 'text-success-deep font-bold',
                'surplus'      => 'Cadangan Aman +' . number_format($quantity * 0.2, 0, ',', '.') . ' kg',
                'surplus_class'=> 'text-success-deep',
                'plan'         => [
                    [
                        'label'   => '1. Alokasi Panen Kebun Binaan:',
                        'qty'     => number_format($quantity, 0, ',', '.') . ' kg',
                        'percent' => 100,
                        'fill'    => 'bg-success-deep',
                    ],
                ],
                'status'       => ['label' => 'Siap Panen', 'icon' => 'lock', 'class' => 'bg-accent text-success-ink'],
            ];
        })->all();

        $firstItem = $order->orderItems->first();
        $unitPrice = $firstItem ? (float) $firstItem->unit_price : ($totalQuantity > 0 ? ($totalOrderAmount / $totalQuantity) : 0);

        $clientContract = $order->user && $order->user->relationLoaded('contracts')
            ? $order->user->contracts->first()
            : ($order->user && method_exists($order->user, 'contracts') ? $order->user->contracts()->where('status', 'ACTIVE')->first() : null);
        $creditLimit = $clientContract ? (float) ($clientContract->fixed_price_per_kg * $clientContract->committed_volume_per_cycle * 2) : 0;

        // Hitung eksposur kredit, sisa plafon, dan status pembekuan akun klien
        $unpaidExposure = 0.0;
        if ($order->user) {
            $unpaidExposure = (float) $order->user->credit_used;
        }
        $creditAvailable = max(0.0, $creditLimit - $unpaidExposure);
        $isOverLimit = $isContractClient && ($totalOrderAmount > $creditAvailable);
        $tataKelolaService = app(TataKelolaService::class);
        $isClientFrozen = $order->user_id ? $tataKelolaService->isClientFrozen((int) $order->user_id) : false;
        $creditPercent = $creditLimit > 0 ? min(100, round(($unpaidExposure / $creditLimit) * 100)) : 0;

        $subtotalAmount = (float) $order->subtotal_amount;
        $shippingCost = (float) $order->shipping_cost;
        $taxAmount = (float) $order->tax_amount;
        $taxRate = (float) $order->tax_rate;
        $taxLabel = $order->tax_label;

        return [
            'id'                    => $order->order_number,
            'order_id'              => $order->id,
            'client'                => $clientName,
            'is_contract'           => $isContractClient,
            'channel'               => $isContractClient ? 'b2b' : 'reguler',
            'meta'                  => ($isContractClient ? 'Kontrak Mitra B2B' : 'Klien Reguler') . ' • Alamat: ' . ($order->delivery_address ?: 'Gudang Klien'),
            'entered'               => 'Masuk: ' . $order->created_at->diffForHumans(),
            'chip'                  => [
                'label' => $isContractClient ? 'KONTRAK B2B' : 'PESANAN REGULER',
                'class' => $isContractClient ? 'bg-accent text-success-ink font-bold' : 'bg-surface-pill text-ink-body font-medium',
            ],
            'queue_commodity_label' => 'Sayur Dipesan:',
            'queue_commodities'     => $queueCommodities,
            'queue_estimate_label'  => 'Nilai Pesanan Terkunci',
            'queue_estimate'        => 'Rp ' . number_format($totalOrderAmount, 0, ',', '.'),
            'queue_estimate_note'   => ['label' => 'Alokasi Siap', 'class' => 'text-success-deep'],
            'queue_status'          => ['label' => 'Stok Kebun Cukup', 'icon' => 'shield', 'class' => 'bg-accent text-success-ink'],
            'source'                => 'Sumber: ' . ($order->order_source === 'WEB_PORTAL' ? 'Website Portal Mitra' : 'Pemesanan Manual WhatsApp / Telepon'),
            'po_ref'                => 'NO. PO: ' . $order->order_number,
            'order_no'              => 'Pesanan #' . $order->order_number,
            'status'                => [
                'label' => $order->status === 'MENUNGGU_PEMBAYARAN' ? 'Invoice Diterbitkan (Menunggu Bayar)' : 'Menunggu Persetujuan Sekretaris',
                'class' => $order->status === 'MENUNGGU_PEMBAYARAN' ? 'bg-accent text-success-ink font-bold' : 'bg-warning-soft text-warning-deep font-bold',
            ],
            'created'               => 'Tanggal: ' . $order->created_at->format('d M Y, H:i') . ' WIB',
            'identity'              => [
                [
                    'label'      => 'Identitas Pembeli',
                    'name'       => $clientName,
                    'code'       => 'ID Klien: CLI-' . str_pad((string) $order->user_id, 4, '0', STR_PAD_LEFT),
                    'code_class' => 'text-success-deep',
                    'note'       => 'Status: ' . ($isContractClient ? 'Mitra Kontrak Aktif' : 'Pembeli Reguler'),
                ],
                [
                    'label'      => 'Kontak Penerima',
                    'name'       => $order->user?->name ?: 'Penanggung Jawab',
                    'code'       => ($order->user?->phone ?: '—') . ' (WhatsApp)',
                    'code_class' => 'text-ink-body',
                    'note'       => 'Email: ' . ($order->user?->email ?: '—'),
                ],
                [
                    'label'      => 'Alamat & Waktu Kirim',
                    'name'       => $order->delivery_address ?: 'Gudang Penerimaan Klien',
                    'code'       => 'Armada: Mobil Boks Berpendingin',
                    'code_class' => 'text-ink-body',
                    'note'       => 'Jadwal Kirim: ' . ($order->target_delivery_date ? $order->target_delivery_date->format('d M Y') : 'Pagi Hari (05:00 WIB)'),
                ],
            ],
            'supply_status'         => ['label' => 'Pasokan Aman', 'icon' => 'check', 'class' => 'bg-accent text-success-ink font-bold'],
            'allocations'           => $harvestAllocations,
            'subtotal_raw'          => $subtotalAmount,
            'shipping_raw'          => $shippingCost,
            'tax_amount_raw'        => $taxAmount,
            'tax_rate_raw'          => $taxRate,
            'tax_label'             => $taxLabel,
            'grand_total_raw'       => $totalOrderAmount,
            'pricing'               => [
                ['label' => 'Subtotal Komoditas', 'value' => 'Rp ' . number_format($subtotalAmount, 0, ',', '.'), 'suffix' => null, 'note' => null, 'highlight' => false],
                ['label' => 'Biaya Logistik Armada Chiller', 'value' => $shippingCost > 0 ? ('Rp ' . number_format($shippingCost, 0, ',', '.')) : 'Rp 0', 'suffix' => null, 'note' => $shippingCost > 0 ? null : '(Bebas Ongkir Franco)', 'highlight' => false],
                ['label' => "Pajak Pertambahan Nilai ({$taxLabel})", 'value' => $taxAmount > 0 ? ('Rp ' . number_format($taxAmount, 0, ',', '.')) : 'Rp 0', 'suffix' => null, 'note' => $taxAmount > 0 ? '(PPN Standar PKP)' : '(Bebas Pajak Produk Pertanian)', 'highlight' => false],
                ['label' => 'Total Tagihan Invoice', 'value' => 'Rp ' . number_format($totalOrderAmount, 0, ',', '.'), 'suffix' => null, 'note' => null, 'highlight' => true],
            ],
            'initial_qty'           => number_format($totalQuantity, 0, ',', '.') . ' kg',
            'deliveries'            => [],
            'deliveries_total'      => 'Rp ' . number_format($totalOrderAmount, 0, ',', '.'),
            'plafon'                => $creditLimit > 0 ? ('Rp ' . number_format($creditLimit, 0, ',', '.')) : 'Sesuai Ketentuan PO',
            'credit_limit_raw'      => $creditLimit,
            'credit_used_raw'       => $unpaidExposure,
            'credit_available_raw'  => $creditAvailable,
            'is_over_limit'         => $isOverLimit,
            'is_frozen'             => $isClientFrozen,
            'credit_percent'        => $creditPercent,
            'credit_limit_label'    => 'Rp ' . number_format($creditLimit, 0, ',', '.'),
            'credit_used_label'     => 'Rp ' . number_format($unpaidExposure, 0, ',', '.'),
            'credit_avail_label'    => 'Rp ' . number_format($creditAvailable, 0, ',', '.'),
            'over_amount_label'     => 'Rp ' . number_format(max(0.0, $totalOrderAmount - $creditAvailable), 0, ',', '.'),
        ];
    }
}
