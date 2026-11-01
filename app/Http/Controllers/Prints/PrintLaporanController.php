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
        $data = $service->buildPrintData(
            $request->query('start_date'),
            $request->query('end_date'),
            $request->query('client_id') ? (int) $request->query('client_id') : null
        );

        return view('prints.report', $data);
    }
}
