<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Support\CoordinatorWeighingData;
use Illuminate\Contracts\View\View;

class CoordinatorWeighingController extends Controller
{
    public function __invoke(): View
    {
        $console = CoordinatorWeighingData::for(auth()->user());

        return view('coordinator.weighing', [
            'operator' => $console['operator'],
            'header' => $console['header'],
            'stages' => $console['stages'],
            'rules' => $console['rules'],
            'order' => $console['order'],
            'terminal' => $console['terminal'],
            'grading' => $console['grading'],
            'evidence' => $console['evidence'],
            'reconciliation' => $console['reconciliation'],
            'financial' => $console['financial'],
            'audit' => $console['audit'],
            'actions' => $console['actions'],
        ]);
    }
}
