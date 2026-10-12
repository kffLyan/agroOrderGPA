<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManualOrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Menampilkan formulir input order manual via telepon / WhatsApp
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $operator = [
            'role'     => 'Sekretaris Operasional',
            'name'     => $user?->name ?: 'Sekretariat GPA',
            'code'     => $user ? 'SK-' . str_pad($user->id, 4, '0', STR_PAD_LEFT) : 'SK-001',
            'initials' => strtoupper(substr($user?->name ?: 'SK', 0, 2)),
            'branch'   => 'Gudang Pusat Sentra',
            'email'    => $user?->email ?: 'sekretaris@agroorder.co.id',
            'shift'    => 'Shift Pagi 07.00 - 16.00 WIB',
        ];

        $policy = [
            'eyebrow' => 'Input Pesanan Manual',
            'status'  => 'Status: Pengisian Formulir',
            'body'    => 'Pencatatan pesanan darurat dari pembeli via telepon atau WhatsApp. Seluruh jumlah pesanan yang dimasukkan akan dicek ketersediaan stoknya sebelum disahkan.',
            'rules'   => 'Cek Batas Kredit • Cek Stok Sayur • Validasi Netto',
        ];

        $source = [
            'label'           => 'Sumber Pesanan',
            'hint'            => 'Kanal masuk pemesanan dari pelanggan.',
            'channels'        => [
                ['key' => 'whatsapp', 'label' => 'WhatsApp Bisnis', 'note' => 'Pesan masuk via chat WhatsApp.'],
                ['key' => 'phone', 'label' => 'Telepon Langsung', 'note' => 'Pemesanan langsung via panggilan telepon.'],
                ['key' => 'memo', 'label' => 'Formulir Fisik', 'note' => 'Formulir tertulis dari pembeli.'],
            ],
            'active'          => 'whatsapp',
            'reference'       => 'WA-' . date('Ymd') . '-001',
            'reference_label' => 'No. Referensi / Chat WhatsApp',
            'reference_hint'  => 'Contoh: Nomor chat atau catatan pemesanan.',
            'received'        => now()->format('d M Y, H:i') . ' WIB',
            'received_label'  => 'Waktu Pesanan Diterima',
            'validator'       => ($user?->name ?: 'Sekretaris') . ' (' . $operator['code'] . ')',
            'validator_label' => 'Petugas yang Mencatat',
            'warehouse'       => 'Gudang Sentra Sentul',
            'warehouse_label' => 'Lokasi Gudang',
        ];

        // Ambil klien dari database
        $clientUser = User::where('client_type', 'B2B_KONTRAK')->first()
            ?? User::where('role', 'KLIEN')->first();

        $client = [
            'label'         => 'Data Pembeli',
            'hint'          => 'Pilih pembeli terdaftar atau masukkan data pembeli baru.',
            'modes'         => [
                ['key' => 'registered', 'label' => 'Pembeli Terdaftar'],
                ['key' => 'new', 'label' => 'Pembeli Baru'],
            ],
            'active_mode'   => 'registered',
            'name'          => $clientUser?->company_name ?: ($clientUser?->name ?: 'Hotel Grand Pangrango'),
            'code'          => 'CLI-' . str_pad($clientUser?->id ?? 1, 4, '0', STR_PAD_LEFT),
            'segment'       => 'Hotel, Resto & Katering',
            'contact'       => $clientUser?->pic_name ?: ($clientUser?->name ?: 'Bpk. Ridwan (Bagian Dapur)'),
            'contact_label' => 'Penanggung Jawab / PIC Dapur',
            'phone'         => $clientUser?->phone ?: '0812-3456-7890',
            'credit'        => [
                'label'           => 'Batas Plafon Kredit Pembeli',
                'status'          => 'Kredit Aktif & Aman',
                'status_note'     => 'Status pembayaran pembeli lancar',
                'used'            => 15000000,
                'used_label'      => 'Kredit Terpakai',
                'limit'           => 50000000,
                'limit_label'     => 'Batas Maksimal Kredit',
                'remaining'       => 35000000,
                'remaining_label' => 'Sisa Plafon Kredit Tersedia',
                'percent'         => 30.0,
                'percent_label'   => 'Pemakaian Plafon',
                'rule'            => 'Batas Kredit Sesuai Ketentuan',
            ],
            'address_label' => 'Alamat Pengiriman Sayur',
            'address'       => $clientUser?->address ?: 'Jl. Raya Pajajaran No. 45, Bogor Tengah',
            'dock'          => 'Akses Armada: Mobil Boks Pendingin',
        ];

        // Ambil produk riil dari database
        $dbProducts = Product::where('is_active', true)->get();
        if ($dbProducts->isEmpty()) {
            $dbProducts = Product::all();
        }

        $commodities = [];
        $catalog = [];

        foreach ($dbProducts as $idx => $prod) {
            $entry = [
                'key'          => 'prod-' . $prod->id,
                'product_id'   => $prod->id,
                'name'         => $prod->name,
                'sku'          => $prod->sku ?: ('GPA-VEG-' . str_pad($prod->id, 3, '0', STR_PAD_LEFT)),
                'grade'        => $prod->grade ?: 'Grade A Pilihan',
                'unit'         => 'kg',
                'min_order'    => (float) ($prod->minimum_order ?: 10),
                'stock'        => 500,
                'stock_bud'    => 400,
                'stock_buffer' => 100,
                'price'        => (float) $prod->base_price,
            ];

            if ($idx < 3) {
                $commodities[] = array_merge($entry, [
                    'quantity' => (float) max(20, $prod->minimum_order ?: 20),
                ]);
            } else {
                $catalog[] = $entry;
            }
        }

        if (empty($commodities)) {
            $commodities = [
                [
                    'key'          => 'romaine',
                    'name'         => 'Selada Romaine',
                    'sku'          => 'GPA-VEG-ROM',
                    'grade'        => 'Grade A Super',
                    'unit'         => 'kg',
                    'min_order'    => 10,
                    'stock'        => 500,
                    'stock_bud'    => 400,
                    'stock_buffer' => 100,
                    'price'        => 15000,
                    'quantity'     => 50,
                ]
            ];
        }

        $logistics = [
            'label'           => 'Jadwal & Pengiriman Armada',
            'hint'            => 'Pilih jadwal tiba, jenis kendaraan, dan catatan penyerahan barang.',
            'date_label'      => 'Tanggal Rencana Pengiriman',
            'date'            => now()->addDay()->format('Y-m-d'),
            'arrival_label'   => 'Waktu Tiba di Lokasi',
            'arrival'         => 'Pagi Hari (05:00 - 08:00 WIB)',
            'arrival_options' => [
                'Pagi Hari (05:00 - 08:00 WIB)',
                'Siang Hari (10:00 - 12:00 WIB)',
                'Sore Hari (14:00 - 16:00 WIB)',
            ],
            'route_label'     => 'Rute Distribusi',
            'route'           => 'Rute Koridor Utama Jabodetabek',
            'route_note'      => 'Pengiriman Sayuran Segar',
            'fleet_label'     => 'Jenis Armada Mobil',
            'fleet'           => 'Mobil Boks Pendingin (Chilled 4°C)',
            'fleet_note'      => 'Kapasitas muatan aman',
            'notes_label'     => 'Catatan Khusus Pengiriman',
            'notes'           => 'Sayur disusun rapi dalam keranjang bersih, PIC dapur siap menerima pagi hari.',
            'fee_label'       => 'Biaya Kirim (Flat)',
            'fee'             => 150000,
            'fee_note'        => 'Tarif flat standar pengiriman',
        ];

        $payment = [
            'label'          => 'Cara Pembayaran & Bukti Transfer',
            'hint'           => 'Pilih metode pembayaran yang disepakati dengan pembeli.',
            'methods'        => [
                ['key' => 'transfer', 'label' => 'Transfer Bank (BCA / Mandiri)'],
                ['key' => 'qris', 'label' => 'QRIS Resmi GPA'],
                ['key' => 'cod', 'label' => 'Bayar di Tempat (COD)'],
                ['key' => 'top14', 'label' => 'Tempo 14 Hari (Khusus Mitra Kontrak)'],
            ],
            'active'         => 'transfer',
            'evidence_label' => 'Foto Bukti Pembayaran',
            'file'           => 'BUKTI_PEMBAYARAN_MANUAL.PNG',
            'size'           => '520 KB',
            'hash'           => 'VERIF-' . date('Ymd'),
            'hash_note'      => 'Bukti Terverifikasi',
            'upload_hint'    => 'Unggah foto bukti transfer atau tangkapan layar chat pemesanan.',
        ];

        return view('sekretaris.manual-order', [
            'operator'    => $operator,
            'policy'      => $policy,
            'source'      => $source,
            'client'      => $client,
            'commodities' => $commodities,
            'catalog'     => $catalog,
            'logistics'   => $logistics,
            'payment'     => $payment,
        ]);
    }

    /**
     * Menyimpan pesanan manual baru ke database
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'client_name'          => 'nullable|string|max:255',
            'delivery_address'     => 'nullable|string|max:500',
            'target_delivery_date' => 'nullable|date',
            'items'                => 'nullable|array',
            'items.*.product_id'   => 'nullable|exists:products,id',
            'items.*.quantity'     => 'nullable|numeric|min:1',
            'items.*.price'        => 'nullable|numeric|min:0',
        ]);

        $secretary = Auth::user();

        // Cari atau gunakan klien default
        $client = User::where('role', 'KLIEN')->first();
        if (!$client) {
            $client = $secretary;
        }

        $order = DB::transaction(function () use ($validated, $secretary, $client) {
            $orderNumber = $this->orderService->generateOrderNumber();
            $targetDate = $validated['target_delivery_date'] ?? now()->addDay()->toDateString();
            $address = $validated['delivery_address'] ?? 'Gudang Utama Klien';

            $newOrder = Order::create([
                'order_number'         => $orderNumber,
                'user_id'              => $client->id,
                'order_source'         => 'INPUT_MANUAL',
                'target_delivery_date' => $targetDate,
                'delivery_address'     => $address,
                'status'               => 'MENUNGGU_VERIFIKASI',
                'estimated_total'      => 0,
                'grand_total'          => 0,
            ]);

            $total = 0;
            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $it) {
                    $prod = Product::find($it['product_id'] ?? null);
                    if ($prod) {
                        $qty = (float) $it['quantity'];
                        $price = (float) ($it['price'] ?? $prod->base_price);
                        $sub = $qty * $price;
                        $total += $sub;

                        OrderItem::create([
                            'order_id'          => $newOrder->id,
                            'product_id'        => $prod->id,
                            'ordered_qty'       => $qty,
                            'unit_price'        => $price,
                            'subtotal_estimate' => $sub,
                        ]);
                    }
                }
            } else {
                $firstProd = Product::first();
                if ($firstProd) {
                    $qty = 50;
                    $price = (float) $firstProd->base_price;
                    $sub = $qty * $price;
                    $total += $sub;

                    OrderItem::create([
                        'order_id'          => $newOrder->id,
                        'product_id'        => $firstProd->id,
                        'ordered_qty'       => $qty,
                        'unit_price'        => $price,
                        'subtotal_estimate' => $sub,
                    ]);
                }
            }

            $newOrder->update([
                'estimated_total' => $total,
                'grand_total'     => $total,
            ]);

            return $newOrder;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Pesanan manual {$order->order_number} berhasil disimpan.",
                'order'   => $order,
            ]);
        }

        return redirect()->route('sekretaris.verification')
            ->with('success', "Pesanan manual {$order->order_number} berhasil dibuat dan masuk ke antrean verifikasi.");
    }
}
