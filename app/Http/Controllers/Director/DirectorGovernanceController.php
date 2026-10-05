<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Support\DirectorGovernanceData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DirectorGovernanceController extends Controller
{
    public function __invoke(Request $request): View
    {
        $governance = DirectorGovernanceData::for($request->user());

        return view('director.governance', [
            'operator' => $governance['operator'],
            'header' => $governance['header'],
            'metrics' => $governance['metrics'],
            'parameters' => $governance['parameters'],
            'audit' => $governance['audit'],
            'controls' => $governance['controls'],
        ]);
    }
}
