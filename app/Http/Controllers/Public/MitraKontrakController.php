<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\HarvestBatch;
use App\Models\User;
use Illuminate\Contracts\View\View;

class MitraKontrakController extends Controller
{
    public function __invoke(): View
    {
        $clients = User::where('role', 'KLIEN')
            ->where('client_type', 'B2B_KONTRAK')
            ->with(['contracts' => function ($query) {
                $query->where('status', 'ACTIVE')->with('product');
            }])
            ->get();

        $poktan = HarvestBatch::select('supplier_name')
            ->distinct()
            ->whereNotNull('supplier_name')
            ->get();

        return view('public.mitra-kontrak', compact('clients', 'poktan'));
    }
}

