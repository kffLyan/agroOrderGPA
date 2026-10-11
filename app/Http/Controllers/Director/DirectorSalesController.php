<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Support\DirectorSalesData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DirectorSalesController extends Controller
{
    public function __invoke(Request $request): View
    {
        $sales = DirectorSalesData::for($request->user());

        return view('director.sales', [
            'operator' => $sales['operator'],
            'salesHeader' => $sales['header'],
            'scopes' => $sales['scopes'],
            'trend' => $sales['trend'],
            'channels' => $sales['channels'],
            'portfolio' => $sales['portfolio'],
            'settlement' => $sales['settlement'],
        ]);
    }
}
