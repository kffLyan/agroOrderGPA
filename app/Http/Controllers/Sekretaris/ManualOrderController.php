<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\HarvestBatch;
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
        // Ambil daftar seluruh pembeli / klien terdaftar
        $allClients = User::where('role', 'KLIEN')->get();
        if ($allClients->isEmpty()) {
            $allClients = User::whereIn('role', ['KLIEN', 'SEKRETARIS'])->get();
        }

        $clients = $allClients->map(function ($c) {
            $creditLimit = 0.0;
            $activeContracts = Contract::where('user_id', $c->id)->where('status', 'ACTIVE')->get();
            foreach ($activeContracts as $ct) {
                $creditLimit += (float) (($ct->committed_volume_per_cycle ?: 0) * ($ct->fixed_price_per_kg ?: 0));
            }
            if ($creditLimit <= 0) {
                $creditLimit = 150000000.0; // Plafon standar B2B 150 Juta
            }
            $usedCredit = (float) Invoice::where('user_id', $c->id)
                ->whereIn('status', ['UNPAID', 'OVERDUE'])
                ->sum('grand_total');

            return [
                'id'           => $c->id,
                'code'         => 'CLI-' . str_pad($c->id, 4, '0', STR_PAD_LEFT),
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

        // Ambil produk riil dari database
        $dbProducts = Product::where('is_active', true)->get();
        if ($dbProducts->isEmpty()) {
            $dbProducts = Product::all();
        }

        $commodities = [];
        $catalog = [];

        foreach ($dbProducts as $idx => $prod) {
            $stockBud = (float) HarvestBatch::where('product_id', $prod->id)
                ->where('source_type', 'PETANI_BINAAN')
                ->where('available_quantity', '>', 0)
                ->sum('available_quantity');

            $stockBuffer = (float) HarvestBatch::where('product_id', $prod->id)
                ->where('source_type', 'BUFFER_LUAR')
                ->where('available_quantity', '>', 0)
                ->sum('available_quantity');

            $totalStock = $stockBud + $stockBuffer;

            $entry = [
                'key'          => 'prod-' . $prod->id,
                'product_id'   => $prod->id,
                'name'         => $prod->name,
                'sku'          => $prod->sku ?: ('GPA-VEG-' . str_pad($prod->id, 3, '0', STR_PAD_LEFT)),
                'grade'        => $prod->grade ?: 'Grade A Pilihan',
                'unit'         => 'kg',
                'min_order'    => (float) ($prod->minimum_order ?: 5),
                'stock'        => $totalStock,
                'stock_bud'    => $stockBud,
                'stock_buffer' => $stockBuffer,
                'price'        => (float) $prod->base_price,
            ];

            // Masukkan seluruh komoditas aktif ke katalog agar selalu tersedia di dropdown
            $catalog[] = $entry;
        }

        return view('sekretaris.manual-order', [
            'clientUser'  => $clientUser,
            'clients'     => $clients,
            'commodities' => $commodities,
            'catalog'     => $catalog,
        ]);
    }

    /**
     * Menyimpan pesanan manual baru ke database secara riil
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'client_id'            => 'nullable|exists:users,id',
            'client_name'          => 'nullable|string|max:255',
            'client_phone'         => 'nullable|string|max:50',
            'delivery_address'     => 'nullable|string|max:500',
            'target_delivery_date' => 'nullable|date',
            'order_source'         => 'nullable|string|max:50',
            'source_reference'     => 'nullable|string|max:100',
            'payment_method'       => 'nullable|string|max:50',
            'payment_custom_note'  => 'nullable|string|max:255',
            'fleet_type'           => 'nullable|string|max:100',
            'status'               => 'nullable|string|in:DRAFT,MENUNGGU_VERIFIKASI',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.quantity'     => 'required|numeric|min:0.1',
            'items.*.price'        => 'nullable|numeric|min:0',
        ]);

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
                'verified_by'          => ($orderStatus === 'MENUNGGU_VERIFIKASI') ? null : null,
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

            // Buat pencatatan pembayaran/termin yang dipilih
            $payMethodRaw = strtolower((string) ($validated['payment_method'] ?? 'transfer'));
            $paymentMethodEnum = match ($payMethodRaw) {
                'qris'    => 'QRIS',
                'cod'     => 'COD',
                'top14'   => 'TEMPO_14',
                'custom'  => 'SK_DIREKTUR',
                default   => 'TRANSFER_BANK',
            };

            Payment::create([
                'order_id'          => $newOrder->id,
                'invoice_id'        => null,
                'payment_reference' => 'PAY-' . date('Ymd') . '-' . str_pad($newOrder->id, 4, '0', STR_PAD_LEFT),
                'payment_method'    => $paymentMethodEnum,
                'amount'            => $total,
                'proof_url'         => $request->input('proof_url') ?: 'uploads/proofs/manual-transfer-sample.png',
                'status'            => ($orderStatus === 'DRAFT') ? 'MENUNGGU_VERIFIKASI' : 'LUNAS',
                'verified_by'       => ($orderStatus === 'MENUNGGU_VERIFIKASI' && $secretary) ? $secretary->id : null,
                'verification_note' => $validated['payment_custom_note'] ?? ($paymentMethodEnum === 'SK_DIREKTUR' ? 'Termin Kustom Disetujui Direksi' : 'Verifikasi Otomatis Input Manual'),
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
