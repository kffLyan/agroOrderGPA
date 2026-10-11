<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Support\ClientCatalogData;
use App\Support\ClientDashboardData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class CatalogController extends Controller
{
    public function __invoke(): View
    {
        return view('client.catalog', [
            'client' => ClientDashboardData::client(Auth::user()),
            'contract' => ClientCatalogData::contract(),
            'quota' => ClientCatalogData::quota(),
            'credit' => ClientCatalogData::credit(),
            'dispatch' => ClientCatalogData::dispatch(),
            'rulebook' => ClientCatalogData::rulebook(),
            'categories' => ClientCatalogData::categories(),
            'categoryLabels' => ClientCatalogData::categoryLabels(),
            'origins' => ClientCatalogData::origins(),
            'sortOptions' => ClientCatalogData::sortOptions(),
            'commodities' => ClientCatalogData::commodities(),
            'adhoc' => ClientCatalogData::adhoc(),
            'reconciliation' => ClientCatalogData::reconciliation(),
        ]);
    }
}
