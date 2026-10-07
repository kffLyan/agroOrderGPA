<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Support\SecretaryInventoryData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Menampilkan konsol Kesiapan Stok & Buffer Panen Gudang
     */
    public function index(Request $request): View
    {
        $console = SecretaryInventoryData::for(auth()->user());

        return view('sekretaris.inventory', [
            'operator'   => $console['operator'],
            'heading'    => $console['heading'],
            'actions'    => $console['actions'],
            'ruleEngine' => $console['ruleEngine'],
            'metrics'    => $console['metrics'],
            'filters'    => $console['filters'],
            'ledger'     => $console['ledger'],
            'activity'   => $console['activity'],
            'readiness'  => $console['readiness'],
        ]);
    }
}
