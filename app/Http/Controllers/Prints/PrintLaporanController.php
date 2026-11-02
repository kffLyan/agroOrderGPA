<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Services\LaporanPrintService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PrintLaporanController extends Controller
{
    // Menyiapkan cetak laporan resmi rekapitulasi penjualan eksekutif standar ukuran kertas A4
    public function __invoke(Request $request, LaporanPrintService $service): View
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $clientId  = $request->query('client_id') ? (int) $request->query('client_id') : null;

        $data = $service->buildPrintData(
            $startDate,
            $endDate,
            $clientId
        );

        return view('prints.report', $data);
    }
}
