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
        $data  = $service->buildPrintData($order);

        return view('prints.surat-jalan', $data);
    }
}
