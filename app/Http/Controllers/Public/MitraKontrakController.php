<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\PublicHomeData;
use App\Support\PublicMitraKontrakData;
use Illuminate\Contracts\View\View;

class MitraKontrakController extends Controller
{
    public function __invoke(): View
    {
        return view('public.mitra-kontrak', [
            'statusBar' => PublicHomeData::statusBar(),
            'navigation' => PublicHomeData::navigation(),
            'hero' => PublicMitraKontrakData::hero(),
            'clients' => PublicMitraKontrakData::clients(),
            'poktan' => PublicMitraKontrakData::poktan(),
            'onboarding' => PublicMitraKontrakData::onboarding(),
            'callToAction' => PublicMitraKontrakData::callToAction(),
            'templatePks' => PublicMitraKontrakData::templatePks(),
            'disclaimer' => PublicMitraKontrakData::disclaimer(),
            'footer' => PublicHomeData::footer(),
        ]);
    }
}