<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Support\CoordinatorReturnsData;
use Illuminate\Contracts\View\View;

class CoordinatorReturnsController extends Controller
{
    public function __invoke(): View
    {
        $report = CoordinatorReturnsData::for(auth()->user());

        return view('coordinator.returns', [
            'operator' => $report['operator'],
            'report' => $report,
        ]);
    }
}
