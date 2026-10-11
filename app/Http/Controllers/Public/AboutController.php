<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\PublicAboutData;
use App\Support\PublicHomeData;
use Illuminate\Contracts\View\View;

class AboutController extends Controller
{
    public function __invoke(): View
    {
        return view('public.about', [
            'statusBar' => PublicHomeData::statusBar(),
            'navigation' => PublicHomeData::navigation(),
            'hero' => PublicAboutData::hero(),
            'stats' => PublicAboutData::stats(),
            'pillars' => PublicAboutData::pillars(),
            'commodities' => PublicAboutData::commodities(),
            'legalities' => PublicAboutData::legalities(),
            'cycle' => PublicAboutData::cycle(),
            'callToAction' => PublicAboutData::callToAction(),
            'footer' => PublicHomeData::footer(),
        ]);
    }
}
