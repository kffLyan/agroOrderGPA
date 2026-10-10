<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Support\ClientDashboardData;
use App\Support\ClientOrdersData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
    public function __invoke(): View
    {
        return view('client.orders', [
            'client' => ClientDashboardData::client(Auth::user()),
            'header' => ClientOrdersData::header(),
            'statusTabs' => ClientOrdersData::statusTabs(),
            'filters' => ClientOrdersData::filters(),
            'columns' => ClientOrdersData::columns(),
            'orders' => ClientOrdersData::orders(),
            'integrity' => ClientOrdersData::integrity(),
            'metrics' => ClientOrdersData::metrics(),
            'pagination' => ClientOrdersData::pagination(),
        ]);
    }
}
