<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;

/**
 * Controller Inspeksi Pengawasan Pesanan Direktur.
 *
 * Penjelasan UKK:
 * Halaman ini digunakan oleh Direktur Utama untuk melakukan pengawasan (supervisi)
 * dan audit forensik terhadap seluruh detail pesanan (PO, item komoditas, verifikator,
 * pengemudi armada, faktur penagihan, serta jejak audit pembayaran).
 */
class InspeksiPesananController extends Controller
{
    /**
     * Menampilkan lembar inspeksi komprehensif pesanan tertentu.
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

        return view('direktur.inspeksi-pesanan.show', compact('order'));
    }
}
