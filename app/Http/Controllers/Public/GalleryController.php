<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\PublicGalleryData;
use App\Support\PublicHomeData;
use Illuminate\Contracts\View\View;

class GalleryController extends Controller
{
    public function __invoke(): View
    {
        return view('public.gallery', [
            'statusBar' => PublicHomeData::statusBar(),
            'navigation' => PublicHomeData::navigation(),
            'hero' => PublicGalleryData::hero(),
            'metrics' => PublicGalleryData::metrics(),
            'filter' => PublicGalleryData::filter(),
            'summary' => PublicGalleryData::summary(),
            'entries' => PublicGalleryData::entries(),
            'pagination' => PublicGalleryData::pagination(),
            'callToAction' => PublicGalleryData::callToAction(),
            'archiveProfile' => PublicGalleryData::archiveProfile(),
            'footer' => PublicHomeData::footer(),
        ]);
    }
}
