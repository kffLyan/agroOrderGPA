<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Support\ClientDashboardData;
use App\Support\ClientDocumentsData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DocumentsController extends Controller
{
    public function __invoke(): View
    {
        return view('client.documents', [
            'client' => ClientDashboardData::client(Auth::user()),
            'header' => ClientDocumentsData::header(),
            'metrics' => ClientDocumentsData::metrics(),
            'tabs' => ClientDocumentsData::tabs(),
            'periods' => ClientDocumentsData::periods(),
            'columns' => ClientDocumentsData::columns(),
            'invoices' => ClientDocumentsData::invoices(),
            'detail' => ClientDocumentsData::detail(),
            'bank' => ClientDocumentsData::bank(),
            'proofMenu' => ClientDocumentsData::proofMenu(),
            'assurance' => ClientDocumentsData::assurance(),
            'pagination' => ClientDocumentsData::pagination(),
            'archiveCounts' => ClientDocumentsData::archiveCounts(),
        ]);
    }
}
