<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Support\SecretaryInvoiceData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Menampilkan konsol Faktur Konsolidasi & Penagihan Tempo B2B
     */
    public function index(Request $request): View
    {
        $console = SecretaryInvoiceData::for(auth()->user());

        return view('sekretaris.invoice', [
            'operator'  => $console['operator'],
            'heading'   => $console['heading'],
            'metrics'   => $console['metrics'],
            'generator' => $console['generator'],
            'clients'   => $console['clients'],
            'ledger'    => $console['ledger'],
        ]);
    }
}
