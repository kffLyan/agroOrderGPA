<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PrintStrukThermalController extends Controller
{
    // Menyiapkan cetak struk kasir format printer thermal POS (58mm/80mm) untuk transaksi tunai kasir
    public function __invoke(Request $request, string $invoice): View
    {
        $invoiceModel = Invoice::with(['orders.orderItems.product', 'orders.payments.verifier', 'user', 'payments.verifier'])
            ->where('invoice_number', $invoice)
            ->orWhere('id', $invoice)
            ->first();

        $orderModel = null;
        if (!$invoiceModel) {
            $orderModel = Order::with(['orderItems.product', 'payments.verifier', 'user', 'invoice'])
                ->where('order_number', $invoice)
                ->orWhere('id', $invoice)
                ->first();

            if ($orderModel && $orderModel->invoice) {
                $invoiceModel = $orderModel->invoice;
            }
        }

        if (!$invoiceModel && !$orderModel) {
            $invoiceModel = Invoice::with(['orders.orderItems.product', 'orders.payments.verifier', 'user', 'payments.verifier'])
                ->latest()
                ->first();
        }

        // Memvalidasi kepemilikan transaksi (Anti-IDOR) agar klien hanya dapat mencetak struk miliknya
        $ownerId = $invoiceModel?->user_id ?? $orderModel?->user_id;
        if (auth()->user()?->role === 'KLIEN' && $ownerId && $ownerId !== auth()->id()) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat struk pelanggan lain.');
        }

        $company = [
            'name'    => 'Green Pasundan Agriculture',
            'legal'   => 'Nomor AHU-0004853.AH.01.29.TH 2024',
            'address' => 'Desa Panundaan, Kec. Ciwidey, Kab. Bandung (40973)',
            'phone'   => '0813-2142-2231',
        ];

        $orders = $invoiceModel ? $invoiceModel->orders : ($orderModel ? collect([$orderModel]) : collect());
        $client = $invoiceModel?->user ?? $orderModel?->user;
        $clientName = $client?->company_name ?: ($client?->name ?: 'Pelanggan Reguler / Walk-In');

        $items = [];
        $subtotal = 0.0;
        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $qty = (float) $item->ordered_qty;
                $price = (float) $item->unit_price;
                $rowAmount = (float) ($item->subtotal ?: ($qty * $price));
                $subtotal += $rowAmount;

                $productName = $item->product?->name ?: 'Komoditas Sayuran';
                $grade = $item->product?->grade ?: '';
                $fullName = $productName . ($grade ? " (Gr. {$grade})" : '');

                $items[] = [
                    'name'     => $fullName,
                    'qty'      => $qty,
                    'qty_fmt'  => number_format($qty, 1, ',', '.') . ' kg',
                    'price'    => $price,
                    'price_fmt'=> number_format($price, 0, ',', '.'),
                    'total'    => $rowAmount,
                    'total_fmt'=> number_format($rowAmount, 0, ',', '.'),
                ];
            }
        }

        $grandTotal = $invoiceModel
            ? (float) ($invoiceModel->grand_total ?: $subtotal)
            : ($orderModel ? (float) $orderModel->grand_total : $subtotal);

        // Mencari rekonsiliasi pembayaran tunai kasir yang terhubung dengan transaksi ini
        $payment = null;
        if ($invoiceModel && $invoiceModel->payments->isNotEmpty()) {
            $payment = $invoiceModel->payments->first();
        }
        if (!$payment) {
            foreach ($orders as $order) {
                if ($order->payments->isNotEmpty()) {
                    $payment = $order->payments->first();
                    break;
                }
            }
        }

        $paymentMethod = $payment?->payment_method ?: 'TUNAI';
        $paymentStatus = in_array($payment?->status, ['LUNAS', 'SUCCESS', 'PAID', 'VERIFIED']) ? 'LUNAS' : ($payment?->status ?: 'LUNAS');
        $paymentRef = $payment?->payment_reference ?: ('PAY-' . date('Ymd') . '-' . str_pad((string) ($invoiceModel?->id ?? 1), 4, '0', STR_PAD_LEFT));

        $cashReceived = null;
        $cashChange = null;
        $note = $payment?->verification_note ?: '';
        if (preg_match('/Diterima Rp\s*([\d\.,]+)/i', $note, $matchesReceived)) {
            $cashReceived = $matchesReceived[1];
        }
        if (preg_match('/Kembalian Rp\s*([\d\.,]+)/i', $note, $matchesChange)) {
            $cashChange = $matchesChange[1];
        }

        // Mengestimasi nominal uang diterima dan kembalian jika rincian belum terurai otomatis
        if (strtoupper($paymentMethod) === 'CASH' || strtoupper($paymentMethod) === 'TUNAI') {
            if (!$cashReceived) {
                $cashReceived = number_format($grandTotal, 0, ',', '.');
            }
            if (!$cashChange) {
                $cashChange = '0';
            }
        }

        $cashier = $payment?->verifier?->name
            ?: ($orders->first()?->verifier?->name
                ?: (auth()->user()?->role === 'SEKRETARIS' ? auth()->user()->name : 'Kasir Sekretariat GPA'));

        $issuedDate = $invoiceModel?->created_at ?: ($orderModel?->created_at ?: now());
        $poNumber = $orders->pluck('order_number')->filter()->implode(', ') ?: ($orderModel?->order_number ?: '-');
        $invoiceNumber = $invoiceModel?->invoice_number ?: ($orderModel?->invoice?->invoice_number ?: ('INV-' . date('Ymd') . '-001'));
        $receiptNumber = 'STRUK-' . preg_replace('/^INV-(GPA-)?/', '', $invoiceNumber);

        $shippingCost = 0.0;
        $taxAmount = 0.0;
        $taxLabel = null;

        if ($orders->isNotEmpty()) {
            foreach ($orders as $order) {
                $shippingCost += (float) ($order->shipping_cost ?? 0);
                $taxAmount += (float) ($order->tax_amount ?? 0);
                if (!$taxLabel && !empty($order->tax_label)) {
                    $taxLabel = $order->tax_label;
                }
            }
        }

        if ($shippingCost <= 0 && $taxAmount <= 0 && ($grandTotal - $subtotal) > 0) {
            $shippingCost = $grandTotal - $subtotal;
        }

        $taxLabel = $taxLabel ?: 'Bebas PPN 0% (DTP Pertanian)';

        $paymentMethodLabel = match (strtoupper($paymentMethod)) {
            'TRANSFER_BANK' => 'Transfer Bank',
            'QRIS'          => 'QRIS Instan',
            'CASH', 'TUNAI' => 'Tunai Kasir',
            'TEMPO_TOP', 'TOP' => 'Kredit Tempo (TOP)',
            'COD'           => 'COD (Bayar di Tempat)',
            'SK_DIREKTUR'   => 'Kebijakan Khusus Direktur',
            default         => ucwords(strtolower(str_replace('_', ' ', $paymentMethod))),
        };

        $paymentStatusLabel = match (strtoupper($paymentStatus)) {
            'LUNAS', 'SUCCESS', 'PAID', 'VERIFIED' => 'Lunas Sah',
            'MENUNGGU_VERIFIKASI', 'PENDING'       => 'Menunggu Verifikasi',
            'MENUNGGU_PEMBAYARAN'                  => 'Menunggu Pembayaran',
            'DITOLAK', 'REJECTED'                  => 'Ditolak',
            'BATAL', 'CANCELLED'                   => 'Dibatalkan',
            default                                => ucwords(strtolower(str_replace('_', ' ', $paymentStatus))),
        };

        return view('prints.thermal-receipt', [
            'company'              => $company,
            'receipt_number'       => $receiptNumber,
            'invoice_number'       => $invoiceNumber,
            'po_number'            => $poNumber,
            'date_formatted'       => Carbon::parse($issuedDate)->format('d/m/Y H:i') . ' WIB',
            'cashier'              => $cashier,
            'client_name'          => $clientName,
            'items'                => $items,
            'subtotal'             => $subtotal,
            'shipping_cost'        => $shippingCost,
            'tax_amount'           => $taxAmount,
            'tax_label'            => $taxLabel,
            'grand_total'          => $grandTotal,
            'payment_method'       => strtoupper($paymentMethod),
            'payment_method_label' => $paymentMethodLabel,
            'payment_status'       => $paymentStatus,
            'payment_status_label' => $paymentStatusLabel,
            'payment_ref'          => $paymentRef,
            'cash_received'        => $cashReceived,
            'cash_change'          => $cashChange,
        ]);
    }
}
