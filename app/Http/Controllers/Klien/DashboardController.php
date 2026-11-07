<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    // Menampilkan dashboard ringkasan operasional dan keuangan portal mandiri mitra klien.
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
                $productName = $item->product?->name ?: 'Komoditas';
                $commodityBreakdown[$productName] = ($commodityBreakdown[$productName] ?? 0) + $qty;
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
                $query->whereHas('invoice', fn ($subQuery) => $subQuery->where('user_id', $user->id))
                    ->orWhereHas('order', fn ($subQuery) => $subQuery->where('user_id', $user->id));
            })
            ->whereIn('status', ['DITOLAK', 'REJECTED'])
            ->latest('id')
            ->first();

        if ($rejectedPayment) {
            $hasSuccessor = Payment::where(function ($query) use ($rejectedPayment) {
                if ($rejectedPayment->invoice_id) {
                    $query->where('invoice_id', $rejectedPayment->invoice_id);
                } elseif ($rejectedPayment->order_id) {
                    $query->where('order_id', $rejectedPayment->order_id);
                }
            })
            ->where('id', '>', $rejectedPayment->id)
            ->whereIn('status', ['MENUNGGU_VERIFIKASI', 'LUNAS', 'PAID'])
            ->exists();

            if ($hasSuccessor) {
                $rejectedPayment = null;
            }
        }

        // 10. Format View Models (Prinsip MVC: Controller mengolah Logika & Transformasi Data)
        $contractNumber = $contract ? $contract->contract_number : ($isContractClient ? ('CTR-B2B-' . str_pad($user->id, 4, '0', STR_PAD_LEFT)) : 'REGULER-HARIAN');
        $dockName = 'DOCK UTAMA — ' . ($user->company_name ?: ($user->name ?: 'KLIEN GPA'));
        $creditBarWidth = max(0, min(100, $creditPercent)) . '%';

        $ordersTable = $recentOrders->map(function ($order) {
            $orderedKg = (float) $order->items->sum('ordered_qty');
            $commodities = $order->items->map(function ($item) {
                return ($item->product?->name ?: 'Komoditas') . ' (' . number_format($item->ordered_qty, 0, ',', '.') . ' kg)';
            })->filter()->join(', ');

            $statusLabel = match ($order->status) {
                'MENUNGGU_VERIFIKASI' => 'Menunggu Verifikasi',
                'MENUNGGU_PEMBAYARAN' => 'Menunggu Pembayaran',
                'LUNAS'               => 'Lunas',
                'SIAP_KIRIM'          => 'Siap Kirim',
                'DALAM_PENGIRIMAN'    => 'Dalam Pengiriman',
                'SELESAI'             => 'Selesai',
                'SELESAI_CATATAN'     => 'Selesai (Catatan)',
                'BATAL'               => 'Dibatalkan',
                default               => ucwords(strtolower(str_replace('_', ' ', $order->status))),
            };

            $statusTone = match ($order->status) {
                'LUNAS', 'SELESAI'                   => 'accent',
                'MENUNGGU_VERIFIKASI', 'SIAP_KIRIM'  => 'neutral',
                'MENUNGGU_PEMBAYARAN', 'DALAM_PENGIRIMAN' => 'warning',
                'BATAL'                              => 'neutral',
                default                              => 'neutral',
            };

            return [
                'po'           => $order->order_number,
                'date'         => $order->created_at ? $order->created_at->format('d/m/Y H:i') : '-',
                'commodities'  => $commodities ?: 'Komoditas Sayuran Segar GPA',
                'packaging'    => 'Krat Plastik Higienis Cold-Chain',
                'weight'       => number_format($orderedKg, 1, ',', '.') . ' kg',
                'weight_note'  => $order->surat_jalan_number ? '(Netto Tera)' : '(Estimasi)',
                'scale_note'   => 'Timbangan Digital Tera Hub Lembang',
                'driver'       => $order->driver?->name ?: null,
                'sj'           => $order->surat_jalan_number ?: null,
                'driver_note'  => $order->driver ? ('Supir GPA • ' . ($order->vehicle_plate_number ?: 'Truk Chiller')) : 'Armada belum ditugaskan',
                'status'       => $statusLabel,
                'raw_status'   => $order->status,
                'status_tone'  => $statusTone,
                'url'          => route('klien.pesanan.show', $order->id),
            ];
        })->values()->all();

        // Ambil data kontrak aktif produk secara efisien tanpa N+1 query
        $userContractsMap = $isContractClient 
            ? Contract::where('user_id', $user->id)->where('status', 'ACTIVE')->get()->keyBy('product_id')
            : collect();

        $reorderData = [];
        foreach ($products as $product) {
            $unitPrice = (float) $product->base_price;
            $hasActiveContract = false;
            $contractVolume = 0;
            if ($isContractClient && isset($userContractsMap[$product->id])) {
                $userContract = $userContractsMap[$product->id];
                $hasActiveContract = true;
                if ($userContract->fixed_price_per_kg) {
                    $unitPrice = (float) $userContract->fixed_price_per_kg;
                }
                $contractVolume = (float) ($userContract->committed_volume_per_cycle ?: 2500);
            }
            $moq = (float) ($product->minimum_order ?: 1);
            $rawGrade = $product->grade ?: 'Super A';
            $cleanGrade = preg_match('/^grade\b/i', $rawGrade) ? $rawGrade : ('Grade ' . $rawGrade);

            $reorderData[] = [
                'key'           => (string) $product->id,
                'id'            => $product->id,
                'sku'           => $product->sku ?? ('GPA-' . str_pad((string) $product->id, 3, '0', STR_PAD_LEFT)),
                'name'          => $product->name,
                'grade'         => $cleanGrade,
                'price'         => $unitPrice,
                'is_contract'   => $hasActiveContract,
                'remaining'     => $hasActiveContract ? $contractVolume : 5000,
                'step'          => 1,
                'initial_qty'   => $moq,
                'minimum_order' => $moq,
                'image_path'    => $product->image_path,
                'image_url'     => asset($product->image_path),
            ];
        }

        return view('klien.dashboard', [
            'isContractClient'     => $isContractClient,
            'contract'             => $contract,
            'pendingContract'      => $pendingContract,
            'contractNumber'       => $contractNumber,
            'dockName'             => $dockName,
            'creditBarWidth'       => $creditBarWidth,
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
            'ordersTable'          => $ordersTable,
            'reorderData'          => $reorderData,
            'products'             => $products,
            'allAvailableProducts' => $allAvailableProducts,
            'rejectedPayment'      => $rejectedPayment,
        ]);
    }
}
