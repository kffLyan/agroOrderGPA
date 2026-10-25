<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

class ArmadaService
{
    /**
     * Mengambil daftar pesanan tugas pengantaran armada untuk supir tertentu
     *
     * @return Collection<int, Order>
     */
    public function getActiveOrdersForDriver(?User $user): Collection
    {
        $orders = Order::with(['orderItems.product', 'user', 'driver', 'verifier'])
            ->where(function ($query) use ($user) {
                if ($user) {
                    $query->where('driver_id', $user->id)
                        ->orWhereIn('status', ['DALAM_PENGIRIMAN', 'SIAP_KIRIM']);
                } else {
                    $query->whereIn('status', ['DALAM_PENGIRIMAN', 'SIAP_KIRIM']);
                }
            })
            ->whereNotIn('status', ['DRAFT', 'BATAL'])
            ->latest()
            ->get();

        // Fallback jika belum ada pesanan aktif sama sekali
        if ($orders->isEmpty()) {
            $orders = Order::with(['orderItems.product', 'user', 'driver', 'verifier'])
                ->whereIn('status', ['LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'])
                ->latest()
                ->take(5)
                ->get();
        }

        return $orders;
    }

    /**
     * Mencari nomor pelat armada operasional yang terdaftar
     *
     * @param Collection<int, Order> $orders
     */
    public function resolveVehiclePlate(Collection $orders): string
    {
        return $orders->first(fn ($o) => !empty($o->vehicle_plate_number))?->vehicle_plate_number ?? '—';
    }

    /**
     * Menghitung total bobot muatan pengantaran (kg)
     *
     * @param Collection<int, Order> $orders
     */
    public function calculateTotalWeightKg(Collection $orders): float
    {
        return (float) $orders->sum(fn ($order) => $order->orderItems->sum('ordered_qty'));
    }

    /**
     * Mengambil target pesanan spesifik atau pesanan terkini untuk pengantaran/PoD
     */
    public function resolveCurrentDeliveryOrder(?int $orderId, ?User $user): ?Order
    {
        $query = Order::with(['orderItems.product', 'user', 'driver', 'verifier']);

        if ($orderId) {
            $order = $query->find($orderId);
            if ($order) {
                return $order;
            }
        }

        return $this->getActiveOrdersForDriver($user)->first();
    }
}
