<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Support\ClientDashboardData;
use App\Support\ClientPaymentProofData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentProofController extends Controller
{
    public function __invoke(Request $request): View
    {
        $invoices = ClientPaymentProofData::invoices();

        $selected = $request->query('tagihan');

        if (! collect($invoices)->contains(fn (array $invoice): bool => $invoice['po'] === $selected)) {
            $selected = $invoices[0]['po'] ?? null;
        }

        return view('client.payment-proof', [
            'client' => ClientDashboardData::client(Auth::user()),
            'header' => ClientPaymentProofData::header(),
            'invoices' => $invoices,
            'selected' => $selected,
            'account' => ClientPaymentProofData::account(),
            'fileRules' => ClientPaymentProofData::fileRules(),
            'form' => ClientPaymentProofData::form(),
            'file' => ClientPaymentProofData::file(),
            'compliance' => ClientPaymentProofData::compliance(),
            'steps' => ClientPaymentProofData::steps(),
            'notice' => ClientPaymentProofData::notice(),
            'hotline' => ClientPaymentProofData::hotline(),
            'plafon' => ClientPaymentProofData::plafon(),
            'historyColumns' => ClientPaymentProofData::historyColumns(),
            'history' => ClientPaymentProofData::history(),
            'exportReport' => ClientPaymentProofData::exportReport(),
        ]);
    }
}
