<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Services\InvoicePrintService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SecretaryInvoiceController extends Controller
{
    /**
     * Tampilkan cetak faktur / invoice resmi Koperasi Produsen GPA (Format Kontrak & Reguler).
     */
    public function __invoke(Request $request, string $invoice, InvoicePrintService $service): View
    {
        $data = $service->buildPrintData($invoice, $request->query('format'));

        return view('prints.invoice', $data);
    }
}
