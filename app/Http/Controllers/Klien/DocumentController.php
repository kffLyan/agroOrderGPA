<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    /**
     * Menampilkan pusat dokumen, faktur konsolidasi, dan rekap tagihan tempo
     */
    public function index(Request $request)
    {
        $userId = Auth::guard('web')->id();
        $user = Auth::guard('web')->user();

        $rawInvoices = Invoice::where('user_id', $userId)
            ->with(['orders.orderItems', 'payments'])
            ->orderBy('due_date', 'desc')
            ->get();

        return view('klien.documents', compact('rawInvoices', 'user'));
    }

    /**
     * Tampilan Siap Cetak Dokumen Rekapitulasi Faktur & Tagihan Resmi (.PDF / Print View)
     */
    public function printRekap(Request $request)
    {
        $userId = Auth::guard('web')->id();
        $user = Auth::guard('web')->user();

        $invoices = Invoice::where('user_id', $userId)
            ->with(['orders.orderItems', 'payments'])
            ->orderBy('due_date', 'desc')
            ->get();

        $totalNetto = (float) $invoices->sum(function ($inv) {
            return $inv->orders->sum(function ($o) {
                return $o->orderItems->sum(fn($i) => $i->actual_net_weight ?? $i->ordered_qty);
            });
        });

        $totalGrand = (float) $invoices->sum('grand_total');
        $unpaidTotal = (float) $invoices->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])->sum('grand_total');
        $paidTotal = (float) $invoices->where('status', 'PAID')->sum('grand_total');

        $docNumber = 'DOC/GPA-INV-REKAP/' . now()->format('Ym') . '/' . str_pad($userId, 4, '0', STR_PAD_LEFT);
        $printDate = now()->translatedFormat('d F Y, H:i') . ' WIB';

        return view('klien.documents.print-rekap', compact(
            'user',
            'invoices',
            'totalNetto',
            'totalGrand',
            'unpaidTotal',
            'paidTotal',
            'docNumber',
            'printDate'
        ));
    }
}
