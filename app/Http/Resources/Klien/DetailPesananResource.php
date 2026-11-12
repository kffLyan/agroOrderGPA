<?php

namespace App\Http\Resources\Klien;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Resource untuk memetakan visual stepper pelacakan status pesanan, foto PoD, dan badge pada antarmuka Klien
class DetailPesananResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Order $order */
        $order = $this->resource;

        $hasPendingProof = ($order->status === 'MENUNGGU_PEMBAYARAN' && $order->has_pending_proof);
        $podPhotos = $order->pod_photo_asset_urls;
        $manifestPhoto = $podPhotos[0] ?? null;
        $dockPhoto = $podPhotos[1] ?? ($podPhotos[0] ?? null);
        $hasTwoPhotos = count($podPhotos) >= 2;
        $hasAnyPhoto = !empty($podPhotos);

        $statusStyles = [
            'MENUNGGU_VERIFIKASI' => 'bg-warning-soft text-warning-deep ring-warning/40',
            'MENUNGGU_PEMBAYARAN' => $hasPendingProof ? 'bg-warning-soft text-warning-deep ring-warning/40' : 'bg-brand/10 text-brand ring-brand/30',
            'LUNAS'               => 'bg-accent/40 text-success-deep ring-success/30',
            'SIAP_KIRIM'          => 'bg-accent text-ink ring-accent-edge',
            'DALAM_PENGIRIMAN'    => 'bg-brand text-white ring-accent',
            'SELESAI'             => 'bg-accent text-success-ink ring-accent',
            'SELESAI_CATATAN'     => 'bg-warning-soft text-warning-deep ring-warning',
            'BATAL'               => 'bg-surface-pill text-ink-quiet ring-line-board',
        ];

        $statusLabels = [
            'MENUNGGU_VERIFIKASI' => 'Menunggu Verifikasi Pesanan',
            'MENUNGGU_PEMBAYARAN' => $hasPendingProof ? 'Menunggu Verifikasi Pembayaran' : 'Menunggu Pembayaran',
            'LUNAS'               => 'Pembayaran Lunas',
            'SIAP_KIRIM'          => 'Surat Jalan Terbit (Siap Kirim)',
            'DALAM_PENGIRIMAN'    => 'Armada Menuju Dock',
            'SELESAI'             => 'Selesai & Diterima',
            'SELESAI_CATATAN'     => 'Selesai (Ada Catatan Retur)',
            'BATAL'               => 'Pesanan Dibatalkan',
        ];

        $statusOrder = $order->status;

        $stepStates = [
            1 => 'done',
            2 => in_array($statusOrder, ['MENUNGGU_PEMBAYARAN', 'LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? 'done' : ($statusOrder === 'MENUNGGU_VERIFIKASI' ? 'active' : 'pending'),
            3 => in_array($statusOrder, ['LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? 'done' : ($statusOrder === 'MENUNGGU_PEMBAYARAN' ? 'active' : 'pending'),
            4 => in_array($statusOrder, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? 'done' : ($statusOrder === 'LUNAS' ? 'active' : 'pending'),
            5 => in_array($statusOrder, ['SELESAI', 'SELESAI_CATATAN']) ? 'done' : (in_array($statusOrder, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN']) ? 'active' : 'pending'),
        ];

        if ($statusOrder === 'BATAL') {
            $stepStates = [1 => 'done', 2 => 'cancelled', 3 => 'pending', 4 => 'pending', 5 => 'pending'];
        }

        $rejectedPayment = $order->has_rejected_proof ? $order->rejected_proof_payment : null;

        return compact(
            'hasPendingProof',
            'podPhotos',
            'manifestPhoto',
            'dockPhoto',
            'hasTwoPhotos',
            'hasAnyPhoto',
            'statusStyles',
            'statusLabels',
            'stepStates',
            'rejectedPayment'
        );
    }
}

