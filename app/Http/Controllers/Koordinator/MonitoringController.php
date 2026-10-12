<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Support\CoordinatorMonitoringData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    /**
     * Tampilkan Konsol Monitoring & Validasi Proof of Delivery (PoD).
     */
    public function index(Request $request): View
    {
        $monitoring = CoordinatorMonitoringData::for($request->user());
        $viewName = view()->exists('koordinator.monitoring') ? 'koordinator.monitoring' : 'coordinator.monitoring';

        return view($viewName, [
            'operator' => $monitoring['operator'],
            'monitoringData' => $monitoring,
        ]);
    }
}
