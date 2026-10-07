<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Support\SecretaryDispatchData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DispatchController extends Controller
{
    /**
     * Menampilkan konsol Surat Jalan & Dispatch Armada Logistik
     */
    public function index(Request $request): View
    {
        $console = SecretaryDispatchData::for(auth()->user());

        return view('sekretaris.dispatch', [
            'operator'   => $console['operator'],
            'heading'    => $console['heading'],
            'metrics'    => $console['metrics'],
            'rule05'     => $console['rule05'],
            'queue'      => $console['queue'],
            'letterhead' => $console['letterhead'],
            'security'   => $console['security'],
            'signatures' => $console['signatures'],
            'release'    => $console['release'],
        ]);
    }
}
