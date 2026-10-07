<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderInspectionController extends Controller
{
    /**
     * Menampilkan detail komprehensif pesanan untuk inspeksi pengawasan Direktur
     */
    public function show(int $id)
    {
        $order = Order::with([
            'user',
            'verifier',
            'driver',
            'invoice',
            'orderItems.product',
            'payments.verifier',
        ])->findOrFail($id);

        return view('direktur.orders.show', compact('order'));
    }
}
