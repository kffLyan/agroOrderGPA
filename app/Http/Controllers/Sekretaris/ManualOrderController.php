<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\HarvestBatch;
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
        $clientUser = User::where('client_type', 'B2B_KONTRAK')->first()
            ?? User::where('role', 'KLIEN')->first();

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
                'min_order'    => (float) ($prod->minimum_order ?: 1),
                'stock'        => $totalStock,
                'stock_bud'    => $stockBud,
                'stock_buffer' => $stockBuffer,
                'price'        => (float) $prod->base_price,
            ];

            if ($idx < 3) {
                $commodities[] = array_merge($entry, [
                    'quantity' => (float) max(1, $prod->minimum_order ?: 1),
                ]);
            } else {
                $catalog[] = $entry;
            }
        }

        return view('sekretaris.manual-order', [
            'clientUser'  => $clientUser,
            'commodities' => $commodities,
            'catalog'     => $catalog,
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
