<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

class ArmadaService
{
    // Mengambil daftar pesanan tugas pengantaran armada khusus supir yang ditugaskan (terisolasi per supir)
    public function getActiveOrdersForDriver(?User $driver): Collection
    {
        if (!$driver) {
            return collect();
        }

        return Order::with(['orderItems.product', 'user', 'driver', 'verifier'])
            ->where('driver_id', $driver->id)
            ->whereNotIn('status', ['DRAFT', 'BATAL'])
            ->orderByRaw("CASE 
                WHEN status = 'DALAM_PENGIRIMAN' THEN 1 
                WHEN status = 'SIAP_KIRIM' THEN 2 
                WHEN status IN ('SELESAI', 'SELESAI_CATATAN') THEN 3 
                ELSE 4 END")
            ->latest('updated_at')
            ->get();
    }

    // Mengidentifikasi nomor pelat armada operasional kendaraan yang ditugaskan pada pesanan aktif.
    public function resolveVehiclePlate(Collection $orders): string
    {
        return $orders->first(fn ($order) => !empty($order->vehicle_plate_number))?->vehicle_plate_number ?? '—';
    }

    // Menghitung total bobot tonase muatan pengantaran sayur dan komoditas dalam satuan kilogram.
    public function calculateTotalWeightKg(Collection $orders): float
    {
        return (float) $orders->sum(fn ($order) => $order->orderItems ? $order->orderItems->sum('ordered_qty') : 0);
    }

    // Mengambil pesanan pengantaran spesifik milik supir berdasarkan ID atau pesanan aktif pertama armada.
    public function resolveCurrentDeliveryOrder(?int $orderId, ?User $driver): ?Order
    {
        if (!$driver) {
            return null;
        }

        $query = Order::with(['orderItems.product', 'user', 'driver', 'verifier'])
            ->where('driver_id', $driver->id);

        if ($orderId) {
            $order = $query->find($orderId);
            if ($order) {
                return $order;
            }
        }

        return $this->getActiveOrdersForDriver($driver)->first();
    }
}
