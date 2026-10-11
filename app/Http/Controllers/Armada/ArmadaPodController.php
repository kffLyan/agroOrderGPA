<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Support\ArmadaPodData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ArmadaPodController extends Controller
{
    public function __invoke(Request $request): View
    {
        $pod = ArmadaPodData::for($request->user());

        return view('armada.pod', [
            'operator' => $pod['operator'],
            'header' => $pod['header'],
            'target' => $pod['target'],
            'steps' => $pod['steps'],
            'condition_title' => $pod['condition_title'],
            'conditions' => $pod['conditions'],
            'closure' => $pod['closure'],
            'navigation' => $pod['navigation'],
            'activeNav' => 'pod',
            'component' => 'armadaPod',
            'componentPayload' => $pod['steps'],
        ]);
    }
}
