<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Support\CoordinatorStockData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CoordinatorStockController extends Controller
{
    public function __invoke(Request $request): View
    {
        $stock = CoordinatorStockData::for($request->user());

        return view('coordinator.stock', [
            'operator' => $stock['operator'],
            'stockData' => $stock,
        ]);
    }
}
