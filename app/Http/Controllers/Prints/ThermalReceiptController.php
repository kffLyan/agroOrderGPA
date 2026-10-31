<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ThermalReceiptController extends Controller
{
    /**
     * Tampilkan cetak struk kasir kecil (format thermal POS 58mm / 80mm) untuk Klien Reguler & Transaksi Tunai.
     */
    public function __invoke(Request $request, string $invoice): View
    {
        $invModel = Invoice::with(['orders.orderItems.product', 'orders.payments.verifier', 'user', 'payments.verifier'])
            ->where('invoice_number', $invoice)
            ->orWhere('id', $invoice)
            ->first();

        $orderModel = null;
        if (!$invModel) {
            $orderModel = Order::with(['orderItems.product', 'payments.verifier', 'user', 'invoice'])
                ->where('order_number', $invoice)
                ->orWhere('id', $invoice)
                ->first();

            if ($orderModel && $orderModel->invoice) {
                $invModel = $orderModel->invoice;
            }
        }

        if (!$invModel && !$orderModel) {
            $invModel = Invoice::with(['orders.orderItems.product', 'orders.payments.verifier', 'user', 'payments.verifier'])
                ->latest()
                ->first();
        }

        // Anti-IDOR: Jika yang mengakses adalah Klien, pastikan milik sendiri
        $ownerId = $invModel?->user_id ?? $orderModel?->user_id;
        if (auth()->user()?->role === 'KLIEN' && $ownerId && $ownerId !== auth()->id()) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat struk pelanggan lain.');
        }

        $company = [
            'name'    => 'Green Pasundan Agriculture',
            'legal'   => 'Nomor AHU-0004853.AH.01.29.TH 2024',
            'address' => 'Desa Panundaan, Kec. Ciwidey, Kab. Bandung (40973)',
            'phone'   => '0813-2142-2231',
        ];

        $orders = $invModel ? $invModel->orders : ($orderModel ? collect([$orderModel]) : collect());
        $client = $invModel?->user ?? $orderModel?->user;
        $clientName = $client?->company_name ?: ($client?->name ?: 'Pelanggan Reguler / Walk-In');

        $items = [];
        $subtotal = 0.0;
        foreach ($orders as $ord) {
            foreach ($ord->orderItems as $item) {
                $qty = (float) $item->ordered_qty;
                $price = (float) $item->unit_price;
                $rowAmount = (float) ($item->subtotal ?: ($qty * $price));
                $subtotal += $rowAmount;

                $prodName = $item->product?->name ?: 'Komoditas Sayuran';
                $grade = $item->product?->grade ?: '';
                $fullName = $prodName . ($grade ? " (Gr. {$grade})" : '');

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

        $grandTotal = $invModel
            ? (float) ($invModel->grand_total ?: $subtotal)
            : ($orderModel ? (float) $orderModel->grand_total : $subtotal);

        // Cari transaksi pembayaran terkait (prioritas pembayaran cash / terbaru)
        $payment = null;
        if ($invModel && $invModel->payments->isNotEmpty()) {
            $payment = $invModel->payments->first();
        }
        if (!$payment) {
            foreach ($orders as $ord) {
                if ($ord->payments->isNotEmpty()) {
                    $payment = $ord->payments->first();
                    break;
                }
            }
        }

        $paymentMethod = $payment?->payment_method ?: 'TUNAI';
        $paymentStatus = in_array($payment?->status, ['LUNAS', 'SUCCESS', 'PAID', 'VERIFIED']) ? 'LUNAS' : ($payment?->status ?: 'LUNAS');
        $paymentRef = $payment?->payment_reference ?: ('PAY-' . date('Ymd') . '-' . str_pad((string) ($invModel?->id ?? 1), 4, '0', STR_PAD_LEFT));

        $cashReceived = null;
        $cashChange = null;
        $note = $payment?->verification_note ?: '';
        if (preg_match('/Diterima Rp\s*([\d\.,]+)/i', $note, $mDiterima)) {
            $cashReceived = $mDiterima[1];
        }
        if (preg_match('/Kembalian Rp\s*([\d\.,]+)/i', $note, $mKembalian)) {
            $cashChange = $mKembalian[1];
        }

        // Jika cash tapi belum ada note terurai, estimasi kembalian 0 / pas
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

        $issuedDate = $invModel?->created_at ?: ($orderModel?->created_at ?: now());
        $poNumber = $orders->pluck('order_number')->filter()->implode(', ') ?: ($orderModel?->order_number ?: '-');
        $invoiceNumber = $invModel?->invoice_number ?: ($orderModel?->invoice?->invoice_number ?: ('INV-' . date('Ymd') . '-001'));
        $receiptNumber = 'STRUK-' . preg_replace('/^INV-(GPA-)?/', '', $invoiceNumber);

        $shippingCost = 0.0;
        $taxAmount = 0.0;
        $taxLabel = null;

        if ($orders->isNotEmpty()) {
            foreach ($orders as $ord) {
                $shippingCost += (float) ($ord->shipping_cost ?? 0);
                $taxAmount += (float) ($ord->tax_amount ?? 0);
                if (!$taxLabel && !empty($ord->tax_label)) {
                    $taxLabel = $ord->tax_label;
                }
            }
        }

        if ($shippingCost <= 0 && $taxAmount <= 0 && ($grandTotal - $subtotal) > 0) {
            $shippingCost = $grandTotal - $subtotal;
        }

        $taxLabel = $taxLabel ?: 'Bebas PPN 0% (DTP Pertanian)';

        return view('prints.thermal-receipt', [
            'company'         => $company,
            'receipt_number'  => $receiptNumber,
            'invoice_number'  => $invoiceNumber,
            'po_number'       => $poNumber,
            'date_formatted'  => Carbon::parse($issuedDate)->format('d/m/Y H:i') . ' WIB',
            'cashier'         => $cashier,
            'client_name'     => $clientName,
            'items'           => $items,
            'subtotal'        => $subtotal,
            'shipping_cost'   => $shippingCost,
            'tax_amount'      => $taxAmount,
            'tax_label'       => $taxLabel,
            'grand_total'     => $grandTotal,
            'payment_method'  => strtoupper($paymentMethod),
            'payment_status'  => $paymentStatus,
            'payment_ref'     => $paymentRef,
            'cash_received'   => $cashReceived,
            'cash_change'     => $cashChange,
        ]);
    }
}
