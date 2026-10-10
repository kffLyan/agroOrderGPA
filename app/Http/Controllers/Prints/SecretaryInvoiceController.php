<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Support\SecretaryInvoiceData;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SecretaryInvoiceController extends Controller
{
    public function __invoke(string $invoice): View
    {
        $document = SecretaryInvoiceData::printDocument($invoice);

        if ($document === null) {
            throw new NotFoundHttpException;
        }

        return view('prints.invoice', $document);
    }
}
