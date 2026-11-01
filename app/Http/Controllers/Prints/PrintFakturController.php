<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\FakturPrintService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PrintFakturController extends Controller
{
    // Menyiapkan cetak faktur penagihan resmi (invoice) format kontrak maupun reguler dengan validasi kepemilikan
    public function __invoke(Request $request, string $invoice, FakturPrintService $service): View
    {
        $invoiceModel = Invoice::where('invoice_number', $invoice)
            ->orWhere('id', $invoice)
            ->first();

        // Memvalidasi kepemilikan faktur (Anti-IDOR) agar klien hanya dapat mencetak fakturnya sendiri
        if (auth()->user()?->role === 'KLIEN' && (!$invoiceModel || $invoiceModel->user_id !== auth()->id())) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat faktur pelanggan lain.');
        }

        $data = $service->buildPrintData($invoice, $request->query('format'));

        return view('prints.invoice', $data);
    }
}
