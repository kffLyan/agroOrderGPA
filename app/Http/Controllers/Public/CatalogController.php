<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\PublicCatalogData;
use App\Support\PublicHomeData;
use Illuminate\Contracts\View\View;

class CatalogController extends Controller
{
    public function __invoke(): View
    {
        return view('public.catalog', [
            'statusBar' => PublicHomeData::statusBar(),
            'navigation' => PublicHomeData::navigation(),
            'breadcrumb' => PublicCatalogData::breadcrumb(),
            'hero' => PublicCatalogData::hero(),
            'toolbar' => PublicCatalogData::toolbar(),
            'commodities' => PublicCatalogData::commodities(),
            'contract' => PublicCatalogData::contract(),
            'validation' => PublicCatalogData::validation(),
            'cart' => PublicCatalogData::cart(),
            'rules' => PublicCatalogData::rules(),
            'footer' => PublicHomeData::footer(),
        ]);
    }
}
