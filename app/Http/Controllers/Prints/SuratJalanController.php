<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Services\SuratJalanPrintService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SuratJalanController extends Controller
{
    /**
     * Tampilkan cetak Surat Jalan resmi pengiriman sayur segar GPA.
     */
    public function __invoke(Request $request, SuratJalanPrintService $service): View
    {
        $order = $service->resolveOrder($request->query('order_id'));

        // Anti-IDOR: Jika Klien mengakses Surat Jalan, pastikan pesanan ini miliknya
        if (auth()->user()?->role === 'KLIEN' && (!$order || $order->user_id !== auth()->id())) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Surat Jalan pesanan lain.');
        }

        $data  = $service->buildPrintData($order);

        return view('prints.surat-jalan', $data);
    }
}
