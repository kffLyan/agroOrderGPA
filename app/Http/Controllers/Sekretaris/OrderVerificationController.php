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
        $user = Auth::user();

        $operator = [
            'role'     => 'Sekretaris Operasional',
            'name'     => $user?->name ?: 'Sekretariat GPA',
            'code'     => $user ? 'SK-' . str_pad($user->id, 4, '0', STR_PAD_LEFT) : 'SK-001',
            'initials' => strtoupper(substr($user?->name ?: 'SK', 0, 2)),
            'branch'   => 'Kantor Pusat Cibitung',
            'email'    => $user?->email ?: 'sekretaris@agroorder.co.id',
            'shift'    => 'Shift Kerja 07.00 - 16.00 WIB',
        ];

        $heading = [
            'title'            => 'Verifikasi Pesanan Masuk & Kesiapan Panen',
            'subtitle'         => 'Pemeriksaan pesanan klien, kepastian stok panen kebun, dan persetujuan resmi sebelum jadwal panen dimulai.',
            'compliance_label' => 'Tingkat Kepatuhan Verifikasi',
            'compliance_value' => '100% Sesuai Aturan',
        ];

        $steps = [
            [
                'number'  => '01',
                'label'   => 'Antrean Verifikasi Pesanan Masuk',
                'note'    => 'Menunggu',
                'active'  => true,
                'message' => 'Pesanan baru yang butuh persetujuan dan penguncian stok sayur.',
            ],
            [
                'number'  => '02',
                'label'   => 'Pencetakan Faktur & Tagihan',
                'note'    => null,
                'active'  => false,
                'message' => 'Penerbitan faktur tagihan untuk pesanan yang sudah selesai dikirim.',
            ],
            [
                'number'  => '03',
                'label'   => 'Pemeriksaan Bukti Transfer',
                'note'    => null,
                'active'  => false,
                'message' => 'Pengecekan bukti pembayaran dari klien reguler.',
            ],
        ];

        $checklist = [
            'title' => 'Daftar Periksa Wajib Sebelum Menyetujui',
            'items' => [
                'Pastikan ketersediaan sayur di kebun atau mitra tani binaan mencukupi.',
                'Pastikan alamat pengiriman dan permintaan armada sudah sesuai.',
                'Periksa limit batas kredit dan kesepakatan tempo pembayaran klien.',
            ],
        ];

        $rule03 = [
            'title'      => 'Kesiapan Pasokan Sayur Kebun',
            'foot_left'  => 'Alokasi stok disinkronkan langsung dengan jadwal panen Koordinator Lapangan.',
            'foot_right' => 'Status Pasokan: Tersedia & Aman',
        ];

        $rule04 = [
            'chip'        => 'Aturan Berat Bersih',
            'title'       => 'Ketentuan Berat Timbangan Nyata (Netto):',
            'body_before' => 'Jumlah kilogram di pesanan awal',
            'body_mid'    => 'adalah estimasi pemesanan. Tagihan resmi dan Surat Jalan',
            'highlight'   => 'wajib merujuk pada Berat Bersih (Netto) nyata',
            'body_after'  => 'hasil timbangan di gudang setelah daun rusak dipisahkan (disortir).',
            'bullets'     => [
                'Surat Jalan baru dapat dicetak setelah timbangan dikunci di gudang',
                'Batas toleransi susut panen maksimal 2.5%',
            ],
        ];

        $rule14 = [
            'chip'        => 'Tagihan & Faktur',
            'title'       => 'Penerbitan Faktur Tagihan Sesuai Kesepakatan',
            'draft_label' => 'Lihat Draf Tagihan',
            'issue_label' => 'Terbitkan Faktur Tagihan',
        ];

        // Ambil pesanan riil dari database
        $realOrders = Order::with(['user', 'orderItems.product', 'payments'])
            ->whereIn('status', ['MENUNGGU_VERIFIKASI', 'TERVERIFIKASI'])
            ->latest()
            ->get();

        if ($realOrders->isEmpty()) {
            $realOrders = Order::with(['user', 'orderItems.product', 'payments'])
                ->latest()
                ->take(5)
                ->get();
        }

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
            $unitPrice = $firstItem ? (float) $firstItem->unit_price : 15000;

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
                'queue_commodities'        => $itemsList ?: [['name' => 'Sayuran Segar Berkualitas', 'weight' => '100 kg', 'class' => 'text-ink font-semibold', 'weight_class' => 'text-ink-body']],
                'queue_estimate_label'     => 'Perkiraan Nilai Pesanan',
                'queue_estimate'           => 'Rp ' . number_format($totalVal, 0, ',', '.'),
                'queue_estimate_note'      => ['label' => 'Alokasi Siap', 'class' => 'text-success-deep'],
                'queue_status'             => ['label' => 'Stok Kebun Cukup', 'icon' => 'shield', 'class' => 'bg-accent text-success-ink'],
                'source'                   => 'Sumber: ' . ($ord->order_source === 'WEB_PORTAL' ? 'Website Portal Mitra' : 'Pemesanan Manual WhatsApp / Telepon'),
                'po_ref'                   => 'NO. PO: ' . $ord->order_number,
                'order_no'                 => 'Pesanan #' . $ord->order_number,
                'status'                   => [
                    'label' => $ord->status === 'TERVERIFIKASI' ? 'Telah Disetujui' : 'Menunggu Persetujuan Sekretaris',
                    'class' => $ord->status === 'TERVERIFIKASI' ? 'bg-accent text-success-ink font-bold' : 'bg-warning-soft text-warning-deep font-bold',
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
                        'code'       => ($ord->user?->phone ?: '0812-3456-7890') . ' (WhatsApp)',
                        'code_class' => 'text-ink-body',
                        'note'       => 'Email: ' . ($ord->user?->email ?: 'klien@agroorder.id'),
                    ],
                    [
                        'label'      => 'Alamat & Waktu Kirim',
                        'name'       => $ord->delivery_address ?: 'Gudang Penerimaan Klien',
                        'code'       => 'Armada: Mobil Boks Berpendingin',
                        'code_class' => 'text-ink-body',
                        'note'       => 'Jadwal Kirim: ' . ($ord->target_delivery_date ? $ord->target_delivery_date->format('d M Y') : 'Pagi Hari (05:00 WIB)'),
                    ],
                ],
                'rule03_status'            => ['label' => 'Pasokan Aman', 'icon' => 'check', 'class' => 'bg-accent text-success-ink font-bold'],
                'allocations'              => $allocations ?: [
                    [
                        'name'         => 'Selada Romaine Grade A',
                        'sku'          => 'Kode: GPA-VEG-ROM • Standar Mutu Prima',
                        'request'      => '500',
                        'request_unit' => 'KG',
                        'system'       => '600',
                        'system_unit'  => 'KG',
                        'system_class' => 'text-success-deep font-bold',
                        'surplus'      => 'Cadangan +100 kg',
                        'surplus_class'=> 'text-success-deep',
                        'plan'         => [
                            ['label' => '1. Alokasi Panen Kebun Binaan:', 'qty' => '500 kg', 'percent' => 100, 'fill' => 'bg-success-deep'],
                        ],
                        'status'       => ['label' => 'Siap Panen', 'icon' => 'lock', 'class' => 'bg-accent text-success-ink'],
                    ]
                ],
                'pricing'                  => [
                    ['label' => 'Harga Satuan per Kg', 'value' => 'Rp ' . number_format($unitPrice, 0, ',', '.'), 'suffix' => '/ kg', 'note' => null, 'highlight' => false],
                    ['label' => 'Perkiraan Nilai Pesanan', 'value' => 'Rp ' . number_format($totalVal, 0, ',', '.'), 'suffix' => null, 'note' => null, 'highlight' => false],
                    ['label' => 'Pajak / PPN (0%)', 'value' => 'Rp 0', 'suffix' => null, 'note' => '(Bebas Pajak Produk Pertanian)', 'highlight' => false],
                    ['label' => 'Total Estimasi Tagihan', 'value' => 'Rp ' . number_format($totalVal, 0, ',', '.'), 'suffix' => null, 'note' => null, 'highlight' => true],
                ],
                'rule04_qty'               => number_format($totalQty ?: 500, 0, ',', '.') . ' kg',
                'deliveries'               => [],
                'deliveries_total'         => 'Rp ' . number_format($totalVal, 0, ',', '.'),
                'plafon'                   => 'Rp 50.000.000',
            ];
        }

        $b2bCount = count(array_filter($mappedOrders, fn($o) => $o['channel'] === 'b2b'));
        $waCount = count($mappedOrders) - $b2bCount;

        $queue = [
            'title'       => 'Antrean Pesanan Masuk',
            'chip'        => count($mappedOrders) . ' Pesanan',
            'filters'     => [
                ['key' => 'all', 'label' => 'Semua (' . count($mappedOrders) . ')'],
                ['key' => 'b2b', 'label' => 'Website B2B (' . $b2bCount . ')'],
                ['key' => 'wa', 'label' => 'WhatsApp / Manual (' . $waCount . ')'],
            ],
            'orders'      => $mappedOrders,
            'footer_note' => 'Pastikan ketiga poin pada daftar periksa telah terpenuhi sebelum menyetujui pesanan.',
        ];

        return view('sekretaris.verification', [
            'operator'  => $operator,
            'heading'   => $heading,
            'steps'     => $steps,
            'queue'     => $queue,
            'checklist' => $checklist,
            'rule03'    => $rule03,
            'rule04'    => $rule04,
            'rule14'    => $rule14,
        ]);
    }

    /**
     * Menyetujui pesanan & mengunci stok panen
     */
    public function approve(int $id)
    {
        try {
            $secretaryId = Auth::guard('web')->id();
            $order = $this->orderService->approveOrder($id, $secretaryId);

            return redirect()->route('sekretaris.verification')
                ->with('success', "Pesanan {$order->order_number} berhasil disetujui. Jadwal panen telah dikunci dan status pesanan kini TERVERIFIKASI.");
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
