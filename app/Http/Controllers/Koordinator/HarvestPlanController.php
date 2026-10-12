<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Support\CoordinatorHarvestPlanData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HarvestPlanController extends Controller
{
    /**
     * Tampilkan Konsol Rencana Panen, Sortir, Sanitasi Krat & Pre-Cooling.
     */
    public function index(Request $request): View
    {
        $harvestPlan = CoordinatorHarvestPlanData::for($request->user());
        $viewName = view()->exists('koordinator.harvest-plan') ? 'koordinator.harvest-plan' : 'coordinator.harvest-plan';

        return view($viewName, [
            'operator' => $harvestPlan['operator'],
            'harvestPlanData' => $harvestPlan,
        ]);
    }
}
