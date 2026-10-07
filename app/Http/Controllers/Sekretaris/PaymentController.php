<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Support\SecretaryPaymentData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Menampilkan konsol Verifikasi Pembayaran & Pencocokan Rekening Koran
     */
    public function index(Request $request): View
    {
        $console = SecretaryPaymentData::for(auth()->user());

        return view('sekretaris.payment', [
            'operator'   => $console['operator'],
            'heading'    => $console['heading'],
            'policy'     => $console['policy'],
            'metrics'    => $console['metrics'],
            'queue'      => $console['queue'],
            'inspection' => $console['inspection'],
            'audit'      => $console['audit'],
        ]);
    }
}
