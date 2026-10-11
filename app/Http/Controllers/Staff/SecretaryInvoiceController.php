<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Support\SecretaryInvoiceData;
use Illuminate\Contracts\View\View;

class SecretaryInvoiceController extends Controller
{
    public function __invoke(): View
    {
        $console = SecretaryInvoiceData::for(auth()->user());

        return view('staff.invoice', [
            'operator' => $console['operator'],
            'heading' => $console['heading'],
            'metrics' => $console['metrics'],
            'generator' => $console['generator'],
            'clients' => $console['clients'],
            'ledger' => $console['ledger'],
        ]);
    }
}
