<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Support\SecretaryReportData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Menampilkan konsol Rekap Laporan & Jurnal Operasional Harian
     */
    public function index(Request $request): View
    {
        $console = SecretaryReportData::for(auth()->user());

        return view('sekretaris.report', [
            'operator'   => $console['operator'],
            'heading'    => $console['heading'],
            'filters'    => $console['filters'],
            'metrics'    => $console['metrics'],
            'standards'  => $console['standards'],
            'tabs'       => $console['tabs'],
            'journal'    => $console['journal'],
            'deviation'  => $console['deviation'],
            'validation' => $console['validation'],
            'seal'       => $console['seal'],
        ]);
    }
}
