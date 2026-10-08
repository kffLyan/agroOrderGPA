<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\PublicHomeData;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('public.home', [
            'statusBar' => PublicHomeData::statusBar(),
            'navigation' => PublicHomeData::navigation(),
            'hero' => PublicHomeData::hero(),
            'sop' => PublicHomeData::sop(),
            'catalog' => PublicHomeData::catalog(),
            'governance' => PublicHomeData::governance(),
            'partners' => PublicHomeData::partners(),
            'sectors' => PublicHomeData::sectors(),
            'gallery' => PublicHomeData::gallery(),
            'footer' => PublicHomeData::footer(),
        ]);
    }
}
