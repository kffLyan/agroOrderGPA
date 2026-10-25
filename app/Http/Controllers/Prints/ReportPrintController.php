<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Services\ReportPrintService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportPrintController extends Controller
{
    /**
     * Tampilkan cetak Laporan Resmi Rekapitulasi Penjualan Eksekutif GPA A4.
     */
    public function __invoke(Request $request, ReportPrintService $service): View
    {
        $data = $service->buildPrintData(
            $request->query('start_date'),
            $request->query('end_date'),
            $request->query('client_id') ? (int) $request->query('client_id') : null
        );

        return view('prints.report', $data);
    }
}
