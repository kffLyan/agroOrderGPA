<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sekretaris\StoreManualOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
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
        // Ambil daftar seluruh pembeli / klien terdaftar dengan kontrak dan faktur terutang (Bebas N+1 Query)
        $allClients = User::where('role', 'KLIEN')
            ->with([
                'contracts' => fn ($q) => $q->where('status', 'ACTIVE'),
                'invoices'  => fn ($q) => $q->whereIn('status', ['UNPAID', 'OVERDUE']),
            ])
            ->get();

        if ($allClients->isEmpty()) {
            $allClients = User::whereIn('role', ['KLIEN', 'SEKRETARIS'])
                ->with([
                    'contracts' => fn ($q) => $q->where('status', 'ACTIVE'),
                    'invoices'  => fn ($q) => $q->whereIn('status', ['UNPAID', 'OVERDUE']),
                ])
                ->get();
        }

        $clients = $allClients->map(function ($c) {
            return [
                'id'           => $c->id,
                'code'         => $c->code,
                'name'         => $c->company_name ?: $c->name,
                'contact'      => $c->contact,
                'phone'        => $c->phone ?: '0812-3456-7890',
                'address'      => $c->address ?: 'Sentra Operasional Klien, Jawa Barat',
                'type'         => $c->client_type ?: 'B2B_KONTRAK',
                'credit_limit' => $c->credit_limit,
                'credit_used'  => $c->credit_used,
            ];
        })->values()->all();

        $clientUser = $allClients->first();

        // Ambil produk riil dari database dengan batch panen aktif untuk mencegah query N+1
        $dbProducts = Product::with(['harvestBatches' => fn($q) => $q->where('available_quantity', '>', 0)])
            ->where('is_active', true)
            ->get();

        if ($dbProducts->isEmpty()) {
            $dbProducts = Product::with(['harvestBatches' => fn($q) => $q->where('available_quantity', '>', 0)])->get();
        }

        $catalog = $dbProducts->map(function ($prod) {
            return [
                'key'          => 'prod-' . $prod->id,
                'product_id'   => $prod->id,
                'name'         => $prod->name,
                'sku'          => $prod->sku ?: ('GPA-VEG-' . str_pad((string) $prod->id, 3, '0', STR_PAD_LEFT)),
                'grade'        => $prod->grade ?: 'Grade A Pilihan',
                'unit'         => $prod->unit ?: 'kg',
                'min_order'    => (float) ($prod->minimum_order ?: 5),
                'stock'        => $prod->stock,
                'stock_bud'    => $prod->stock_bud,
                'stock_buffer' => $prod->stock_buffer,
                'price'        => $prod->price,
            ];
        })->values()->all();

        $governanceService = app(\App\Services\GovernanceService::class);
        $paymentChannels = $governanceService->getActivePaymentChannels();
        if (empty($paymentChannels)) {
            $paymentChannels = $governanceService->defaultPaymentChannels();
        }

        // View Models (Prinsip MVC: Controller mengolah data & logika tampilan)
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

        return view('sekretaris.manual-order', [
            'clientUser'      => $clientUser,
            'clients'         => $clients,
            'commodities'     => [],
            'catalog'         => $catalog,
            'paymentChannels' => $paymentChannels,
            'source'          => $sourceData,
            'sourceData'      => $sourceData,
            'clientData'      => $clientData,
            'logisticsData'   => $logisticsData,
            'paymentData'     => $paymentData,
        ]);
    }

    /**
     * Menyimpan pesanan manual baru ke database secara riil
     */
    public function store(StoreManualOrderRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $secretary = Auth::user();

        // Tentukan akun pembeli
        $clientId = $validated['client_id'] ?? null;
        $client = $clientId ? User::find($clientId) : null;
        if (!$client) {
            $client = User::where('role', 'KLIEN')->first() ?? $secretary;
        }

        $orderStatus = $validated['status'] ?? 'MENUNGGU_VERIFIKASI';

        $order = DB::transaction(function () use ($validated, $secretary, $client, $orderStatus, $request) {
            $orderNumber = $this->orderService->generateOrderNumber();
            $targetDate = $validated['target_delivery_date'] ?? now()->addDay()->toDateString();
            $address = $validated['delivery_address'] ?? ($client->address ?: 'Gudang Utama Klien, Jawa Barat');

            // Petakan ke enum database: 'WEB_PORTAL', 'WHATSAPP_ADMIN', 'ROUTINE_SCHEDULE'
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
                'verified_by'          => null,
            ]);

            $total = 0;
            foreach ($validated['items'] as $it) {
                $prod = Product::find($it['product_id']);
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
                        'subtotal'          => $sub,
                    ]);
                }
            }

            $newOrder->update([
                'grand_total'     => $total,
            ]);

            // Tangani unggahan berkas bukti bayar fisik jika ada
            $proofPath = null;
            if ($request->hasFile('proof_file')) {
                $proofPath = $request->file('proof_file')->store('payments', 'public');
            } elseif ($request->hasFile('payment_proof')) {
                $proofPath = $request->file('payment_proof')->store('payments', 'public');
            }

            // Buat pencatatan pembayaran/termin yang dipilih via Enum
            $paymentMethod = \App\Enums\PaymentMethod::fromRaw(
                $validated['payment_method'] ?? null,
                $validated['payment_channel_id'] ?? null
            );
            $paymentMethodEnum = $paymentMethod->value;

            // Catatan verifikasi / detail kanal pembayaran resmi
            $senderBank = $validated['sender_bank'] ?? null;
            $senderAccount = $validated['sender_account'] ?? null;
            $customNote = $validated['payment_custom_note'] ?? null;

            $cashAmount = (float) ($validated['cash_amount'] ?? 0);
            if ($cashAmount <= 0) {
                $cashAmount = $total;
            }
            $cashChange = (float) ($validated['cash_change'] ?? max(0, $cashAmount - $total));

            $verificationNotes = [];
            if ($paymentMethodEnum === 'CASH') {
                $verificationNotes[] = 'Pembayaran Tunai Kasir: Total Rp ' . number_format($total, 0, ',', '.')
                    . ' • Diterima Rp ' . number_format($cashAmount, 0, ',', '.')
                    . ' • Kembalian Rp ' . number_format($cashChange, 0, ',', '.');
            } elseif ($senderBank) {
                $accInfo = $senderAccount ? " (Rek/No: {$senderAccount})" : '';
                $verificationNotes[] = "Kanal: {$senderBank}{$accInfo}";
            }
            if ($customNote) {
                $verificationNotes[] = $customNote;
            }
            if (empty($verificationNotes)) {
                $verificationNotes[] = ($paymentMethodEnum === 'SK_DIREKTUR')
                    ? 'Termin Kustom Disetujui Direksi'
                    : 'Input Pesanan Manual Telepon / WhatsApp';
            }
            $finalNote = implode(' • ', $verificationNotes);

            $paymentRef = $validated['payment_reference'] ?? ('PAY-' . date('Ymd') . '-' . str_pad((string) $newOrder->id, 4, '0', STR_PAD_LEFT));

            Payment::create([
                'order_id'          => $newOrder->id,
                'invoice_id'        => null,
                'payment_reference' => $paymentRef,
                'payment_method'    => $paymentMethodEnum,
                'amount'            => $total,
                'proof_url'         => $proofPath ?: ($request->input('proof_url') ?: null),
                'status'            => ($orderStatus === 'DRAFT') ? 'MENUNGGU_VERIFIKASI' : 'LUNAS',
                'verified_by'       => $secretary?->id,
                'verification_note' => $finalNote,
                'paid_at'           => now(),
            ]);

            return $newOrder;
        });

        $message = ($orderStatus === 'DRAFT')
            ? "Draf pesanan manual {$order->order_number} berhasil disimpan."
            : "Pesanan manual {$order->order_number} berhasil dibuat dan masuk ke antrean verifikasi.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'message'      => $message,
                'redirect_url' => route('sekretaris.verification'),
                'order'        => $order,
            ]);
        }

        return redirect()->route('sekretaris.verification')
            ->with('success', $message);
    }
}
