<?php

namespace App\Http\Controllers\Sekretaris;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sekretaris\StoreManualOrderRequest;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\GovernanceService;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controller Input Pesanan Manual (Telepon / WhatsApp / Walk-in) Sekretaris.
 *
 * Penjelasan UKK:
 * Modul ini mengakomodasi pemesanan non-portal dari klien yang menghubungi via WhatsApp atau datang langsung:
 * 1. Pilihan metode bayar fleksibel: Tunai (Cash Kasir), Transfer Bank, QRIS, atau Tempo TOP.
 * 2. Perhitungan otomatis kembalian tunai, PPN 11% (jika aktif), dan ongkos kirim.
 * 3. Penerbitan Invoice instan dan alokasi stok fisik FIFO langsung dari gudang.
 */
class PesananManualController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Menampilkan formulir input order manual via telepon / WhatsApp.
     */
    public function index(Request $request): View
    {
        // Ambil daftar seluruh pembeli / klien terdaftar dengan kontrak dan faktur terutang
        $allClients = User::where('role', 'KLIEN')
            ->with([
                'contracts' => fn($q) => $q->where('status', 'ACTIVE'),
                'invoices'  => fn($q) => $q->whereIn('status', ['UNPAID', 'OVERDUE']),
            ])
            ->get();

        if ($allClients->isEmpty()) {
            $allClients = User::whereIn('role', ['KLIEN', 'SEKRETARIS'])
                ->with([
                    'contracts' => fn($q) => $q->where('status', 'ACTIVE'),
                    'invoices'  => fn($q) => $q->whereIn('status', ['UNPAID', 'OVERDUE']),
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

        // Ambil produk riil dari database dengan batch panen aktif
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

        $governanceService = app(GovernanceService::class);
        $paymentChannels = $governanceService->getActivePaymentChannels();
        if (empty($paymentChannels)) {
            $paymentChannels = $governanceService->defaultPaymentChannels();
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

        return view('sekretaris.pesanan-manual', [
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
     * Menyimpan pesanan manual baru ke database secara riil.
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

        $requestedStatus = $validated['status'] ?? 'LUNAS';
        $isDraft = ($requestedStatus === 'DRAFT');

        // Jika sekretaris memverifikasi di tempat (LUNAS, SIAP_KIRIM, VERIFIKASI)
        $isDirectVerification = !$isDraft && in_array($requestedStatus, ['LUNAS', 'SIAP_KIRIM', 'VERIFIKASI', 'VERIFIED']);

        $paymentMethod = PaymentMethod::fromRaw(
            $validated['payment_method'] ?? null,
            $validated['payment_channel_id'] ?? null
        );
        $paymentMethodEnum = $paymentMethod->value;

        // Tentukan status pesanan
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
            foreach ($validated['items'] as $it) {
                $prod = Product::find($it['product_id']);
                if ($prod) {
                    $qty = (float) $it['quantity'];
                    $price = (float) ($it['price'] ?? $prod->base_price);
                    $sub = $qty * $price;
                    $total += $sub;

                    OrderItem::create([
                        'order_id'    => $newOrder->id,
                        'product_id'  => $prod->id,
                        'ordered_qty' => $qty,
                        'unit_price'  => $price,
                        'subtotal'    => $sub,
                    ]);
                }
            }

            // Ambil kebijakan PPN & Ongkir dari GovernanceService
            $gov = app(GovernanceService::class);
            $taxSettings = $gov->getTaxSettings();

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

            // Jika verifikasi langsung oleh Sekretaris:
            if ($isDirectVerification) {
                // 1. Alokasikan stok panen secara atomik
                $stockService = app(StockService::class);
                foreach ($validated['items'] as $it) {
                    $stockService->allocateStock((int) $it['product_id'], (float) $it['quantity']);
                }

                // 2. Terbitkan Faktur (Invoice) resmi otomatis
                $invNumber = $this->orderService->generateInvoiceNumber();
                $dueDate = ($paymentMethodEnum === 'TEMPO_TOP') ? now()->addDays(14)->toDateString() : now()->toDateString();
                $invoice = Invoice::create([
                    'invoice_number'  => $invNumber,
                    'user_id'         => $client->id,
                    'period_start'    => now()->toDateString(),
                    'period_end'      => $targetDate,
                    'subtotal_amount' => $total,
                    'grand_total'     => $grandTotalFinal,
                    'due_date'        => $dueDate,
                    'status'          => ($paymentMethodEnum === 'TEMPO_TOP') ? 'UNPAID' : 'PAID',
                    'pdf_file_path'   => 'invoices/' . $invNumber . '.pdf',
                ]);
                $newOrder->update(['invoice_id' => $invoice->id]);
            }

            $proofPath = null;
            if ($request->hasFile('proof_file')) {
                $proofPath = $request->file('proof_file')->store('payments', 'public');
            } elseif ($request->hasFile('payment_proof')) {
                $proofPath = $request->file('payment_proof')->store('payments', 'public');
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
                $accInfo = $senderAccount ? " (Rek/No: {$senderAccount})" : '';
                $verificationNotes[] = "Kanal: {$senderBank}{$accInfo}";
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
                    'client_name'  => (!empty($validated['client_name']) && empty($validated['client_id']))
                        ? $validated['client_name']
                        : ($client?->company_name ?: ($client?->name ?: 'Pelanggan Walk-In')),
                    'receipt_url'  => route('prints.thermal-receipt', ['invoice' => $order->order_number]),
                    'invoice_url'  => route('prints.invoice', ['invoice' => $order->order_number]),
                ],
            ]);
        }

        return redirect($redirectUrl)->with('success', $message);
    }
}
