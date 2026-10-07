<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Support\SecretaryManualOrderData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ManualOrderController extends Controller
{
    /**
     * Menampilkan formulir Input Pesanan Manual / Darurat via Telepon atau WA
     */
    public function index(Request $request): View
    {
        $form = SecretaryManualOrderData::for(auth()->user());

        return view('sekretaris.manual-order', [
            'operator'    => $form['operator'],
            'policy'      => $form['policy'],
            'source'      => $form['source'],
            'client'      => $form['client'],
            'commodities' => $form['commodities'],
            'catalog'     => $form['catalog'],
            'logistics'   => $form['logistics'],
            'payment'     => $form['payment'],
        ]);
    }
}
