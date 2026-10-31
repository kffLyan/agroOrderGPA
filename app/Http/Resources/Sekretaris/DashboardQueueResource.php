<?php

namespace App\Http\Resources\Sekretaris;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardQueueResource extends JsonResource
{
    /**
     * Transform the order resource into an array for secretary dashboard queue.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Order $ord */
        $ord = $this->resource;

        $clientName = $ord->user?->company_name ?: ($ord->user?->name ?: 'Mitra Klien GPA');
        $isContract = ($ord->user?->client_type === 'B2B_KONTRAK');

        $itemsList = $ord->orderItems->map(function ($it) {
            return [
                'name'   => $it->product?->name ?: 'Komoditas Sayur',
                'weight' => number_format((float) $it->ordered_qty, 0, ',', '.') . ' kg',
                'class'  => 'font-medium text-ink',
            ];
        })->all();

        $estTotal = (float) $ord->grand_total;

        return [
            'id'          => $ord->id,
            'po'          => $ord->order_number,
            'client'      => $clientName,
            'po_chip'     => [
                'label' => $isContract ? 'KONTRAK B2B' : 'REGULER',
                'class' => $isContract ? 'bg-accent text-success-ink font-bold' : 'bg-surface-pill text-ink-body',
            ],
            'terms'       => [
                'label' => $isContract ? 'Term of Payment (Tempo 30H)' : 'Pembayaran Di Muka (Cash/Transfer)',
                'class' => 'font-bold text-ink-subtle',
            ],
            'items'       => $itemsList,
            'estimate'    => 'Rp ' . number_format($estTotal, 0, ',', '.'),
            'status'      => [
                'label' => match ($ord->status) {
                    'MENUNGGU_VERIFIKASI'  => 'Menunggu Validasi',
                    'MENUNGGU_PEMBAYARAN' => 'Menunggu Pembayaran',
                    'LUNAS'                => 'Lunas',
                    'SIAP_KIRIM'           => 'Siap Terbit SJ',
                    'DALAM_PENGIRIMAN'     => 'Dalam Pengiriman',
                    'SELESAI'              => 'Terkirim Selesai',
                    default                => 'Draft PO',
                },
                'class' => match ($ord->status) {
                    'MENUNGGU_VERIFIKASI'  => 'border-warning/40 bg-warning-wash font-bold text-warning-deep',
                    'MENUNGGU_PEMBAYARAN' => 'border-line-soft bg-surface-pill font-medium text-ink-body',
                    'LUNAS'                => 'border-accent-deep bg-accent font-bold text-success-deep',
                    'SIAP_KIRIM'           => 'border-brand/40 bg-brand-faint font-bold text-brand-deep',
                    default                => 'border-line-soft bg-surface-pill text-ink-body',
                },
                'icon'  => match ($ord->status) {
                    'MENUNGGU_VERIFIKASI'  => 'clock',
                    'MENUNGGU_PEMBAYARAN' => 'receipt',
                    'LUNAS'                => 'badge-check',
                    'SIAP_KIRIM'           => 'truck',
                    default                => 'package',
                },
            ],
            'status_note' => [
                'label' => $ord->status === 'MENUNGGU_VERIFIKASI'
                    ? ('Masuk ' . $ord->created_at->diffForHumans())
                    : 'Operasional Berjalan',
                'class' => $ord->status === 'MENUNGGU_VERIFIKASI'
                    ? 'font-semibold text-warning-deep'
                    : 'text-ink-quiet',
            ],
            'actions'     => [
                [
                    'label'   => 'Verifikasi',
                    'tone'    => 'ink',
                    'intent'  => 'approve',
                    'message' => "PO {$ord->order_number} disetujui & kuota panen dikunci.",
                    'url'     => route('sekretaris.verifikasi'),
                ],
                [
                    'label'   => 'Detail',
                    'tone'    => 'default',
                    'intent'  => 'detail',
                    'message' => "Membuka rincian pesanan {$ord->order_number}.",
                    'url'     => route('sekretaris.verifikasi'),
                ],
            ],
        ];
    }
}
