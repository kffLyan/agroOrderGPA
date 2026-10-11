<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Support\SecretaryInventoryData;
use Illuminate\Contracts\View\View;

class SecretaryInventoryController extends Controller
{
    public function __invoke(): View
    {
        $console = SecretaryInventoryData::for(auth()->user());

        return view('staff.inventory', [
            'operator' => $console['operator'],
            'heading' => $console['heading'],
            'actions' => $console['actions'],
            'ruleEngine' => $console['ruleEngine'],
            'metrics' => $console['metrics'],
            'filters' => $console['filters'],
            'ledger' => $console['ledger'],
            'activity' => $console['activity'],
            'readiness' => $console['readiness'],
        ]);
    }
}
