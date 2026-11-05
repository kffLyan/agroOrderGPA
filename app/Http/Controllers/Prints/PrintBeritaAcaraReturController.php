<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Services\ReturPrintService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PrintBeritaAcaraReturController extends Controller
{
    // Menyiapkan cetak Berita Acara Retur (BAR) dan selisih mutu timbangan fisik untuk dokumentasi serah terima
    public function __invoke(Request $request, ReturPrintService $service): View
    {
        $orderId = $request->query('order_id');
        $order   = $service->resolveOrder($orderId);

        // Anti-IDOR: Jika Klien mengakses Berita Acara Retur, pastikan pesanan ini miliknya
        if (auth()->user()?->role === 'KLIEN' && (!$order || $order->user_id !== auth()->id())) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Berita Acara Retur pesanan lain.');
        }

        $document = $service->buildPrintData($order);

        return view('prints.return-act', $document);
    }
}
