<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Support\CoordinatorHarvestPlanData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CoordinatorHarvestPlanController extends Controller
{
    public function __invoke(Request $request): View
    {
        $harvestPlan = CoordinatorHarvestPlanData::for($request->user());

        return view('coordinator.harvest-plan', [
            'operator' => $harvestPlan['operator'],
            'harvestPlanData' => $harvestPlan,
        ]);
    }
}
