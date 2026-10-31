<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * S3-01: Menentukan apakah pengguna boleh melihat rincian pesanan tertentu
     */
    public function view(User $user, Order $order): bool
    {
        // 1. Direktur & Sekretaris diizinkan melihat seluruh data pesanan rantai pasok
        if (in_array($user->role, ['DIREKTUR', 'SEKRETARIS'])) {
            return true;
        }

        // 2. Klien HANYA diizinkan melihat pesanan miliknya sendiri (Anti-IDOR)
        return $user->id === $order->user_id;
    }

    /**
     * Menentukan apakah pengguna boleh memperbarui atau membatalkan pesanan
     */
    public function update(User $user, Order $order): bool
    {
        if (in_array($user->role, ['DIREKTUR', 'SEKRETARIS'])) {
            return true;
        }

        return $user->id === $order->user_id && $order->status === 'MENUNGGU_VERIFIKASI';
    }
}
