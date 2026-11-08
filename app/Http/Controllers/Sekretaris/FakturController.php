<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Http\Resources\Sekretaris\FakturKlienResource;
use App\Http\Resources\Sekretaris\BukuBesarFakturResource;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Controller penerbitan faktur penagihan dan monitoring tempo untuk Sekretaris
class FakturController extends Controller
{
    // Menampilkan buku besar faktur penagihan klien B2B, status pelunasan, dan kalkulasi tempo TOP
    public function index(Request $request): View
    {
        // Mengambil data klien B2B beserta pesanan sah yang telah siap kirim, dalam perjalanan, atau selesai
        $clientsQuery = User::where('client_type', 'B2B_KONTRAK')
            ->orWhere('role', 'KLIEN')
            ->with(['orders' => fn($orderQuery) => $orderQuery->whereIn('status', ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'])->with(['orderItems.product', 'invoice.orders'])])
            ->get();

        if ($clientsQuery->isEmpty()) {
            $clientsQuery = User::where('role', 'KLIEN')->get();
        }

        $clients = FakturKlienResource::collection($clientsQuery)->resolve();

        // Mengambil seluruh data faktur yang tercatat beserta rincian user dan status pelunasannya
        $invoices = Invoice::with(['user', 'orders.orderItems', 'payments'])->latest()->get();

        $receivableTotal = (float) $invoices->where('status', '!=', 'PAID')->sum('grand_total');
        $paidTotal       = (float) $invoices->where('status', 'PAID')->sum('grand_total');

        $today = now()->startOfDay();
        $dueSoonTotal = (float) $invoices->filter(function ($invoice) use ($today) {
            if ($invoice->status === 'PAID') {
                return false;
            }
            $dueDate = $invoice->due_date ? Carbon::parse($invoice->due_date)->startOfDay() : $today->copy()->addDays(30);
            $diffDays = (int) round($today->diffInDays($dueDate, false));
            return $diffDays >= 0 && $diffDays <= 7;
        })->sum('grand_total');

        $overdueTotal = (float) $invoices->filter(function ($invoice) use ($today) {
            if ($invoice->status === 'PAID') {
                return false;
            }
            $dueDate = $invoice->due_date ? Carbon::parse($invoice->due_date)->startOfDay() : $today->copy()->addDays(30);
            return $today->gt($dueDate);
        })->sum('grand_total');

        $ledgerRows = BukuBesarFakturResource::collection($invoices)->resolve();

        return view('sekretaris.faktur', [
            'clients'         => $clients,
            'ledgerRows'      => $ledgerRows,
            'receivableTotal' => $receivableTotal,
            'dueSoonTotal'    => $dueSoonTotal,
            'overdueTotal'    => $overdueTotal,
            'paidTotal'       => $paidTotal,
        ]);
    }

    // Menerbitkan faktur konsolidasi resmi yang menggabungkan beberapa Surat Jalan sah terpilih
    public function store(Request $request)
    {
        $request->validate([
            'client_id'   => 'required|exists:users,id',
            'surat_jalan' => 'required|array|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $clientId = (int) $request->input('client_id');
            $sjList = (array) $request->input('surat_jalan');

            $client = User::with('contracts')->findOrFail($clientId);

            // Ambil seluruh pesanan yang nomor surat jalannya dipilih
            $orders = Order::with(['orderItems', 'payments'])
                ->where('user_id', $clientId)
                ->where(function ($query) use ($sjList) {
                    $query->whereIn('surat_jalan_number', $sjList)
                        ->orWhereIn('id', $sjList);
                })
                ->lockForUpdate()
                ->get();

            if ($orders->isEmpty()) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ditemukan surat jalan yang valid untuk diterbitkan faktur.',
                    ], 422);
                }
                return back()->withErrors(['error' => 'Tidak ditemukan surat jalan yang valid untuk diterbitkan faktur.']);
            }

            // Hitung subtotal dan grand total gabungan dari semua pesanan yang dipilih
            $combinedSubtotal = (float) $orders->sum(fn ($o) => (float) ($o->subtotal ?: $o->orderItems->sum(fn ($item) => (float) $item->subtotal)));
            if ($combinedSubtotal <= 0) {
                $combinedSubtotal = (float) $orders->sum('grand_total');
            }
            $combinedGrandTotal = (float) $orders->sum('grand_total');

            // Tentukan TOP dan jatuh tempo berdasarkan kontrak aktif
            $contract = $client->contracts->firstWhere('status', 'ACTIVE');
            $topDays = $contract ? (int) $contract->top_days : 30;
            $dueDate = now()->addDays($topDays)->toDateString();

            $periodStart = $orders->min('created_at')?->toDateString() ?: now()->toDateString();
            $periodEnd = $orders->max('target_delivery_date') ?: now()->toDateString();

            // Generate nomor faktur baru yang unik
            $orderService = app(\App\Services\OrderService::class);
            $newInvoiceNumber = $orderService->generateInvoiceNumber();

            // Catat invoice_id lama sebelum diperbarui
            $oldInvoiceIds = $orders->pluck('invoice_id')->filter()->unique()->all();

            // Buat Faktur Konsolidasi baru di database
            $newInvoice = Invoice::create([
                'invoice_number'  => $newInvoiceNumber,
                'user_id'         => $clientId,
                'period_start'    => $periodStart,
                'period_end'      => $periodEnd,
                'subtotal_amount' => $combinedSubtotal,
                'grand_total'     => $combinedGrandTotal,
                'due_date'        => $dueDate,
                'status'          => 'UNPAID',
                'pdf_file_path'   => 'invoices/' . $newInvoiceNumber . '.pdf',
            ]);

            // Hubungkan semua pesanan terpilih ke faktur konsolidasi baru
            foreach ($orders as $order) {
                $order->invoice_id = $newInvoice->id;
                $order->save();
            }

            // Alihkan pembayaran ke faktur baru
            Payment::whereIn('order_id', $orders->pluck('id'))->update([
                'invoice_id' => $newInvoice->id,
            ]);

            // Hapus placeholder pembayaran lama yang belum pernah diajukan bukti jika sudah ada pembayaran riil yang diajukan
            $hasSubmittedPayment = Payment::whereIn('order_id', $orders->pluck('id'))
                ->where(function ($q) {
                    $q->whereNotNull('proof_url')->where('proof_url', '!=', '')
                      ->orWhereNotNull('paid_at');
                })
                ->exists();

            if ($hasSubmittedPayment) {
                Payment::whereIn('order_id', $orders->pluck('id'))
                    ->whereNull('proof_url')
                    ->whereNull('paid_at')
                    ->delete();
            }

            // Hapus placeholder faktur individual lama yang kini sudah kosong
            foreach ($oldInvoiceIds as $oldId) {
                if ($oldId === $newInvoice->id) {
                    continue;
                }
                $oldInv = Invoice::with('orders')->find($oldId);
                if ($oldInv && $oldInv->orders->isEmpty()) {
                    Payment::where('invoice_id', $oldId)->update(['invoice_id' => $newInvoice->id]);
                    $oldInv->delete();
                }
            }

            $successMsg = "Faktur konsolidasi {$newInvoice->invoice_number} berhasil diterbitkan untuk {$orders->count()} Surat Jalan (Total: Rp " . number_format($combinedGrandTotal, 0, ',', '.') . ").";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'        => true,
                    'message'        => $successMsg,
                    'invoice_number' => $newInvoice->invoice_number,
                    'invoice_id'     => $newInvoice->id,
                    'redirect'       => route('sekretaris.faktur'),
                ]);
            }

            return redirect()->route('sekretaris.faktur')->with('success', $successMsg);
        });
    }

    // Mengekspor rekapitulasi buku besar faktur penagihan dan piutang B2B ke format CSV untuk arsip akuntansi
    public function exportCsv(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Laporan_Faktur_Tagihan_GPA_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBFsep=;\r\n");

            fputcsv($handle, ['KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)'], ';');
            fputcsv($handle, ['BUKU BESAR FAKTUR PENAGIHAN & MONITORING TEMPO PEMBAYARAN (TOP)'], ';');
            fputcsv($handle, ['WAKTU CETAK', date('d/m/Y H:i') . ' WIB'], ';');
            fputcsv($handle, [], ';');
            fputcsv($handle, [
                'No. Faktur',
                'Tanggal Terbit',
                'Entitas Klien / Mitra B2B',
                'Nomor Kontrak',
                'Jumlah SJ Terlampir',
                'Total Tagihan (Rp)',
                'Skema Tempo',
                'Jatuh Tempo',
                'Status Tagihan',
            ], ';');

            $invoices = Invoice::with(['user.contracts', 'orders'])->latest()->get();
            foreach ($invoices as $invoice) {
                $clientName = $invoice->user?->company_name ?: ($invoice->user?->name ?: 'Mitra Klien');
                $activeContract = $invoice->user?->contracts ? $invoice->user->contracts->firstWhere('status', 'ACTIVE') : null;
                $contractNo = $activeContract?->contract_number ?: 'Non-Kontrak (Reguler)';
                $dueDate = $invoice->due_date ?: now()->addDays(30);

                fputcsv($handle, [
                    $invoice->invoice_number,
                    $invoice->created_at ? $invoice->created_at->format('d/m/Y') : '-',
                    $clientName,
                    $contractNo,
                    max(1, $invoice->orders->count()),
                    (float) $invoice->grand_total,
                    'Tempo 30 Hari',
                    $dueDate->format('d/m/Y'),
                    $invoice->status === 'PAID' ? 'Lunas' : ($invoice->status === 'OVERDUE' ? 'Jatuh Tempo' : 'Belum Lunas'),
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }
}
