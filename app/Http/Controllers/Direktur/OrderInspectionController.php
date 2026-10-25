<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;

class OrderInspectionController extends Controller
{
    /**
     * Menampilkan detail komprehensif pesanan untuk inspeksi pengawasan Direktur
     */
    public function show(Order $order): View
    {
        $order->loadMissing([
            'user',
            'verifier',
            'driver',
            'invoice',
            'orderItems.product',
            'payments.verifier',
        ]);

        return view('direktur.orders.show', compact('order'));
    }
}
