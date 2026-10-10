<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Support\DirectorDashboardData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DirectorDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $dashboard = DirectorDashboardData::for($request->user());

        return view('director.dashboard', [
            'operator' => $dashboard['operator'],
            'header' => $dashboard['header'],
            'kpis' => $dashboard['kpis'],
            'weekly' => $dashboard['weekly'],
            'commodities' => $dashboard['commodities'],
            'channels' => $dashboard['channels'],
            'receivables' => $dashboard['receivables'],
        ]);
    }
}
