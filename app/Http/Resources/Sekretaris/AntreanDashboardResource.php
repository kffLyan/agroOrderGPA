<?php

namespace App\Http\Resources\Sekretaris;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AntreanDashboardResource extends JsonResource
{
    // Mengubah model Order menjadi data baris kartu antrean pesanan pada dashboard Sekretaris
    public function toArray(Request $request): array
    {
        $order = $this->resource;

        // Ambil nama klien dan tentukan apakah klien bertipe kontrak
        $clientName = $order->user?->company_name ?: ($order->user?->name ?: 'Mitra Klien GPA');
        $isContractClient = ($order->user?->client_type === 'B2B_KONTRAK');

        // Kumpulkan daftar rincian item pesanan
        $orderItemList = $order->orderItems->map(function ($item) {
            return [
                'name'   => $item->product?->name ?: 'Komoditas Sayur',
                'weight' => number_format((float) $item->ordered_qty, 0, ',', '.') . ' kg',
                'class'  => 'font-medium text-ink',
            ];
        })->all();

        $estimatedTotal = (float) $order->grand_total;

        return [
            'id'          => $order->id,
            'po'          => $order->order_number,
            'client'      => $clientName,
            'po_chip'     => [
                'label' => $isContractClient ? 'KONTRAK B2B' : 'REGULER',
                'class' => $isContractClient ? 'bg-accent text-success-ink font-bold' : 'bg-surface-pill text-ink-body',
            ],
            'terms'       => [
                'label' => $isContractClient ? 'Term of Payment (Tempo 30H)' : 'Pembayaran Di Muka (Cash/Transfer)',
                'class' => 'font-bold text-ink-subtle',
            ],
            'items'       => $orderItemList,
            'estimate'    => 'Rp ' . number_format($estimatedTotal, 0, ',', '.'),
            'status'      => [
                'label' => match ($order->status) {
                    'MENUNGGU_VERIFIKASI' => 'Menunggu Validasi',
                    'MENUNGGU_PEMBAYARAN' => 'Menunggu Pembayaran',
                    'LUNAS'               => 'Lunas',
                    'SIAP_KIRIM'          => 'Siap Terbit SJ',
                    'DALAM_PENGIRIMAN'    => 'Dalam Pengiriman',
                    'SELESAI'             => 'Terkirim Selesai',
                    'SELESAI_CATATAN'     => 'Selesai (Catatan)',
                    'BATAL'               => 'Dibatalkan',
                    default               => ucwords(strtolower(str_replace('_', ' ', (string) $order->status))),
                },
                'class' => match ($order->status) {
                    'MENUNGGU_VERIFIKASI' => 'border-warning/40 bg-warning-wash font-bold text-warning-deep',
                    'MENUNGGU_PEMBAYARAN' => 'border-line-soft bg-surface-pill font-medium text-ink-body',
                    'LUNAS'               => 'border-accent-deep bg-accent font-bold text-success-deep',
                    'SIAP_KIRIM'          => 'border-brand/40 bg-brand-faint font-bold text-brand-deep',
                    default               => 'border-line-soft bg-surface-pill text-ink-body',
                },
                'icon'  => match ($order->status) {
                    'MENUNGGU_VERIFIKASI' => 'clock',
                    'MENUNGGU_PEMBAYARAN' => 'receipt',
                    'LUNAS'               => 'badge-check',
                    'SIAP_KIRIM'          => 'truck',
                    default               => 'package',
                },
            ],
            'status_note' => [
                'label' => $order->status === 'MENUNGGU_VERIFIKASI'
                    ? ('Masuk ' . $order->created_at->diffForHumans())
                    : 'Operasional Berjalan',
                'class' => $order->status === 'MENUNGGU_VERIFIKASI'
                    ? 'font-semibold text-warning-deep'
                    : 'text-ink-quiet',
            ],
            'actions'     => [
                [
                    'label'   => 'Verifikasi',
                    'tone'    => 'ink',
                    'intent'  => 'approve',
                    'message' => "PO {$order->order_number} disetujui & kuota panen dikunci.",
                    'url'     => route('sekretaris.verifikasi', ['order' => $order->id]),
                ],
                [
                    'label'   => 'Detail',
                    'tone'    => 'default',
                    'intent'  => 'detail',
                    'message' => "Membuka rincian pesanan {$order->order_number}.",
                    'url'     => route('sekretaris.verifikasi', ['order' => $order->id]),
                ],
            ],
        ];
    }
}
