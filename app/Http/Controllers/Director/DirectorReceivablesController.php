<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Support\DirectorReceivablesData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DirectorReceivablesController extends Controller
{
    public function __invoke(Request $request): View
    {
        $receivables = DirectorReceivablesData::for($request->user());

        return view('director.receivables', [
            'operator' => $receivables['operator'],
            'header' => $receivables['header'],
            'cards' => $receivables['cards'],
            'aging' => $receivables['aging'],
            'ledger' => $receivables['ledger'],
            'policy' => $receivables['policy'],
            'verification' => $receivables['verification'],
        ]);
    }
}
