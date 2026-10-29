<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Exception;

// Layanan utama proses pemesanan (buat pesanan, verifikasi, potong stok, faktur)
class OrderService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    // Buat nomor pesanan otomatis berurutan dengan proteksi anti-bentrok (contoh: ORD-GPA-202610-0001)
    public function generateOrderNumber(): string
    {
        $prefix = 'ORD-GPA-' . date('Ym') . '-';

        return Cache::lock('order_number_sequence_lock', 5)->block(3, function () use ($prefix) {
            $lastOrder = Order::where('order_number', 'LIKE', $prefix . '%')
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = 1;
            if ($lastOrder) {
                $lastSequence = (int) substr($lastOrder->order_number, -4);
                $nextNumber = $lastSequence + 1;
            }

            $candidate = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            // Pengaman ekstra: Jika nomor sudah terpakai di database, cari urutan berikutnya
            while (Order::where('order_number', $candidate)->exists()) {
                $nextNumber++;
                $candidate = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }

            return $candidate;
        });
    }

    // Resolusi harga satuan: Harga Kontrak B2B aktif vs Harga Katalog
    public function resolveUnitPrice(User $user, Product $product): float
    {
        $activeContract = Contract::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('status', 'ACTIVE')
            ->first();

        if ($activeContract) {
            return (float) $activeContract->fixed_price_per_kg;
        }

        return (float) $product->base_price;
    }

    /**
     * Memeriksa apakah ada pesanan serupa yang baru saja diajukan (< 90 detik)
     * untuk mencegah duplikasi pesanan (idempotency fallback).
     */
    public function findRecentDuplicateOrder(User $user, array $data): ?Order
    {
        $targetDate = $data['target_delivery_date'] ?? null;
        if (!$targetDate || empty($data['items'])) {
            return null;
        }

        $recentOrders = Order::where('user_id', $user->id)
            ->where('status', OrderStatus::MENUNGGU_VERIFIKASI->value)
            ->whereDate('target_delivery_date', $targetDate)
            ->where('created_at', '>=', now()->subSeconds(90))
            ->with('orderItems')
            ->orderBy('id', 'desc')
            ->get();

        $incomingItems = collect($data['items'])->map(fn($item) => [
            'product_id' => (int) $item['product_id'],
            'quantity'   => (float) $item['quantity'],
        ])->sortBy('product_id')->values()->toArray();

        foreach ($recentOrders as $order) {
            $existingItems = $order->orderItems->map(fn($item) => [
                'product_id' => (int) $item->product_id,
                'quantity'   => (float) $item->ordered_qty,
            ])->sortBy('product_id')->values()->toArray();

            if ($existingItems == $incomingItems) {
                return $order;
            }
        }

        return null;
    }

    /**
     * Menyimpan transaksi pesanan baru dalam siklus ACID
     *
     * @throws Exception
     */
    public function createOrder(User $user, array $data, ?string $proofPath = null): Order
    {
        return DB::transaction(function () use ($user, $data, $proofPath) {
            $orderNumber = $this->generateOrderNumber();
            $estimatedTotal = 0.0;
            $itemsToInsert = [];

            // 1. Validasi ketersediaan stok panen bebas (diagregasikan per produk agar tidak bisa diakali dengan item duplikat)
            $productTotals = [];
            foreach ($data['items'] as $itemData) {
                $pid = (int) $itemData['product_id'];
                $qty = (float) $itemData['quantity'];
                $productTotals[$pid] = ($productTotals[$pid] ?? 0.0) + $qty;
            }

            foreach ($productTotals as $productId => $totalRequestedQty) {
                $product = Product::findOrFail($productId);
                if (!$this->stockService->isStockSufficient($product->id, $totalRequestedQty)) {
                    $availableStock = $this->stockService->getAvailableStock($product->id);
                    throw new Exception("Sisa panen komoditas {$product->name} tidak mencukupi. Kuota tersedia: {$availableStock} {$product->unit}.");
                }
            }

            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $requestedQty = (float) $itemData['quantity'];
                $unitPrice = $this->resolveUnitPrice($user, $product);
                $subtotal = $unitPrice * $requestedQty;
                $estimatedTotal += $subtotal;

                $itemsToInsert[] = [
                    'product_id'  => $product->id,
                    'ordered_qty' => $requestedQty,
                    'unit_price'  => $unitPrice,
                ];
            }

            // 2. Simpan data header pesanan (Status awal MENUNGGU_VERIFIKASI)
            $order = Order::create([
                'order_number'         => $orderNumber,
                'user_id'              => $user->id,
                'order_source'         => 'WEB_PORTAL',
                'target_delivery_date' => $data['target_delivery_date'],
                'delivery_address'     => $data['delivery_address'],
                'grand_total'          => $estimatedTotal,
                'status'               => OrderStatus::MENUNGGU_VERIFIKASI->value,
            ]);

            // 3. Simpan baris detail komoditas
            foreach ($itemsToInsert as $item) {
                OrderItem::create([
                    'order_id'        => $order->id,
                    'product_id'      => $item['product_id'],
                    'ordered_qty'     => $item['ordered_qty'],
                    'unit_price'      => $item['unit_price'],
                    'subtotal'        => $item['unit_price'] * $item['ordered_qty'],
                    'returned_weight' => 0.00,
                    'return_reason'   => null,
                ]);
            }

            // 4. Catat entitas transaksi pembayaran awal
            if (!empty($data['payment_method'])) {
                Payment::create([
                    'order_id'          => $order->id,
                    'invoice_id'        => null,
                    'payment_reference' => 'PAY-' . strtoupper(uniqid()),
                    'payment_method'    => $data['payment_method'],
                    'amount'            => $estimatedTotal,
                    'proof_url'         => $proofPath,
                    'status'            => PaymentStatus::MENUNGGU_VERIFIKASI->value,
                    'paid_at'           => ($data['payment_method'] === 'TEMPO_TOP' || empty($proofPath)) ? null : now(),
                ]);
            }

            return $order;
        });
    }

    /**
     * Persetujuan pesanan oleh Sekretaris & pemotongan kuota stok
     * Sesuai alur: Terbitkan Faktur (Invoice) & status berubah menjadi MENUNGGU_PEMBAYARAN
     *
     * @throws Exception
     */
    public function approveOrder(int $orderId, int $secretaryId): Order
    {
        return DB::transaction(function () use ($orderId, $secretaryId) {
            $order = Order::with('orderItems')->lockForUpdate()->findOrFail($orderId);

            if ($order->status !== OrderStatus::MENUNGGU_VERIFIKASI->value) {
                throw new Exception("Hanya pesanan berstatus MENUNGGU_VERIFIKASI yang dapat disetujui.");
            }

            // Potong kuota stok batch panen secara atomik (SELECT ... FOR UPDATE)
            foreach ($order->orderItems as $item) {
                $this->stockService->allocateStock($item->product_id, (float) $item->ordered_qty);
            }

            // 1. Buat / Terbitkan Invoice Tagihan PDF resmi jika belum ada
            if (!$order->invoice_id) {
                $prefix = 'INV-GPA-' . date('Ym') . '-';
                $lastInv = Invoice::where('invoice_number', 'LIKE', $prefix . '%')
                    ->orderBy('id', 'desc')
                    ->first();
                $seq = 1;
                if ($lastInv) {
                    $seq = ((int) substr($lastInv->invoice_number, -4)) + 1;
                }
                $invNumber = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);

                // Periksa TOP days jika klien kontrak B2B
                $clientContract = Contract::where('user_id', $order->user_id)
                    ->where('status', 'ACTIVE')
                    ->first();
                $topDays = $clientContract ? (int) $clientContract->top_days : 14;
                $dueDate = now()->addDays($topDays)->toDateString();

                $invoice = Invoice::create([
                    'invoice_number'  => $invNumber,
                    'user_id'         => $order->user_id,
                    'period_start'    => $order->created_at ? $order->created_at->toDateString() : now()->toDateString(),
                    'period_end'      => $order->target_delivery_date ?: now()->toDateString(),
                    'subtotal_amount' => (float) $order->grand_total,
                    'grand_total'     => (float) $order->grand_total,
                    'due_date'        => $dueDate,
                    'status'          => 'UNPAID',
                    'pdf_file_path'   => 'invoices/' . $invNumber . '.pdf',
                ]);

                $order->invoice_id = $invoice->id;

                // Tautkan invoice_id pada pembayaran terkait
                Payment::where('order_id', $order->id)->whereNull('invoice_id')->update([
                    'invoice_id' => $invoice->id,
                ]);
            }

            // 2. Perbarui status pesanan menjadi MENUNGGU_PEMBAYARAN sesuai MODELING_SUMMARY.md
            $order->status = OrderStatus::MENUNGGU_PEMBAYARAN->value;
            $order->verified_by = $secretaryId;
            $order->save();

            return $order;
        });
    }

    /**
     * Pembatalan pesanan oleh Sekretaris
     *
     * @throws Exception
     */
    public function rejectOrder(int $orderId, int $secretaryId, string $reason): Order
    {
        return DB::transaction(function () use ($orderId, $secretaryId, $reason) {
            $order = Order::lockForUpdate()->findOrFail($orderId);

            if ($order->status !== OrderStatus::MENUNGGU_VERIFIKASI->value) {
                throw new Exception("Hanya pesanan berstatus MENUNGGU_VERIFIKASI yang dapat dibatalkan.");
            }

            $order->update([
                'status'      => OrderStatus::BATAL->value,
                'verified_by' => $secretaryId,
            ]);

            // Simpan catatan alasan penolakan pada Payment yang ada tanpa memodifikasi skema tabel database
            $payment = Payment::where('order_id', $order->id)->first();
            if ($payment) {
                $payment->update([
                    'status'            => PaymentStatus::DITOLAK->value,
                    'verified_by'       => $secretaryId,
                    'verification_note' => $reason,
                ]);
            } else {
                Payment::create([
                    'order_id'          => $order->id,
                    'invoice_id'        => $order->invoice_id,
                    'payment_reference' => 'REJ-' . strtoupper(uniqid()),
                    'payment_method'    => 'TRANSFER_BANK',
                    'amount'            => $order->grand_total,
                    'status'            => PaymentStatus::DITOLAK->value,
                    'verified_by'       => $secretaryId,
                    'verification_note' => $reason,
                ]);
            }

            return $order;
        });
    }
}
