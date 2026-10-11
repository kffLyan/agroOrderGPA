<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Support\SecretaryReportData;
use Illuminate\Contracts\View\View;

class SecretaryReportController extends Controller
{
    public function __invoke(): View
    {
        $console = SecretaryReportData::for(auth()->user());

        return view('staff.report', [
            'operator' => $console['operator'],
            'heading' => $console['heading'],
            'filters' => $console['filters'],
            'metrics' => $console['metrics'],
            'standards' => $console['standards'],
            'tabs' => $console['tabs'],
            'journal' => $console['journal'],
            'deviation' => $console['deviation'],
            'validation' => $console['validation'],
            'seal' => $console['seal'],
        ]);
    }
}
