<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Support\SecretaryManualOrderData;
use Illuminate\Contracts\View\View;

class SecretaryManualOrderController extends Controller
{
    public function __invoke(): View
    {
        $form = SecretaryManualOrderData::for(auth()->user());

        return view('staff.manual-order', [
            'operator' => $form['operator'],
            'policy' => $form['policy'],
            'source' => $form['source'],
            'client' => $form['client'],
            'commodities' => $form['commodities'],
            'catalog' => $form['catalog'],
            'logistics' => $form['logistics'],
            'payment' => $form['payment'],
        ]);
    }
}
