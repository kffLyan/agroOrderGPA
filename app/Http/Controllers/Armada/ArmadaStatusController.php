<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Support\ArmadaStatusData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ArmadaStatusController extends Controller
{
    public function __invoke(Request $request): View
    {
        $status = ArmadaStatusData::for($request->user());

        return view('armada.status', [
            'operator' => $status['operator'],
            'header' => $status['header'],
            'vehicle' => $status['vehicle'],
            'driver' => $status['driver'],
            'telemetry' => $status['telemetry'],
            'trip' => $status['trip'],
            'performance' => $status['performance'],
            'support' => $status['support'],
            'closure' => $status['closure'],
            'navigation' => $status['navigation'],
            'activeNav' => 'fleet',
            'component' => 'armadaStatus',
            'componentPayload' => [],
        ]);
    }
}
