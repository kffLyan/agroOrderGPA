<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Support\CoordinatorDispatchData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DispatchController extends Controller
{
    /**
     * Tampilkan Konsol Surat Jalan & Dispatch Armada Logistik.
     */
    public function index(Request $request): View
    {
        $dispatch = CoordinatorDispatchData::for($request->user());
        $viewName = view()->exists('koordinator.dispatch') ? 'koordinator.dispatch' : 'coordinator.dispatch';

        return view($viewName, [
            'operator' => $dispatch['operator'],
            'dispatchData' => $dispatch,
        ]);
    }
}
