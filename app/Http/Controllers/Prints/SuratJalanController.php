<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Support\SuratJalanData;
use Illuminate\Contracts\View\View;

class SuratJalanController extends Controller
{
    public function __invoke(): View
    {
        return view('prints.surat-jalan', SuratJalanData::document());
    }
}
