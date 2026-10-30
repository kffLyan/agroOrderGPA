<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Services\ReturnActPrintService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReturnActController extends Controller
{
    /**
     * Tampilkan cetak Berita Acara Retur & Selisih Mutu Timbangan (BAP).
     */
    public function __invoke(Request $request, ReturnActPrintService $service): View
    {
        $order = $service->resolveOrder($request->query('order_id'));

        // Anti-IDOR: Jika Klien mengakses Berita Acara Retur, pastikan pesanan ini miliknya
        if (auth()->user()?->role === 'KLIEN' && (!$order || $order->user_id !== auth()->id())) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Berita Acara Retur pesanan lain.');
        }

        $document = $service->buildPrintData($order);

        return view('prints.return-act', $document);
    }
}
