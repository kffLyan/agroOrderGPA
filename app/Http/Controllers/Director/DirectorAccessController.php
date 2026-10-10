<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Support\DirectorAccessData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DirectorAccessController extends Controller
{
    public function __invoke(Request $request): View
    {
        $access = DirectorAccessData::for($request->user());

        return view('director.access', [
            'operator' => $access['operator'],
            'header' => $access['header'],
            'metrics' => $access['metrics'],
            'matrix' => $access['matrix'],
            'directory' => $access['directory'],
            'policies' => $access['policies'],
        ]);
    }
}
