<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Ringkasan & Operasional Klien (Klien Reguler & B2B Kontrak).
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $isContractClient = ($user->client_type === 'B2B_KONTRAK');

        // 1. Data Kontrak Aktif Klien & Pengajuan Pending
        $contract = Contract::with('product')
            ->where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->first();

        $pendingContract = Contract::with('product')
            ->where('user_id', $user->id)
            ->whereIn('status', ['PENDING', 'PENDING_APPROVAL'])
            ->latest()
            ->first();

        // 2. Plafon Kredit & Tempo (Top 30D)
        $creditLimit = 0.0;
        if ($contract && $contract->fixed_price_per_kg && $contract->committed_volume_per_cycle) {
            $creditLimit = (float) ($contract->fixed_price_per_kg * $contract->committed_volume_per_cycle * 2);
        }

        $unpaidInvoicesSum = (float) Invoice::where('user_id', $user->id)
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->sum('grand_total');

        $availableCredit = max(0, $creditLimit - $unpaidInvoicesSum);
        $creditPercent = $creditLimit > 0 ? ($availableCredit / $creditLimit) * 100 : 100.0;

        // 3. Pesanan In-Flight Pipeline
        $activeOrders = Order::with(['items.product', 'driver', 'invoice'])
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['SELESAI', 'SELESAI_CATATAN', 'BATAL'])
            ->latest()
            ->get();

        $pipelineCount = $activeOrders->count();
        $pipelineValue = (float) $activeOrders->sum('grand_total');

        $stagesCount = [
            'verifikasi' => $activeOrders->where('status', 'MENUNGGU_VERIFIKASI')->count(),
            'bayar'      => $activeOrders->where('status', 'MENUNGGU_PEMBAYARAN')->count(),
            'lunas'      => $activeOrders->where('status', 'LUNAS')->count(),
            'kemas'      => $activeOrders->where('status', 'SIAP_KIRIM')->count(),
            'kirim'      => $activeOrders->where('status', 'DALAM_PENGIRIMAN')->count(),
        ];

        // 4. Serapan Kuota Panen (Bulan Ini)
        $monthStart = Carbon::now()->startOfMonth();
        $deliveredOrders = Order::with('items.product')
            ->where('user_id', $user->id)
            ->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])
            ->where('created_at', '>=', $monthStart)
            ->get();

        $totalKgHarvest = 0;
        $commodityBreakdown = [];

        foreach ($deliveredOrders as $order) {
            foreach ($order->items as $item) {
                $qty = (float) $item->ordered_qty;
                $totalKgHarvest += $qty;
                $pName = $item->product?->name ?: 'Komoditas';
                $commodityBreakdown[$pName] = ($commodityBreakdown[$pName] ?? 0) + $qty;
            }
        }

        $targetKg = 0;
        if ($contract && $contract->committed_volume_per_cycle) {
            $targetKg = (float) $contract->committed_volume_per_cycle * 4;
        }

        $harvestPercent = $targetKg > 0 ? min(100.0, ($totalKgHarvest / $targetKg) * 100) : 0.0;

        // 5. Tagihan & Faktur Ringkasan (Pemberitahuan)
        $latestInvoice = Invoice::where('user_id', $user->id)
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->latest('due_date')
            ->first();

        // 6. Live Tracking (Pengiriman Berjalan)
        $inTransitOrder = Order::with(['items.product', 'driver'])
            ->where('user_id', $user->id)
            ->where('status', 'DALAM_PENGIRIMAN')
            ->latest('departure_time')
            ->first();

        // 7. In-Flight Orders List untuk tabel
        $recentOrders = Order::with(['items.product', 'driver'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(6)
            ->get();

        // 8. Quick Reorder Komoditas (Kontrak B2B)
        $products = Product::where('is_active', true)->take(4)->get();
        $allAvailableProducts = Product::where('is_active', true)->orderBy('name')->get();

        // 9. Bukti Pembayaran Ditolak yang Membutuhkan Tindakan Klien
        $rejectedPayment = Payment::with(['invoice', 'order', 'verifier'])
            ->where(function ($query) use ($user) {
                $query->whereHas('invoice', fn ($q) => $q->where('user_id', $user->id))
                    ->orWhereHas('order', fn ($q) => $q->where('user_id', $user->id));
            })
            ->whereIn('status', ['DITOLAK', 'REJECTED'])
            ->latest('id')
            ->first();

        if ($rejectedPayment) {
            $hasSuccessor = Payment::where(function ($q) use ($rejectedPayment) {
                if ($rejectedPayment->invoice_id) {
                    $q->where('invoice_id', $rejectedPayment->invoice_id);
                } elseif ($rejectedPayment->order_id) {
                    $q->where('order_id', $rejectedPayment->order_id);
                }
            })
            ->where('id', '>', $rejectedPayment->id)
            ->whereIn('status', ['MENUNGGU_VERIFIKASI', 'LUNAS', 'PAID'])
            ->exists();

            if ($hasSuccessor) {
                $rejectedPayment = null;
            }
        }

        return view('klien.dashboard', [
            'isContractClient'     => $isContractClient,
            'contract'             => $contract,
            'pendingContract'      => $pendingContract,
            'syncedAt'             => Carbon::now(),
            'creditLimit'          => $creditLimit,
            'availableCredit'      => $availableCredit,
            'creditPercent'        => $creditPercent,
            'pipelineCount'        => $pipelineCount,
            'pipelineValue'        => $pipelineValue,
            'stagesCount'          => $stagesCount,
            'totalKgHarvest'       => $totalKgHarvest,
            'targetKg'             => $targetKg,
            'harvestPercent'       => $harvestPercent,
            'commodityBreakdown'   => $commodityBreakdown,
            'latestInvoice'        => $latestInvoice,
            'inTransitOrder'       => $inTransitOrder,
            'recentOrders'         => $recentOrders,
            'products'             => $products,
            'allAvailableProducts' => $allAvailableProducts,
            'rejectedPayment'      => $rejectedPayment,
        ]);
    }
}
