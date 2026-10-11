<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Support\CoordinatorDispatchData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CoordinatorDispatchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $dispatch = CoordinatorDispatchData::for($request->user());

        return view('coordinator.dispatch', [
            'operator' => $dispatch['operator'],
            'dispatchData' => $dispatch,
        ]);
    }
}
