<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Support\SecretaryDispatchData;
use Illuminate\Contracts\View\View;

class SecretaryDispatchController extends Controller
{
    public function __invoke(): View
    {
        $console = SecretaryDispatchData::for(auth()->user());

        return view('staff.dispatch', [
            'operator' => $console['operator'],
            'heading' => $console['heading'],
            'metrics' => $console['metrics'],
            'rule05' => $console['rule05'],
            'queue' => $console['queue'],
            'letterhead' => $console['letterhead'],
            'security' => $console['security'],
            'signatures' => $console['signatures'],
            'release' => $console['release'],
        ]);
    }
}
