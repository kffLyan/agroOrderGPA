<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Http\Requests\Armada\SimpanBuktiTerimaRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ArmadaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Controller bukti serah terima digital (PoD) untuk Armada
class BuktiTerimaController extends Controller
{
    public function __construct(
        protected ArmadaService $armadaService
    ) {}

    // Menampilkan konsol input bukti serah terima (PoD), tanda tangan digital, dan komplain retur bagi supir
    public function index(Request $request): View
    {
        $user = Auth::user();
        $targetOrderId = $request->query('order_id') ? (int) $request->query('order_id') : null;

        // Validasi keamanan: cegah supir lain membuka lembar PoD pesanan milik supir lain via query param
        if ($targetOrderId) {
            $checkOrder = Order::find($targetOrderId);
            if ($checkOrder && $checkOrder->driver_id && $checkOrder->driver_id !== $user->id) {
                abort(403, 'Akses Ditolak: Bukti serah terima ini ditugaskan kepada supir armada lain.');
            }
        }

        $currentOrder = $this->armadaService->resolveCurrentDeliveryOrder($targetOrderId, $user);
        $plate = $currentOrder?->vehicle_plate_number ?: '—';

        return view('armada.bukti-terima', [
            'currentOrder' => $currentOrder,
            'plate'        => $plate,
            'activeNav'    => 'pod',
        ]);
    }

    // Mengunggah foto bukti serah terima, mencatat bobot retur komoditas, dan menyelesaikan status pengiriman pesanan
    public function store(SimpanBuktiTerimaRequest $request, Order $order): RedirectResponse
    {
        $user = Auth::user();

        // Mencegah akses tidak sah (Anti-IDOR) jika pesanan telah ditugaskan kepada supir armada lain
        if ($order->driver_id && $order->driver_id !== $user->id) {
            abort(403, 'Akses Ditolak: Pesanan ini ditugaskan kepada supir armada lain.');
        }

        $condition = $request->input('condition', 'clear');
        $receiverName = $request->input('received_by_name');

        $uploadedPhotos = [];
        if ($request->hasFile('manifest_photo')) {
            $file = $request->file('manifest_photo');
            $filename = 'pod_' . $order->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pod'), $filename);
            $uploadedPhotos[] = 'uploads/pod/' . $filename;
        } elseif ($request->hasFile('pod_photo')) {
            $file = $request->file('pod_photo');
            $filename = 'pod_' . $order->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pod'), $filename);
            $uploadedPhotos[] = 'uploads/pod/' . $filename;
        }

        if ($request->hasFile('dock_photo')) {
            $file = $request->file('dock_photo');
            $filename = 'dock_' . $order->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pod'), $filename);
            $uploadedPhotos[] = 'uploads/pod/' . $filename;
        }

        $hasReturn = false;
        if ($request->has('returns') && is_array($request->input('returns'))) {
            foreach ($request->input('returns') as $itemId => $returnData) {
                $returnedWeight = (float) ($returnData['returned_weight'] ?? 0);
                $returnReason = $returnData['return_reason'] ?? null;

                if ($returnedWeight > 0) {
                    $hasReturn = true;
                    $order->orderItems()->where('id', $itemId)->update([
                        'returned_weight' => $returnedWeight,
                        'return_reason'   => $returnReason,
                    ]);
                }
            }
        }

        // Menentukan status akhir pesanan (SELESAI atau SELESAI_CATATAN) berdasarkan ada/tidaknya retur barang
        $order->status = ($condition === 'discrepancy' || $hasReturn) ? 'SELESAI_CATATAN' : 'SELESAI';
        $order->arrival_time = now();
        if ($receiverName) {
            $order->received_by_name = $receiverName;
        }
        if (!empty($uploadedPhotos)) {
            $order->pod_photo_url = implode(',', $uploadedPhotos);
        }
        if (!$order->driver_id && $user) {
            $order->driver_id = $user->id;
        }
        $order->save();

        // Skema A (Khusus Mitra B2B): Otomatis sesuaikan grand_total faktur (Credit Note) jika pesanan memiliki faktur berjalan
        if ($hasReturn && $order->invoice_id) {
            $invoice = $order->invoice;
            if ($invoice && !in_array($invoice->status, ['PAID'])) {
                $client = $order->user;
                $isB2B = ($client?->client_type === 'B2B_KONTRAK' || ($client?->contracts && $client->contracts->where('status', 'ACTIVE')->isNotEmpty()));
                if ($isB2B) {
                    $totalInvoiceReturns = (float) OrderItem::whereHas('order', function ($q) use ($invoice) {
                        $q->where('invoice_id', $invoice->id);
                    })->sum(DB::raw('returned_weight * unit_price'));

                    $baseSubtotal = (float) ($invoice->subtotal_amount ?: $invoice->grand_total);
                    if ($baseSubtotal > 0) {
                        $invoice->grand_total = max(0, $baseSubtotal - $totalInvoiceReturns);
                        $invoice->save();
                    }
                }
            }
        }

        return redirect()
            ->route('armada.tugas')
            ->with('success', 'Bukti serah terima (PoD) berhasil disimpan! Pesanan #' . $order->order_number . ' telah selesai diantarkan.');
    }
}
