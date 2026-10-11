<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Support\CoordinatorMonitoringData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CoordinatorMonitoringController extends Controller
{
    public function __invoke(Request $request): View
    {
        $monitoring = CoordinatorMonitoringData::for($request->user());

        return view('coordinator.monitoring', [
            'operator' => $monitoring['operator'],
            'monitoringData' => $monitoring,
        ]);
    }
}
