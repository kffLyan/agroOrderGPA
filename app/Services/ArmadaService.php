<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

class ArmadaService
{
    // Mengambil daftar pesanan tugas pengantaran armada untuk supir tertentu atau pesanan siap kirim.
    public function getActiveOrdersForDriver(?User $driver): Collection
    {
        $orders = Order::with(['orderItems.product', 'user', 'driver', 'verifier'])
            ->where(function ($query) use ($driver) {
                if ($driver) {
                    $query->where('driver_id', $driver->id)
                        ->orWhereIn('status', ['DALAM_PENGIRIMAN', 'SIAP_KIRIM']);
                } else {
                    $query->whereIn('status', ['DALAM_PENGIRIMAN', 'SIAP_KIRIM']);
                }
            })
            ->whereNotIn('status', ['DRAFT', 'BATAL'])
            ->latest()
            ->get();

        // Fallback mengambil riwayat pengiriman jika belum ada tugas aktif baru
        if ($orders->isEmpty()) {
            $orders = Order::with(['orderItems.product', 'user', 'driver', 'verifier'])
                ->whereIn('status', ['LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'])
                ->latest()
                ->take(5)
                ->get();
        }

        return $orders;
    }

    // Mengidentifikasi nomor pelat armada operasional kendaraan yang ditugaskan pada pesanan aktif.
    public function resolveVehiclePlate(Collection $orders): string
    {
        return $orders->first(fn ($order) => !empty($order->vehicle_plate_number))?->vehicle_plate_number ?? '—';
    }

    // Menghitung total bobot tonase muatan pengantaran sayur dan komoditas dalam satuan kilogram.
    public function calculateTotalWeightKg(Collection $orders): float
    {
        return (float) $orders->sum(fn ($order) => $order->orderItems->sum('ordered_qty'));
    }

    // Mengambil pesanan pengantaran spesifik berdasarkan ID atau pesanan aktif pertama armada.
    public function resolveCurrentDeliveryOrder(?int $orderId, ?User $driver): ?Order
    {
        $query = Order::with(['orderItems.product', 'user', 'driver', 'verifier']);

        if ($orderId) {
            $order = $query->find($orderId);
            if ($order) {
                return $order;
            }
        }

        return $this->getActiveOrdersForDriver($driver)->first();
    }
}
