<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
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
            $creditLimit = (float) $c->contracts->sum(
                fn ($ct) => ($ct->committed_volume_per_cycle ?: 0) * ($ct->fixed_price_per_kg ?: 0)
            );

            if ($creditLimit <= 0) {
                $creditLimit = 150000000.0; // Plafon standar B2B 150 Juta
            }

            $usedCredit = (float) $c->invoices->sum('grand_total');

            return [
                'id'           => $c->id,
                'code'         => 'CLI-' . str_pad((string) $c->id, 4, '0', STR_PAD_LEFT),
                'name'         => $c->company_name ?: $c->name,
                'contact'      => $c->pic_name ?: $c->name,
                'phone'        => $c->phone ?: '0812-3456-7890',
                'address'      => $c->address ?: 'Sentra Operasional Klien, Jawa Barat',
                'type'         => $c->client_type ?: 'B2B_KONTRAK',
                'credit_limit' => $creditLimit,
                'credit_used'  => $usedCredit,
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

        $catalog = [];
        foreach ($dbProducts as $prod) {
            $stockBud = (float) $prod->harvestBatches
                ->where('source_type', 'PETANI_BINAAN')
                ->sum('available_quantity');

            $stockBuffer = (float) $prod->harvestBatches
                ->where('source_type', 'BUFFER_LUAR')
                ->sum('available_quantity');

            $totalStock = $stockBud + $stockBuffer;

            $catalog[] = [
                'key'          => 'prod-' . $prod->id,
                'product_id'   => $prod->id,
                'name'         => $prod->name,
                'sku'          => $prod->sku ?: ('GPA-VEG-' . str_pad((string) $prod->id, 3, '0', STR_PAD_LEFT)),
                'grade'        => $prod->grade ?: 'Grade A Pilihan',
                'unit'         => 'kg',
                'min_order'    => (float) ($prod->minimum_order ?: 5),
                'stock'        => $totalStock,
                'stock_bud'    => $stockBud,
                'stock_buffer' => $stockBuffer,
                'price'        => (float) $prod->base_price,
            ];
        }

        $governanceService = app(\App\Services\GovernanceService::class);
        $paymentChannels = $governanceService->getActivePaymentChannels();
        if (empty($paymentChannels)) {
            $paymentChannels = $governanceService->defaultPaymentChannels();
        }

        return view('sekretaris.manual-order', [
            'clientUser'      => $clientUser,
            'clients'         => $clients,
            'commodities'     => [],
            'catalog'         => $catalog,
            'paymentChannels' => $paymentChannels,
        ]);
    }

    /**
     * Menyimpan pesanan manual baru ke database secara riil
     */
    public function store(\App\Http\Requests\Sekretaris\StoreManualOrderRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $secretary = Auth::user();

        // Tentukan akun pembeli
        $clientId = $validated['client_id'] ?? null;
        $client = $clientId ? User::find($clientId) : null;
        if (!$client) {
            $client = User::where('role', 'KLIEN')->first() ?? $secretary;
        }

        // Kepatuhan Tata Kelola: Cek Master Freeze & Kunci Kredit Klien
        $governanceService = app(\App\Services\GovernanceService::class);
        if ($governanceService->isMasterFreezeActive()) {
            $reason = $governanceService->getMasterFreezeReason() ?? 'Darurat Operasional';
            $msg = "Sistem pemesanan sedang dibekukan sementara oleh Direktur Utama ({$reason}).";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['general' => $msg]);
        }

        if ($client && $governanceService->isClientFrozen((int) $client->id)) {
            $msg = "Plafon kredit klien ini sedang dibekukan oleh Direktur Utama. Tidak dapat membuat pesanan baru.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['client_id' => $msg]);
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

            // Buat pencatatan pembayaran/termin yang dipilih
            $payMethodRaw = strtolower((string) ($validated['payment_method'] ?? 'transfer'));
            $channelId = strtolower((string) ($validated['payment_channel_id'] ?? ''));

            $paymentMethodEnum = match (true) {
                $payMethodRaw === 'cash' || $payMethodRaw === 'tunai' || $channelId === 'cash' => 'CASH',
                $payMethodRaw === 'qris' || $channelId === 'qris' => 'QRIS',
                $payMethodRaw === 'cod'                            => 'COD',
                $payMethodRaw === 'top14', $payMethodRaw === 'tempo_top' => 'TEMPO_TOP',
                $payMethodRaw === 'custom', $payMethodRaw === 'sk_direktur' => 'SK_DIREKTUR',
                default                                            => 'TRANSFER_BANK',
            };

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
                'verified_by'       => ($orderStatus === 'MENUNGGU_VERIFIKASI' && $secretary) ? $secretary->id : ($secretary?->id),
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
