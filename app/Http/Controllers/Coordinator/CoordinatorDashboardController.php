<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Support\CoordinatorDashboardData;
use Illuminate\Contracts\View\View;

class CoordinatorDashboardController extends Controller
{
    public function __invoke(): View
    {
        $console = CoordinatorDashboardData::for(auth()->user());

        return view('coordinator.dashboard', [
            'operator' => $console['operator'],
            'heading' => $console['heading'],
            'metrics' => $console['metrics'],
            'supply' => $console['supply'],
            'packing' => $console['packing'],
            'gate' => $console['gate'],
            'footer' => $console['footer'],
        ]);
    }
}
