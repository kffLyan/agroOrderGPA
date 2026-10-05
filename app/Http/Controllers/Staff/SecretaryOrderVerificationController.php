<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Support\SecretaryVerificationData;
use Illuminate\Contracts\View\View;

class SecretaryOrderVerificationController extends Controller
{
    public function __invoke(): View
    {
        $console = SecretaryVerificationData::for(auth()->user());

        return view('staff.verification', [
            'operator' => $console['operator'],
            'heading' => $console['heading'],
            'steps' => $console['steps'],
            'queue' => $console['queue'],
            'checklist' => $console['checklist'],
            'rule03' => $console['rule03'],
            'rule04' => $console['rule04'],
            'rule14' => $console['rule14'],
        ]);
    }
}
