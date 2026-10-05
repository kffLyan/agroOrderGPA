<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Support\DirectorApprovalData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DirectorApprovalController extends Controller
{
    public function __invoke(Request $request): View
    {
        $approval = DirectorApprovalData::for($request->user());

        return view('director.approval', [
            'operator' => $approval['operator'],
            'header' => $approval['header'],
            'cards' => $approval['cards'],
            'queue' => $approval['queue'],
        ]);
    }
}
