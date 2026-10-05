<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Support\SecretaryDashboardData;
use Illuminate\Contracts\View\View;

class SecretaryDashboardController extends Controller
{
    public function __invoke(): View
    {
        $operator = SecretaryDashboardData::operator(auth()->user());

        return view('staff.dashboard', [
            'operator' => $operator,
            'heading' => SecretaryDashboardData::heading(),
            'toolbar' => SecretaryDashboardData::toolbar(),
            'metrics' => SecretaryDashboardData::metrics(),
            'queue' => SecretaryDashboardData::queue(),
            'pipeline' => SecretaryDashboardData::pipeline(),
            'stock' => SecretaryDashboardData::stock(),
            'alerts' => SecretaryDashboardData::alerts(),
        ]);
    }
}
