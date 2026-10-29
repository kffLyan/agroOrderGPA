<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
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
        $invModel = Invoice::where('invoice_number', $invoice)
            ->orWhere('id', $invoice)
            ->first();

        // Anti-IDOR: Jika yang mengakses adalah Klien, pastikan faktur ini miliknya sendiri
        if (auth()->user()?->role === 'KLIEN' && (!$invModel || $invModel->user_id !== auth()->id())) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat faktur pelanggan lain.');
        }

        $data = $service->buildPrintData($invoice, $request->query('format'));

        return view('prints.invoice', $data);
    }
}
