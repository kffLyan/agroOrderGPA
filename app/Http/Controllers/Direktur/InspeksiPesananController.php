<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;

// Controller inspeksi dan supervisi pesanan untuk Direktur
class InspeksiPesananController extends Controller
{
    // Menampilkan lembar inspeksi komprehensif pesanan tertentu
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

        return view('direktur.inspeksi-pesanan.show', ['order' => $order]);
    }
}
