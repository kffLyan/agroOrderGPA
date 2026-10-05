<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Support\SecretaryPaymentData;
use Illuminate\Contracts\View\View;

class SecretaryPaymentController extends Controller
{
    public function __invoke(): View
    {
        $console = SecretaryPaymentData::for(auth()->user());

        return view('staff.payment', [
            'operator' => $console['operator'],
            'heading' => $console['heading'],
            'policy' => $console['policy'],
            'metrics' => $console['metrics'],
            'queue' => $console['queue'],
            'inspection' => $console['inspection'],
            'audit' => $console['audit'],
        ]);
    }
}
