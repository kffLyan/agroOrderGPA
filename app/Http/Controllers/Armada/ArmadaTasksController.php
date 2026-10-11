<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Support\ArmadaTasksData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ArmadaTasksController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tasks = ArmadaTasksData::for($request->user());

        return view('armada.tasks', [
            'operator' => $tasks['operator'],
            'header' => $tasks['header'],
            'assignment' => $tasks['assignment'],
            'telemetry' => $tasks['telemetry'],
            'manifest' => $tasks['manifest'],
            'filters' => $tasks['filters'],
            'queue_title' => $tasks['queue_title'],
            'queue_compliance' => $tasks['queue_compliance'],
            'drops' => $tasks['drops'],
            'support' => $tasks['support'],
            'navigation' => $tasks['navigation'],
            'activeNav' => 'tasks',
            'component' => 'armadaTasks',
            'componentPayload' => $tasks['filters'],
        ]);
    }
}
