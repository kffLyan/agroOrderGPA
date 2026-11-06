<?php

namespace App\Http\Controllers\Sekretaris;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sekretaris\SimpanPesananManualRequest;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\TataKelolaService;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// Controller input pesanan manual (WhatsApp / Telepon) untuk Sekretaris
class PesananManualController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    // Menyiapkan formulir pembuatan pesanan manual untuk klien walk-in, via WhatsApp, atau telepon
    public function index(Request $request): View
    {
        // Mengambil daftar seluruh klien terdaftar dengan kontrak aktif dan faktur tertunggak
        $allClients = User::where('role', 'KLIEN')
            ->with([
                'contracts' => fn($subQuery) => $subQuery->where('status', 'ACTIVE'),
                'invoices'  => fn($subQuery) => $subQuery->whereIn('status', ['UNPAID', 'OVERDUE']),
            ])
            ->get();

        if ($allClients->isEmpty()) {
            $allClients = User::whereIn('role', ['KLIEN', 'SEKRETARIS'])
                ->with([
                    'contracts' => fn($subQuery) => $subQuery->where('status', 'ACTIVE'),
                    'invoices'  => fn($subQuery) => $subQuery->whereIn('status', ['UNPAID', 'OVERDUE']),
                ])
                ->get();
        }

        $clients = $allClients->map(function ($client) {
            return [
                'id'           => $client->id,
                'code'         => $client->code,
                'name'         => $client->company_name ?: $client->name,
                'contact'      => $client->contact,
                'phone'        => $client->phone ?: '0812-3456-7890',
                'address'      => $client->address ?: 'Sentra Operasional Klien, Jawa Barat',
                'type'         => $client->client_type ?: 'B2B_KONTRAK',
                'credit_limit' => $client->credit_limit,
                'credit_used'  => $client->credit_used,
            ];
        })->values()->all();

        $clientUser = $allClients->first();

        // Mengambil daftar komoditas produk aktif yang memiliki stok panen tersedia di gudang
        $dbProducts = Product::with(['harvestBatches' => fn($subQuery) => $subQuery->where('available_quantity', '>', 0)])
            ->where('is_active', true)
            ->get();

        if ($dbProducts->isEmpty()) {
            $dbProducts = Product::with(['harvestBatches' => fn($subQuery) => $subQuery->where('available_quantity', '>', 0)])->get();
        }

        $catalog = $dbProducts->map(function ($product) {
            return [
                'key'          => 'prod-' . $product->id,
                'product_id'   => $product->id,
                'name'         => $product->name,
                'sku'          => $product->sku ?: ('GPA-VEG-' . str_pad((string) $product->id, 3, '0', STR_PAD_LEFT)),
                'grade'        => $product->grade ?: 'Grade A Pilihan',
                'unit'         => $product->unit ?: 'kg',
                'min_order'    => (float) ($product->minimum_order ?: 5),
                'stock'        => $product->stock,
                'stock_bud'    => $product->stock_bud,
                'stock_buffer' => $product->stock_buffer,
                'price'        => $product->price,
            ];
        })->values()->all();

        $tataKelolaService = app(TataKelolaService::class);
        $paymentChannels = $tataKelolaService->getActivePaymentChannels();
        if (empty($paymentChannels)) {
            $paymentChannels = $tataKelolaService->defaultPaymentChannels();
        }

        // View Models
        $user = Auth::user();
        $code = $user ? 'SK-' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT) : 'SK-001';
        $firstClient = $clients[0] ?? null;

        $sourceData = [
            'reference'       => 'MANUAL-' . date('Ymd') . '-' . str_pad((string) ($clientUser?->id ?? 1), 3, '0', STR_PAD_LEFT),
            'received'        => now()->format('d M Y, H:i') . ' WIB',
            'active'          => 'whatsapp',
            'channels'        => [
                ['key' => 'whatsapp', 'label' => 'WhatsApp Bisnis GPA', 'note' => 'Pesan teks / dokumen PDF'],
                ['key' => 'telepon', 'label' => 'Telepon Langsung', 'note' => 'Konfirmasi lisan via call center'],
                ['key' => 'email', 'label' => 'Email Resmi', 'note' => 'Lampiran purchase order'],
            ],
            'reference_label' => 'Nomor Referensi / Chat',
            'reference_hint'  => 'Contoh: WA-' . date('Ym') . '-001',
            'received_label'  => 'Waktu Diterima',
            'validator_label' => 'Petugas yang Mencatat',
            'validator'       => ($user?->name ?: 'Sekretaris') . ' (' . $code . ')',
            'warehouse_label' => 'Lokasi Gudang',
            'warehouse'       => 'Gudang Konsolidasi Ciwidey',
        ];

        $clientData = [
            'id'          => $clientUser?->id,
            'code'        => $firstClient['code'] ?? ($clientUser ? ('CLI-' . str_pad((string) $clientUser->id, 4, '0', STR_PAD_LEFT)) : '—'),
            'name'        => $firstClient['name'] ?? ($clientUser?->company_name ?: ($clientUser?->name ?: '—')),
            'contact'     => $firstClient['contact'] ?? ($clientUser?->pic_name ?: ($clientUser?->name ?: '—')),
            'phone'       => $firstClient['phone'] ?? ($clientUser?->phone ?: '—'),
            'address'     => $firstClient['address'] ?? ($clientUser?->address ?: '—'),
            'active_mode' => 'registered',
            'credit'      => [
                'limit' => $firstClient['credit_limit'] ?? 0.0,
                'used'  => $firstClient['credit_used'] ?? 0.0,
            ],
        ];

        $logisticsData = [
            'date'    => now()->addDay()->format('Y-m-d'),
            'arrival' => 'Pagi Hari (05:00 - 08:00 WIB)',
            'route'   => 'Rute Koridor Pengantaran GPA',
            'fleet'   => 'Armada Reefer Chilled',
            'notes'   => '',
            'fee'     => 0,
        ];

        $paymentData = [
            'active'    => 'transfer',
            'file'      => '',
            'size'      => '',
            'hash'      => 'VERIF-' . date('Ymd'),
            'hash_note' => 'Menunggu Dokumen',
        ];

        $creditLimit = (float) ($firstClient['credit_limit'] ?? 0.0);
        $usedCredit  = (float) ($firstClient['credit_used'] ?? 0.0);

        return view('sekretaris.pesanan-manual', [
            'clientUser'      => $clientUser,
            'clients'         => $clients,
            'creditLimit'     => $creditLimit,
            'usedCredit'      => $usedCredit,
            'commodities'     => [],
            'catalog'         => $catalog,
            'paymentChannels' => $paymentChannels,
            'taxSettings'     => $tataKelolaService->getTaxSettings(),
            'source'          => $sourceData,
            'sourceData'      => $sourceData,
            'clientData'      => $clientData,
            'logisticsData'   => $logisticsData,
            'paymentData'     => $paymentData,
        ]);
    }

    // Menyimpan transaksi pesanan manual, mengalokasikan stok panen, dan menerbitkan faktur penagihan otomatis
    public function store(SimpanPesananManualRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $secretary = Auth::user();

        // Mengambil profil entitas klien pemesan atau membuat/menggunakan profil pembeli baru
        $clientId = $validated['client_id'] ?? null;
        $client = $clientId ? User::find($clientId) : null;

        if (!$client) {
            $clientName = !empty($validated['client_name']) ? trim($validated['client_name']) : 'Pelanggan Walk-In';
            $clientPhone = !empty($validated['client_phone']) ? trim($validated['client_phone']) : '0812-3456-7890';
            $deliveryAddress = !empty($validated['delivery_address']) ? trim($validated['delivery_address']) : 'Kota Bandung, Jawa Barat';
            $picName = !empty($validated['client_contact']) ? trim($validated['client_contact']) : $clientName;

            // Cari apakah user KLIEN dengan nama / company_name tersebut sudah ada di database
            $client = User::where('role', 'KLIEN')
                ->where(function ($q) use ($clientName) {
                    $q->where('company_name', $clientName)
                      ->orWhere('name', $clientName);
                })
                ->first();

            // Jika belum ada, daftarkan sebagai pembeli reguler baru
            if (!$client) {
                $slug = Str::slug($clientName) ?: 'pembeli';
                $email = 'pembeli.' . $slug . '@agroorder.gpa';
                $counter = 1;
                while (User::where('email', $email)->exists()) {
                    $email = 'pembeli.' . $slug . '.' . $counter . '@agroorder.gpa';
                    $counter++;
                }

                $client = User::create([
                    'name'              => $clientName,
                    'company_name'      => $clientName,
                    'email'             => $email,
                    'phone'             => $clientPhone,
                    'address'           => $deliveryAddress,
                    'pic_name'          => $picName,
                    'pic_phone'         => $clientPhone,
                    'role'              => 'KLIEN',
                    'client_type'       => 'REGULER',
                    'is_active'         => true,
                    'email_verified_at' => now(),
                    'password'          => Hash::make(Str::random(16)),
                ]);
            }
        }

        $requestedStatus = $validated['status'] ?? 'LUNAS';
        $isDraft = ($requestedStatus === 'DRAFT');

        // Menentukan apakah pesanan langsung disahkan dan diverifikasi di tempat oleh sekretaris
        $isDirectVerification = !$isDraft && in_array($requestedStatus, ['LUNAS', 'SIAP_KIRIM', 'VERIFIKASI', 'VERIFIED']);

        $paymentMethod = PaymentMethod::fromRaw(
            $validated['payment_method'] ?? null,
            $validated['payment_channel_id'] ?? null
        );
        $paymentMethodEnum = $paymentMethod->value;

        // Menetapkan status pesanan berdasarkan metode pembayaran (Tempo TOP vs Lunas Langsung)
        if ($isDraft) {
            $orderStatus = 'MENUNGGU_VERIFIKASI';
        } elseif ($isDirectVerification) {
            $orderStatus = ($paymentMethod === PaymentMethod::TEMPO_TOP)
                ? OrderStatus::MENUNGGU_PEMBAYARAN->value
                : OrderStatus::LUNAS->value;
        } else {
            $orderStatus = 'MENUNGGU_VERIFIKASI';
        }

        $order = DB::transaction(function () use ($validated, $secretary, $client, $orderStatus, $isDirectVerification, $paymentMethodEnum, $request) {
            $orderNumber = $this->orderService->generateOrderNumber();
            $targetDate = $validated['target_delivery_date'] ?? now()->addDay()->toDateString();
            $address = $validated['delivery_address'] ?? ($client->address ?: 'Gudang Utama Klien, Jawa Barat');

            $sourceInput = strtoupper((string) ($validated['order_source'] ?? 'WHATSAPP_ADMIN'));
            $orderSource = match ($sourceInput) {
                'ROUTINE_SCHEDULE', 'ROUTINE' => 'ROUTINE_SCHEDULE',
                'WEB_PORTAL', 'WEB'           => 'WEB_PORTAL',
                default                       => 'WHATSAPP_ADMIN',
            };

            $newOrder = Order::create([
                'order_number'         => $orderNumber,
                'user_id'              => $client->id,
                'order_source'         => $orderSource,
                'target_delivery_date' => $targetDate,
                'delivery_address'     => $address,
                'status'               => $orderStatus,
                'grand_total'          => 0,
                'verified_by'          => $isDirectVerification ? $secretary?->id : null,
            ]);

            $total = 0;
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $qty = (float) $item['quantity'];
                    $price = (float) ($item['price'] ?? $product->base_price);
                    $subtotal = $qty * $price;
                    $total += $subtotal;

                    OrderItem::create([
                        'order_id'    => $newOrder->id,
                        'product_id'  => $product->id,
                        'ordered_qty' => $qty,
                        'unit_price'  => $price,
                        'subtotal'    => $subtotal,
                    ]);
                }
            }

            // Memeriksa kebijakan perpajakan PPN dan ongkos kirim dari layanan tata kelola
            $tataKelolaService = app(TataKelolaService::class);
            $taxSettings = $tataKelolaService->getTaxSettings();

            $shippingCost = 0.0;
            if (isset($validated['shipping_cost']) && is_numeric($validated['shipping_cost'])) {
                $shippingCost = max(0.0, (float) $validated['shipping_cost']);
            } elseif (isset($validated['fee']) && is_numeric($validated['fee'])) {
                $shippingCost = max(0.0, (float) $validated['fee']);
            }

            $taxRate = $taxSettings['enabled'] ? (float) $taxSettings['percent'] : 0.0;
            $taxAmount = round($total * ($taxRate / 100), 2);
            $grandTotalFinal = $total + $shippingCost + $taxAmount;

            $newOrder->update([
                'grand_total' => $grandTotalFinal,
            ]);

            // Jika diverifikasi langsung, lakukan pemotongan alokasi stok dan buat faktur tagihan resmi
            if ($isDirectVerification) {
                $stockService = app(StockService::class);
                foreach ($validated['items'] as $item) {
                    $stockService->allocateStock((int) $item['product_id'], (float) $item['quantity']);
                }

                $invoiceNumber = $this->orderService->generateInvoiceNumber();
                $dueDate = ($paymentMethodEnum === 'TEMPO_TOP') ? now()->addDays(14)->toDateString() : now()->toDateString();
                $invoice = Invoice::create([
                    'invoice_number'  => $invoiceNumber,
                    'user_id'         => $client->id,
                    'period_start'    => now()->toDateString(),
                    'period_end'      => $targetDate,
                    'subtotal_amount' => $total,
                    'grand_total'     => $grandTotalFinal,
                    'due_date'        => $dueDate,
                    'status'          => ($paymentMethodEnum === 'TEMPO_TOP') ? 'UNPAID' : 'PAID',
                    'pdf_file_path'   => 'invoices/' . $invoiceNumber . '.pdf',
                ]);
                $newOrder->update(['invoice_id' => $invoice->id]);
            }

            $proofPath = null;
            if ($request->hasFile('proof_file')) {
                $proofPath = $request->file('proof_file')->store('payments', 'public');
            } elseif ($request->hasFile('payment_proof')) {
                $proofPath = $request->file('payment_proof')->store('payments', 'public');
            }

            if ($proofPath) {
                $destinationDir = public_path('storage/payments');
                if (!is_dir($destinationDir)) {
                    @mkdir($destinationDir, 0775, true);
                }
                @copy(storage_path('app/public/' . $proofPath), $destinationDir . DIRECTORY_SEPARATOR . basename($proofPath));
            }

            $senderBank = $validated['sender_bank'] ?? null;
            $senderAccount = $validated['sender_account'] ?? null;
            $customNote = $validated['payment_custom_note'] ?? null;

            $cashAmount = (float) ($validated['cash_amount'] ?? 0);
            if ($cashAmount <= 0) {
                $cashAmount = $grandTotalFinal;
            }
            $cashChange = (float) ($validated['cash_change'] ?? max(0, $cashAmount - $grandTotalFinal));

            $verificationNotes = [];
            if ($paymentMethodEnum === 'CASH') {
                $verificationNotes[] = 'Pembayaran Tunai Kasir: Total Rp ' . number_format($grandTotalFinal, 0, ',', '.')
                    . ' • Diterima Rp ' . number_format($cashAmount, 0, ',', '.')
                    . ' • Kembalian Rp ' . number_format($cashChange, 0, ',', '.');
            } elseif ($senderBank) {
                $accountInfo = $senderAccount ? " (Rek/No: {$senderAccount})" : '';
                $verificationNotes[] = "Kanal: {$senderBank}{$accountInfo}";
            }
            if ($customNote) {
                $verificationNotes[] = $customNote;
            }
            $financialBreakdownNote = "Rincian: Subtotal Rp " . number_format($total, 0, ',', '.')
                . ($shippingCost > 0 ? " • Ongkir Rp " . number_format($shippingCost, 0, ',', '.') : " • Bebas Ongkir")
                . ($taxRate > 0 ? " • PPN ({$taxRate}%) Rp " . number_format($taxAmount, 0, ',', '.') : " • Bebas PPN (0% DTP)");
            $verificationNotes[] = $financialBreakdownNote;

            $finalNote = implode(' • ', $verificationNotes);
            $paymentRef = $validated['payment_reference'] ?? ('PAY-' . date('Ymd') . '-' . str_pad((string) $newOrder->id, 4, '0', STR_PAD_LEFT));

            Payment::create([
                'order_id'          => $newOrder->id,
                'invoice_id'        => $newOrder->invoice_id,
                'payment_reference' => $paymentRef,
                'payment_method'    => $paymentMethodEnum,
                'amount'            => $grandTotalFinal,
                'proof_url'         => $proofPath ?: ($request->input('proof_url') ?: null),
                'status'            => ($orderStatus === 'DRAFT' || $orderStatus === 'MENUNGGU_VERIFIKASI') ? 'MENUNGGU_VERIFIKASI' : 'LUNAS',
                'verified_by'       => $isDirectVerification ? $secretary?->id : null,
                'verification_note' => $finalNote,
                'paid_at'           => now(),
            ]);

            return $newOrder;
        });

        $redirectUrl = $isDirectVerification
            ? route('sekretaris.surat-jalan')
            : route('sekretaris.verifikasi');

        $message = $isDirectVerification
            ? "Pesanan manual {$order->order_number} berhasil diverifikasi dan langsung diteruskan ke antrean Penerbitan Surat Jalan."
            : ($isDraft
                ? "Draf pesanan manual {$order->order_number} berhasil disimpan."
                : "Pesanan manual {$order->order_number} berhasil dibuat dan masuk ke antrean verifikasi.");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'message'      => $message,
                'redirect_url' => $redirectUrl,
                'order'        => [
                    'id'           => $order->id,
                    'order_number' => $order->order_number,
                    'grand_total'  => (float) $order->grand_total,
                    'client_name'  => $client?->company_name ?: ($client?->name ?: 'Pelanggan Walk-In'),
                    'receipt_url'  => route('prints.thermal-receipt', ['invoice' => $order->order_number]),
                    'invoice_url'  => route('prints.invoice', ['invoice' => $order->order_number]),
                ],
            ]);
        }

        return redirect($redirectUrl)->with('success', $message);
    }
}
