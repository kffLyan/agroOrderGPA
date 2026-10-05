<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Support\DirectorReportData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DirectorReportController extends Controller
{
    public function __invoke(Request $request): View
    {
        $report = DirectorReportData::for($request->user());

        return view('director.report', [
            'operator' => $report['operator'],
            'lock' => $report['lock'],
            'filters' => $report['filters'],
            'cards' => $report['cards'],
            'commodities' => $report['commodities'],
            'clients' => $report['clients'],
            'audit' => $report['audit'],
            'seal' => $report['seal'],
        ]);
    }
}
