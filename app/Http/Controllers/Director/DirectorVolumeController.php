<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Support\DirectorVolumeData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DirectorVolumeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $volume = DirectorVolumeData::for($request->user());

        return view('director.volume', [
            'operator' => $volume['operator'],
            'volumeHeader' => $volume['header'],
            'cards' => $volume['cards'],
            'commodities' => $volume['commodities'],
            'trend' => $volume['trend'],
            'allocations' => $volume['allocations'],
        ]);
    }
}
