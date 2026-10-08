<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\PublicHomeData;
use App\Support\PublicSupplyChainData;
use Illuminate\Contracts\View\View;

class SupplyChainController extends Controller
{
    public function __invoke(): View
    {
        return view('public.supply-chain', [
            'statusBar' => PublicHomeData::statusBar(),
            'navigation' => PublicHomeData::navigation(),
            'hero' => PublicSupplyChainData::hero(),
            'metrics' => PublicSupplyChainData::headlineMetrics(),
            'nodes' => PublicSupplyChainData::nodes(),
            'stages' => PublicSupplyChainData::stages(),
            'qualityControl' => PublicSupplyChainData::qualityControl(),
            'returnProtocol' => PublicSupplyChainData::returnProtocol(),
            'infrastructure' => PublicSupplyChainData::infrastructure(),
            'callToAction' => PublicSupplyChainData::callToAction(),
            'footer' => PublicHomeData::footer(),
        ]);
    }
}
