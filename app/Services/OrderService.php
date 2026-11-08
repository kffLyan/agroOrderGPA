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
use App\Services\TataKelolaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Exception;

// Layanan orkestrasi transaksi pemesanan komoditas B2B, verifikasi sekretariat, dan penerbitan faktur.
class OrderService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    // Menghasilkan nomor pesanan unik berurutan bulanan dengan proteksi race-condition melalui lock cache.
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

            $newOrderNumber = $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);

            // Pengaman ekstra jika kandidat nomor pesanan sudah terpakai di database
            while (Order::where('order_number', $newOrderNumber)->exists()) {
                $nextNumber++;
                $newOrderNumber = $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            }

            return $newOrderNumber;
        });
    }

    // Menghasilkan nomor faktur komoditas berurutan bulanan dengan proteksi race-condition melalui lock cache.
    public function generateInvoiceNumber(): string
    {
        $prefix = 'INV-GPA-' . date('Ym') . '-';

        return Cache::lock('invoice_number_sequence_lock', 5)->block(3, function () use ($prefix) {
            $lastInvoice = Invoice::where('invoice_number', 'LIKE', $prefix . '%')
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = 1;
            if ($lastInvoice) {
                $lastSequence = (int) substr($lastInvoice->invoice_number, -4);
                $nextNumber = $lastSequence + 1;
            }

            $newInvoiceNumber = $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);

            // Pengaman ekstra jika kandidat nomor faktur sudah terpakai di database
            while (Invoice::where('invoice_number', $newInvoiceNumber)->exists()) {
                $nextNumber++;
                $newInvoiceNumber = $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            }

            return $newInvoiceNumber;
        });
    }

    // Menentukan harga satuan komoditas berdasarkan kontrak B2B aktif atau harga dasar katalog.
    public function resolveUnitPrice(User $user, Product $product): float
    {
        $contract = Contract::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('status', 'ACTIVE')
            ->first();

        if ($contract) {
            return (float) $contract->fixed_price_per_kg;
        }

        return (float) $product->base_price;
    }

    // Memeriksa riwayat pesanan identik dalam jendela waktu 90 detik untuk mencegah duplikasi pemesanan.
    public function findRecentDuplicateOrder(User $user, array $orderData): ?Order
    {
        $deliveryDate = $orderData['target_delivery_date'] ?? null;
        if (!$deliveryDate || empty($orderData['items'])) {
            return null;
        }

        $recentOrders = Order::where('user_id', $user->id)
            ->where('status', OrderStatus::MENUNGGU_VERIFIKASI->value)
            ->whereDate('target_delivery_date', $deliveryDate)
            ->where('created_at', '>=', now()->subSeconds(90))
            ->with('orderItems')
            ->orderBy('id', 'desc')
            ->get();

        $incomingItems = collect($orderData['items'])->map(fn($item) => [
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

    // Menyimpan transaksi pesanan baru, item komoditas, dan rekaman pembayaran dalam transaksi database.
    public function createOrder(User $user, array $orderData, ?string $proofPath = null): Order
    {
        return DB::transaction(function () use ($user, $orderData, $proofPath) {
            $orderNumber = $this->generateOrderNumber();
            $subtotal = 0.0;
            $items = [];

            // 1. Validasi stok panen di gudang
            $productTotals = [];
            foreach ($orderData['items'] as $itemData) {
                $productId = (int) $itemData['product_id'];
                $qty = (float) $itemData['quantity'];
                $productTotals[$productId] = ($productTotals[$productId] ?? 0.0) + $qty;
            }

            foreach ($productTotals as $productId => $neededQty) {
                $product = Product::findOrFail($productId);
                if (!$this->stockService->isStockSufficient($product->id, $neededQty)) {
                    $available = $this->stockService->getAvailableStock($product->id);
                    throw new Exception("Sisa panen komoditas {$product->name} tidak mencukupi. Kuota tersedia: {$available} {$product->unit}.");
                }
            }

            // Hitung harga satuan dan subtotal tiap komoditas
            foreach ($orderData['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $qty = (float) $itemData['quantity'];
                $unitPrice = $this->resolveUnitPrice($user, $product);
                $itemSubtotal = $unitPrice * $qty;
                $subtotal += $itemSubtotal;

                $items[] = [
                    'product_id'  => $product->id,
                    'ordered_qty' => $qty,
                    'unit_price'  => $unitPrice,
                ];
            }

            // Ambil pengaturan PPN dan ongkir dari Tata Kelola
            $tataKelolaService = app(TataKelolaService::class);
            $taxSettings = $tataKelolaService->getTaxSettings();

            $shippingCost = 0.0;
            if (isset($orderData['shipping_cost']) && is_numeric($orderData['shipping_cost'])) {
                $shippingCost = max(0.0, (float) $orderData['shipping_cost']);
            }

            $taxPercent = 0.0;
            $taxAmount = 0.0;
            if ($taxSettings['enabled']) {
                $taxPercent = (float) $taxSettings['percent'];
                $taxAmount = round($subtotal * ($taxPercent / 100), 2);
            }

            $grandTotal = $subtotal + $shippingCost + $taxAmount;

            // 2. Simpan data header pesanan (status awal: MENUNGGU_VERIFIKASI)
            $order = Order::create([
                'order_number'         => $orderNumber,
                'user_id'              => $user->id,
                'order_source'         => 'WEB_PORTAL',
                'target_delivery_date' => $orderData['target_delivery_date'],
                'delivery_address'     => $orderData['delivery_address'],
                'grand_total'          => $grandTotal,
                'status'               => OrderStatus::MENUNGGU_VERIFIKASI->value,
            ]);

            // 3. Simpan rincian komoditas sayur dengan harga terkunci saat transaksi
            foreach ($items as $item) {
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

            // 4. Buat catatan pembayaran awal
            if (!empty($orderData['payment_method'])) {
                $note = "Rincian Biaya: Subtotal Rp " . number_format($subtotal, 0, ',', '.')
                    . " • Ongkir Rp " . number_format($shippingCost, 0, ',', '.')
                    . ($taxPercent > 0 ? " • PPN ({$taxPercent}%) Rp " . number_format($taxAmount, 0, ',', '.') : " • Bebas PPN (0% DTP)");

                Payment::create([
                    'order_id'          => $order->id,
                    'invoice_id'        => null,
                    'payment_reference' => 'PAY-' . strtoupper(uniqid()),
                    'payment_method'    => $orderData['payment_method'],
                    'amount'            => $grandTotal,
                    'proof_url'         => $proofPath,
                    'status'            => PaymentStatus::MENUNGGU_VERIFIKASI->value,
                    'verification_note' => $note,
                    'paid_at'           => ($orderData['payment_method'] === 'TEMPO_TOP' || empty($proofPath)) ? null : now(),
                ]);
            }

            return $order;
        });
    }

    // Menyetujui pesanan oleh Sekretaris, mengalokasikan stok panen gudang, dan menerbitkan faktur tagihan.
    public function approveOrder(int $orderId, int $secretaryId, ?float $shippingCost = null): Order
    {
        return DB::transaction(function () use ($orderId, $secretaryId, $shippingCost) {
            $order = Order::with('orderItems')->lockForUpdate()->findOrFail($orderId);

            if ($order->status !== OrderStatus::MENUNGGU_VERIFIKASI->value) {
                throw new Exception("Hanya pesanan berstatus MENUNGGU_VERIFIKASI yang dapat disetujui.");
            }

            // Validasi kepatuhan tata kelola: master freeze dan pembekuan akun klien
            $tataKelolaService = app(TataKelolaService::class);
            if ($tataKelolaService->isMasterFreezeActive()) {
                $freezeReason = $tataKelolaService->getMasterFreezeReason() ?? 'Darurat Operasional';
                throw new Exception("Persetujuan ditolak: Sistem transaksi sedang dalam status Master Freeze darurat ({$freezeReason}).");
            }
            if ($order->user_id && $tataKelolaService->isClientFrozen((int) $order->user_id)) {
                throw new Exception("Persetujuan ditolak: Akun plafon kredit klien ini sedang DIBEKUKAN sementara oleh Direktur Utama.");
            }

            // Mengurangi kuota stok panen komoditas di gudang sesuai rincian kuantitas pesanan
            foreach ($order->orderItems as $item) {
                $this->stockService->allocateStock($item->product_id, (float) $item->ordered_qty);
            }

            // Menghitung ulang subtotal komoditas dari rincian item pesanan yang disetujui
            $subtotal = (float) $order->orderItems->sum(fn ($item) => ($item->subtotal ?: ($item->ordered_qty * $item->unit_price)));
            if ($subtotal <= 0) {
                $subtotal = (float) $order->grand_total;
            }

            // Mengambil parameter konfigurasi pajak PPN aktif dari tata kelola koperasi
            $tataKelolaService = app(TataKelolaService::class);
            $taxSettings = $tataKelolaService->getTaxSettings();
            $taxPercent = 0.0;
            $taxAmount = 0.0;
            if ($taxSettings['enabled']) {
                $taxPercent = (float) $taxSettings['percent'];
                $taxAmount = round($subtotal * ($taxPercent / 100), 2);
            }

            // Memperbarui tarif ongkir dan merevisi grand total jika disesuaikan manual oleh Sekretaris
            if ($shippingCost !== null) {
                $shipping = max(0.0, (float) $shippingCost);
                $grandTotal = $subtotal + $shipping + $taxAmount;
                $order->grand_total = $grandTotal;

                $note = "Rincian Biaya: Subtotal Rp " . number_format($subtotal, 0, ',', '.')
                    . " • Ongkir Rp " . number_format($shipping, 0, ',', '.')
                    . ($taxPercent > 0 ? " • PPN ({$taxPercent}%) Rp " . number_format($taxAmount, 0, ',', '.') : " • Bebas PPN (0% DTP)");

                $payment = Payment::where('order_id', $order->id)->first();
                if ($payment) {
                    $payment->update([
                        'amount'            => $grandTotal,
                        'verification_note' => $note,
                    ]);
                }
            }

            // Menerbitkan dokumen faktur komoditas baru jika pesanan belum memiliki faktur
            if (!$order->invoice_id) {
                $invoiceNumber = $this->generateInvoiceNumber();

                $contract = Contract::where('user_id', $order->user_id)
                    ->where('status', 'ACTIVE')
                    ->first();
                $topDays = $contract ? (int) $contract->top_days : 14;
                $dueDate = now()->addDays($topDays)->toDateString();

                $invoice = Invoice::create([
                    'invoice_number'  => $invoiceNumber,
                    'user_id'         => $order->user_id,
                    'period_start'    => $order->created_at ? $order->created_at->toDateString() : now()->toDateString(),
                    'period_end'      => $order->target_delivery_date ?: now()->toDateString(),
                    'subtotal_amount' => $subtotal,
                    'grand_total'     => (float) $order->grand_total,
                    'due_date'        => $dueDate,
                    'status'          => 'UNPAID',
                    'pdf_file_path'   => 'invoices/' . $invoiceNumber . '.pdf',
                ]);

                $order->invoice_id = $invoice->id;

                Payment::where('order_id', $order->id)->whereNull('invoice_id')->update([
                    'invoice_id' => $invoice->id,
                ]);
            } else {
                $existingInvoice = Invoice::find($order->invoice_id);
                if ($existingInvoice) {
                    $existingInvoice->update([
                        'subtotal_amount' => $subtotal,
                        'grand_total'     => (float) $order->grand_total,
                    ]);
                }
            }

            // Memperbarui status pesanan menjadi menunggu pembayaran dan mencatat verifikator
            $order->status = OrderStatus::MENUNGGU_PEMBAYARAN->value;
            $order->verified_by = $secretaryId;
            $order->save();

            return $order;
        });
    }

    // Menolak atau membatalkan pesanan serta mengembalikan kuota alokasi stok panen gudang.
    public function rejectOrder(int $orderId, int $secretaryId, string $reason): Order
    {
        return DB::transaction(function () use ($orderId, $secretaryId, $reason) {
            $order = Order::lockForUpdate()->findOrFail($orderId);

            if ($order->status !== OrderStatus::MENUNGGU_VERIFIKASI->value && $order->status !== OrderStatus::MENUNGGU_PEMBAYARAN->value) {
                throw new Exception("Hanya pesanan yang belum lunas yang dapat dibatalkan.");
            }

            // Kembalikan alokasi stok ke gudang jika sebelumnya sudah disetujui
            if ($order->status === OrderStatus::MENUNGGU_PEMBAYARAN->value) {
                foreach ($order->orderItems as $item) {
                    $this->stockService->restoreStock($item->product_id, (float) $item->ordered_qty);
                }
            }

            $order->update([
                'status'      => OrderStatus::BATAL->value,
                'verified_by' => $secretaryId,
            ]);

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
