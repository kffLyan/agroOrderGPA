<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Support\ClientCartData;
use App\Support\ClientCatalogData;
use App\Support\ClientDashboardData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __invoke(): View
    {
        return view('client.cart', [
            'client' => ClientDashboardData::client(Auth::user()),
            'draft' => ClientCartData::draft(),
            'warning' => ClientCartData::warning(),
            'itemsSection' => ClientCartData::itemsSection(),
            'demoLines' => ClientCartData::demoLines(),
            'commodities' => ClientCatalogData::commodities(),
            'delivery' => ClientCartData::delivery(),
            'docks' => ClientCartData::docks(),
            'receiving' => ClientCartData::receiving(),
            'driverNote' => ClientCartData::driverNote(),
            'summary' => ClientCartData::summary(),
            'paymentMethods' => ClientCartData::paymentMethods(),
            'guarantee' => ClientCartData::guarantee(),
        ]);
    }
}
