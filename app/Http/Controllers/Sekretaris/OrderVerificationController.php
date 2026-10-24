<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderVerificationController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Menampilkan antrean pesanan masuk untuk diverifikasi Sekretaris
     */
    public function index(): View
    {
        // Ambil hanya pesanan yang berstatus MENUNGGU_VERIFIKASI (belum diverifikasi)
        $realOrders = Order::with(['user', 'orderItems.product', 'payments'])
            ->where('status', 'MENUNGGU_VERIFIKASI')
            ->latest()
            ->get();

        $mappedOrders = [];
        foreach ($realOrders as $ord) {
            $clientName = $ord->user?->company_name ?: ($ord->user?->name ?: 'Mitra Klien GPA');
            $isContract = ($ord->user?->client_type === 'B2B_KONTRAK');
            $totalQty = (float) $ord->orderItems->sum('ordered_qty');
            $totalVal = (float) $ord->grand_total;

            $itemsList = $ord->orderItems->map(function ($it) {
                return [
                    'name'         => $it->product?->name ?: 'Komoditas Sayur',
                    'weight'       => number_format((float) $it->ordered_qty, 0, ',', '.') . ' kg',
                    'weight_class' => 'text-ink-body',
                    'class'        => 'text-ink font-semibold',
                ];
            })->all();

            $allocations = $ord->orderItems->map(function ($it) {
                $qty = (float) $it->ordered_qty;
                return [
                    'name'         => ($it->product?->name ?: 'Komoditas') . ' Grade A Pilihan',
                    'sku'          => 'Kode: ' . ($it->product?->sku ?: 'GPA-VEG-01') . ' • Toleransi Sortir max 3%',
                    'request'      => number_format($qty, 0, ',', '.'),
                    'request_unit' => 'KG',
                    'system'       => number_format($qty * 1.2, 0, ',', '.'),
                    'system_unit'  => 'KG',
                    'system_class' => 'text-success-deep font-bold',
                    'surplus'      => 'Cadangan Aman +' . number_format($qty * 0.2, 0, ',', '.') . ' kg',
                    'surplus_class'=> 'text-success-deep',
                    'plan'         => [
                        [
                            'label'   => '1. Alokasi Panen Kebun Binaan:',
                            'qty'     => number_format($qty, 0, ',', '.') . ' kg',
                            'percent' => 100,
                            'fill'    => 'bg-success-deep'
                        ],
                    ],
                    'status'       => ['label' => 'Siap Panen', 'icon' => 'lock', 'class' => 'bg-accent text-success-ink'],
                ];
            })->all();

            $firstItem = $ord->orderItems->first();
            $unitPrice = $firstItem ? (float) $firstItem->unit_price : ($totalQty > 0 ? ($totalVal / $totalQty) : 0);

            $clientContract = method_exists($ord->user, 'contracts') ? $ord->user->contracts()->where('status', 'ACTIVE')->first() : null;
            $plafonVal = $clientContract ? (float) ($clientContract->fixed_price_per_kg * $clientContract->committed_volume_per_cycle * 2) : 0;

            $mappedOrders[] = [
                'id'                       => $ord->order_number,
                'order_id'                 => $ord->id,
                'client'                   => $clientName,
                'channel'                  => $ord->order_source === 'WEB_PORTAL' ? 'b2b' : 'wa',
                'meta'                     => ($isContract ? 'Kontrak Mitra B2B' : 'Klien Reguler') . ' • Alamat: ' . ($ord->delivery_address ?: 'Gudang Klien'),
                'entered'                  => 'Masuk: ' . $ord->created_at->diffForHumans(),
                'chip'                     => [
                    'label' => $isContract ? 'KONTRAK B2B' : 'PESANAN REGULER',
                    'class' => $isContract ? 'bg-accent text-success-ink font-bold' : 'bg-surface-pill text-ink-body font-medium',
                ],
                'queue_commodity_label'    => 'Sayur Dipesan:',
                'queue_commodities'        => $itemsList,
                'queue_estimate_label'     => 'Nilai Pesanan Terkunci',
                'queue_estimate'           => 'Rp ' . number_format($totalVal, 0, ',', '.'),
                'queue_estimate_note'      => ['label' => 'Alokasi Siap', 'class' => 'text-success-deep'],
                'queue_status'             => ['label' => 'Stok Kebun Cukup', 'icon' => 'shield', 'class' => 'bg-accent text-success-ink'],
                'source'                   => 'Sumber: ' . ($ord->order_source === 'WEB_PORTAL' ? 'Website Portal Mitra' : 'Pemesanan Manual WhatsApp / Telepon'),
                'po_ref'                   => 'NO. PO: ' . $ord->order_number,
                'order_no'                 => 'Pesanan #' . $ord->order_number,
                'status'                   => [
                    'label' => $ord->status === 'MENUNGGU_PEMBAYARAN' ? 'Invoice Diterbitkan (Menunggu Bayar)' : 'Menunggu Persetujuan Sekretaris',
                    'class' => $ord->status === 'MENUNGGU_PEMBAYARAN' ? 'bg-accent text-success-ink font-bold' : 'bg-warning-soft text-warning-deep font-bold',
                ],
                'created'                  => 'Tanggal: ' . $ord->created_at->format('d M Y, H:i') . ' WIB',
                'identity'                 => [
                    [
                        'label'      => 'Identitas Pembeli',
                        'name'       => $clientName,
                        'code'       => 'ID Klien: CLI-' . str_pad($ord->user_id, 4, '0', STR_PAD_LEFT),
                        'code_class' => 'text-success-deep',
                        'note'       => 'Status: ' . ($isContract ? 'Mitra Kontrak Aktif' : 'Pembeli Reguler'),
                    ],
                    [
                        'label'      => 'Kontak Penerima',
                        'name'       => $ord->user?->name ?: 'Penanggung Jawab',
                        'code'       => ($ord->user?->phone ?: '—') . ' (WhatsApp)',
                        'code_class' => 'text-ink-body',
                        'note'       => 'Email: ' . ($ord->user?->email ?: '—'),
                    ],
                    [
                        'label'      => 'Alamat & Waktu Kirim',
                        'name'       => $ord->delivery_address ?: 'Gudang Penerimaan Klien',
                        'code'       => 'Armada: Mobil Boks Berpendingin',
                        'code_class' => 'text-ink-body',
                        'note'       => 'Jadwal Kirim: ' . ($ord->target_delivery_date ? $ord->target_delivery_date->format('d M Y') : 'Pagi Hari (05:00 WIB)'),
                    ],
                ],
                'supply_status'            => ['label' => 'Pasokan Aman', 'icon' => 'check', 'class' => 'bg-accent text-success-ink font-bold'],
                'allocations'              => $allocations,
                'pricing'                  => [
                    ['label' => 'Harga Satuan per Kg', 'value' => 'Rp ' . number_format($unitPrice, 0, ',', '.'), 'suffix' => '/ kg', 'note' => null, 'highlight' => false],
                    ['label' => 'Total Nilai Pesanan', 'value' => 'Rp ' . number_format($totalVal, 0, ',', '.'), 'suffix' => null, 'note' => null, 'highlight' => false],
                    ['label' => 'Pajak / PPN (0%)', 'value' => 'Rp 0', 'suffix' => null, 'note' => '(Bebas Pajak Produk Pertanian)', 'highlight' => false],
                    ['label' => 'Total Tagihan Invoice', 'value' => 'Rp ' . number_format($totalVal, 0, ',', '.'), 'suffix' => null, 'note' => null, 'highlight' => true],
                ],
                'initial_qty'              => number_format($totalQty, 0, ',', '.') . ' kg',
                'deliveries'               => [],
                'deliveries_total'         => 'Rp ' . number_format($totalVal, 0, ',', '.'),
                'plafon'                   => $plafonVal > 0 ? ('Rp ' . number_format($plafonVal, 0, ',', '.')) : 'Sesuai Ketentuan PO',
            ];
        }

        $b2bCount = count(array_filter($mappedOrders, fn($o) => $o['channel'] === 'b2b'));
        $waCount = count($mappedOrders) - $b2bCount;

        return view('sekretaris.verification', [
            'mappedOrders' => $mappedOrders,
            'b2bCount'     => $b2bCount,
            'waCount'      => $waCount,
        ]);
    }

    /**
     * Menyetujui pesanan, mengunci kuota panen, & menerbitkan Invoice tagihan PDF
     */
    public function approve(Request $request, int $id)
    {
        try {
            $secretaryId = Auth::guard('web')->id();
            $order = $this->orderService->approveOrder($id, $secretaryId);

            $message = "Pesanan {$order->order_number} berhasil disetujui. Kuota stok panen telah dikunci, Invoice tagihan PDF telah diterbitkan, dan status pesanan kini MENUNGGU_PEMBAYARAN.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'      => true,
                    'message'      => $message,
                    'order_id'     => $order->id,
                    'order_number' => $order->order_number,
                    'status'       => $order->status,
                ]);
            }

            return redirect()->route('sekretaris.verification')
                ->with('success', $message);
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Menolak pesanan masuk beserta alasan penolakan
     */
    public function reject(Request $request, int $id)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:255'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan pesanan wajib diisi.',
        ]);

        try {
            $secretaryId = Auth::guard('web')->id();
            $order = $this->orderService->rejectOrder($id, $secretaryId, $request->input('rejection_reason'));

            $message = "Pesanan {$order->order_number} telah ditolak.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'      => true,
                    'message'      => $message,
                    'order_id'     => $order->id,
                    'order_number' => $order->order_number,
                ]);
            }

            return redirect()->route('sekretaris.verification')
                ->with('success', $message);
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
