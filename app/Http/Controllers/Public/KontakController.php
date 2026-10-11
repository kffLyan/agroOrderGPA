<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\PublicHomeData;
use App\Support\PublicKontakData;
use Illuminate\Contracts\View\View;

class KontakController extends Controller
{
    public function __invoke(): View
    {
        return view('public.kontak', [
            'statusBar' => PublicHomeData::statusBar(),
            'navigation' => PublicHomeData::navigation(),
            'hero' => PublicKontakData::hero(),
            'directory' => PublicKontakData::directory(),
            'form' => PublicKontakData::form(),
            'schematic' => PublicKontakData::schematic(),
            'footer' => PublicHomeData::footer(),
        ]);
    }
}
