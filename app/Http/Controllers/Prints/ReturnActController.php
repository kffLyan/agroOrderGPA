<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Support\ReturnActData;
use Illuminate\Contracts\View\View;

class ReturnActController extends Controller
{
    public function __invoke(): View
    {
        return view('prints.return-act', ReturnActData::document());
    }
}
