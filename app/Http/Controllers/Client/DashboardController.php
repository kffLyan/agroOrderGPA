<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Support\ClientDashboardData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();

        return view('client.dashboard', [
            'client' => ClientDashboardData::client($user),
            'contract' => ClientDashboardData::contract(),
            'credit' => ClientDashboardData::credit(),
            'pipeline' => ClientDashboardData::pipeline(),
            'harvest' => ClientDashboardData::harvest(),
            'sla' => ClientDashboardData::sla(),
            'invoice' => ClientDashboardData::invoice(),
            'shipment' => ClientDashboardData::shipment(),
            'orders' => ClientDashboardData::orders(),
            'reorder' => ClientDashboardData::reorder(),
            'syncedAt' => Carbon::now('Asia/Jakarta'),
        ]);
    }
}
