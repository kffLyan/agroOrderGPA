<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Support\ArmadaDispatchData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ArmadaDispatchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $dispatch = ArmadaDispatchData::for($request->user());

        return view('armada.dispatch', [
            'operator' => $dispatch['operator'],
            'header' => $dispatch['header'],
            'intro' => $dispatch['intro'],
            'route' => $dispatch['route'],
            'letter' => $dispatch['letter'],
            'cargo' => $dispatch['cargo'],
            'gatePass' => $dispatch['gatePass'],
            'navigation' => $dispatch['navigation'],
            'activeNav' => 'dispatch',
            'component' => 'armadaDispatch',
            'componentPayload' => [
                'expiry_minutes' => $dispatch['gatePass']['expiry_minutes'],
                'pic_phone' => $dispatch['letter']['pic']['phone'],
                'sj_number' => $dispatch['letter']['number'],
            ],
        ]);
    }
}
