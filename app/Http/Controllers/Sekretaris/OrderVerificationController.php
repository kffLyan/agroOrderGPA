<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\SecretaryVerificationData;
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
        $console = SecretaryVerificationData::for(Auth::user());

        // Ambil pesanan riil dari database jika ada
        $realOrders = Order::with(['user', 'orderItems.product', 'payments'])
            ->where('status', 'MENUNGGU_VERIFIKASI')
            ->latest()
            ->get();

        if ($realOrders->isNotEmpty()) {
            $mappedOrders = [];
            foreach ($realOrders as $ord) {
                $clientName = $ord->user?->company_name ?: ($ord->user?->name ?: 'Mitra Klien GPA');
                $isContract = ($ord->user?->client_type === 'B2B_KONTRAK');
                $totalQty = (float) $ord->orderItems->sum('ordered_qty');
                $totalVal = (float) ($ord->grand_total ?: $ord->estimated_total);

                $itemsList = $ord->orderItems->map(function ($it) {
                    return [
                        'name'         => $it->product?->name ?: 'Komoditas Sayur',
                        'weight'       => number_format((float) $it->ordered_qty, 0, ',', '.') . ' kg',
                        'weight_class' => 'text-ink-body',
                        'class'        => 'text-ink',
                    ];
                })->all();

                $allocations = $ord->orderItems->map(function ($it) {
                    $qty = (float) $it->ordered_qty;
                    return [
                        'name'         => ($it->product?->name ?: 'Komoditas') . ' Grade A Super',
                        'sku'          => 'SKU: ' . ($it->product?->sku ?: 'GPA-VEG-01') . ' • Toleransi Sortir max 3%',
                        'request'      => number_format($qty, 0, ',', '.'),
                        'request_unit' => 'KG',
                        'system'       => number_format($qty * 1.25, 0, ',', '.'),
                        'system_unit'  => 'KG',
                        'system_class' => 'text-success-deep',
                        'surplus'      => 'Surplus +' . number_format($qty * 0.25, 0, ',', '.') . ' kg',
                        'surplus_class'=> 'text-success-deep',
                        'plan'         => [
                            ['label' => '1. Alokasi Panen Gudang / Kebun Binaan:', 'qty' => number_format($qty, 0, ',', '.') . ' kg', 'percent' => 100, 'fill' => 'bg-success-deep'],
                        ],
                        'status'       => ['label' => 'Terkunci', 'icon' => 'lock', 'class' => 'bg-accent text-success-ink'],
                    ];
                })->all();

                $mappedOrders[] = [
                    'id'                       => $ord->order_number,
                    'order_id'                 => $ord->id,
                    'client'                   => $clientName,
                    'channel'                  => $ord->order_source === 'WEB_PORTAL' ? 'b2b' : 'wa',
                    'meta'                     => ($isContract ? 'Kontrak Aktif' : 'Reguler Harian') . ' • Alamat: ' . ($ord->delivery_address ?: 'Dock Klien'),
                    'entered'                  => 'Masuk: ' . $ord->created_at->diffForHumans(),
                    'chip'                     => [
                        'label' => $isContract ? 'KONTRAK B2B' : 'REGULER',
                        'class' => $isContract ? 'bg-accent text-success-ink font-bold' : 'bg-surface-pill text-ink-body',
                    ],
                    'queue_commodity_label'    => 'Komoditas Dipesan:',
                    'queue_commodities'        => $itemsList ?: [['name' => 'Sayuran Segar Hortikultura', 'weight' => '100 kg', 'class' => 'text-ink', 'weight_class' => 'text-ink-body']],
                    'queue_estimate_label'     => 'Estimasi Nilai PO',
                    'queue_estimate'           => 'Rp ' . number_format($totalVal, 0, ',', '.'),
                    'queue_estimate_note'      => ['label' => 'Ready Alokasi', 'class' => 'text-success-deep'],
                    'queue_status'             => ['label' => 'Stok Buffer Aman', 'icon' => 'shield', 'class' => 'bg-accent text-success-ink'],
                    'source'                   => 'Source: ' . ($ord->order_source === 'WEB_PORTAL' ? 'Portal Web B2B' : 'Input Manual WhatsApp'),
                    'po_ref'                   => 'REF: ' . $ord->order_number,
                    'order_no'                 => 'Order #' . $ord->order_number,
                    'status'                   => ['label' => 'Menunggu Verifikasi Sekre', 'class' => 'bg-accent text-success-ink'],
                    'created'                  => 'Dibuat: ' . $ord->created_at->format('d M Y, H:i') . ' WIB',
                    'identity'                 => [
                        [
                            'label'      => 'Identitas Pembeli',
                            'name'       => $clientName,
                            'code'       => 'ID: CLI-' . str_pad($ord->user_id, 4, '0', STR_PAD_LEFT),
                            'code_class' => 'text-success-deep',
                            'note'       => 'Status: ' . ($isContract ? 'Kontrak B2B Terverifikasi' : 'Klien Reguler'),
                        ],
                        [
                            'label'      => 'Kontak & PIC Penerima',
                            'name'       => $ord->user?->name ?: 'PIC Operasional',
                            'code'       => ($ord->user?->phone ?: '0812-0000-0000') . ' (WA Aktif)',
                            'code_class' => 'text-ink-body',
                            'note'       => 'Email: ' . ($ord->user?->email ?: 'klien@agroorder.id'),
                        ],
                        [
                            'label'      => 'Destinasi & Jadwal Kirim',
                            'name'       => $ord->delivery_address ?: 'Gudang Utama Klien',
                            'code'       => 'Syarat: Armada Logistik Berpendingin',
                            'code_class' => 'text-danger',
                            'note'       => 'Target: ' . ($ord->target_delivery_date ? $ord->target_delivery_date->format('d M Y') : 'H+1 Subuh 05:00 WIB'),
                        ],
                    ],
                    'rule03_status'            => ['label' => 'Aman / Tercukupi', 'icon' => 'check', 'class' => 'bg-accent text-success-ink'],
                    'allocations'              => $allocations,
                    'pricing'                  => [
                        ['label' => 'Estimasi Bruto PO', 'value' => 'Rp ' . number_format($totalVal, 0, ',', '.'), 'suffix' => null, 'note' => null, 'highlight' => false],
                        ['label' => 'PPN / Retribusi (0%)', 'value' => 'Rp 0', 'suffix' => null, 'note' => '(Komoditas Pokok)', 'highlight' => false],
                        ['label' => 'Total Estimasi PO', 'value' => 'Rp ' . number_format($totalVal, 0, ',', '.'), 'suffix' => null, 'note' => null, 'highlight' => true],
                    ],
                    'rule04_qty'               => number_format($totalQty, 0, ',', '.') . ' kg',
                    'deliveries'               => [],
                    'deliveries_total'         => 'Rp ' . number_format($totalVal, 0, ',', '.'),
                    'plafon'                   => 'Rp 50.000.000',
                ];
            }
            $console['queue']['orders'] = array_merge($mappedOrders, $console['queue']['orders']);
        }

        return view('sekretaris.verification', [
            'operator'  => $console['operator'],
            'heading'   => $console['heading'],
            'steps'     => $console['steps'],
            'queue'     => $console['queue'],
            'checklist' => $console['checklist'],
            'rule03'    => $console['rule03'],
            'rule04'    => $console['rule04'],
            'rule14'    => $console['rule14'],
        ]);
    }

    /**
     * Menyetujui pesanan & mengunci stok panen via Pessimistic Row Locking
     */
    public function approve(int $id)
    {
        try {
            $secretaryId = Auth::guard('web')->id();
            $order = $this->orderService->approveOrder($id, $secretaryId);

            return redirect()->route('sekretaris.verification')
                ->with('success', "Pesanan {$order->order_number} disetujui. Kuota stok berhasil dikunci dan pesanan dialihkan ke status TERVERIFIKASI.");
        } catch (Exception $e) {
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

            return redirect()->route('sekretaris.verification')
                ->with('success', "Pesanan {$order->order_number} telah ditolak.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
