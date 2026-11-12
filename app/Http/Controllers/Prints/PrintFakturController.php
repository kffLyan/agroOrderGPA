<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PrintFakturController extends Controller
{
    // Menyiapkan cetak faktur penagihan resmi (invoice) format standar resmi GPA
    public function __invoke(Request $request, string $invoice): View
    {
        $invoiceModel = Invoice::where('invoice_number', $invoice)
            ->orWhere('id', $invoice)
            ->orWhereHas('orders', fn ($q) => $q->where('order_number', $invoice))
            ->first();

        // Memvalidasi kepemilikan faktur (Anti-IDOR) agar klien hanya dapat mencetak fakturnya sendiri
        if (auth()->user()?->role === 'KLIEN' && (!$invoiceModel || $invoiceModel->user_id !== auth()->id())) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat faktur pelanggan lain.');
        }

        $data = $this->buildPrintData($invoice);

        return view('prints.faktur', $data);
    }

    // Membangun payload data terstruktur untuk lembar cetak faktur tagihan resmi standar tunggal GPA
    public function buildPrintData(string $invoice, ?string $requestedFormat = null): array
    {
        $invoiceModel = Invoice::with(['user.contracts.product', 'orders.orderItems.product', 'payments'])
            ->where('invoice_number', $invoice)
            ->orWhere('id', $invoice)
            ->orWhereHas('orders', fn ($q) => $q->where('order_number', $invoice))
            ->first();

        if (!$invoiceModel) {
            $invoiceModel = Invoice::with(['user.contracts.product', 'orders.orderItems.product', 'payments'])
                ->latest()
                ->first();
        }

        $company = [
            'brand'        => 'GREEN PASUNDAN AGRICULTURE',
            'full_brand'   => 'KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)',
            'legal_number' => 'Nomor AHU-0004853.AH.01.29.TH 2024',
            'address'      => 'Kp. Lebakwangi RT. 001 RW. 009 Desa Panundaan Kecamatan Ciwidey Kabupaten Bandung (40973)',
            'phone'        => '081321422231',
            'bank_name'    => 'BCA',
            'bank_account' => '6768489174',
            'bank_holder'  => 'Green Pasundan Agriculture',
            'signer_name'  => 'AGUS DIKI PERMANA',
            'signer_role'  => 'Koperasi Produsen Green Pasundan Agriculture',
        ];

        $formatDateIndo = fn ($date) => $date ? Carbon::parse($date)->translatedFormat('l, d F Y') : '-';

        $client             = $invoiceModel?->user;
        $contracts          = $client?->contracts ? $client->contracts->where('status', 'ACTIVE') : collect();
        $isContractClient   = ($client?->client_type === 'B2B_KONTRAK' || $contracts->isNotEmpty());
        $clientTypeLabel    = $isContractClient ? 'Mitra B2B Kontrak' : 'Pelanggan Reguler';

        $clientName         = $client?->company_name ?: ($client?->name ?: ($invoiceModel ? 'Mitra Klien' : '-'));
        $picName            = $client?->pic_name ?: ($client?->name ?: ($invoiceModel ? 'Pimpinan Purchasing' : '-'));
        $officeName         = $client?->company_name ?: ($isContractClient ? ($client?->name ?: '-') : 'Pelanggan Reguler / Perorangan');

        $poNumbers = $invoiceModel ? $invoiceModel->orders->pluck('order_number')->filter()->unique()->values() : collect();
        $poDisplay = $poNumbers->isNotEmpty()
            ? $poNumbers->implode(', ')
            : ($invoiceModel ? ('PO-' . $invoiceModel->invoice_number) : '-');

        $issuedDate = $invoiceModel?->created_at ?: now();
        $dueDate    = $invoiceModel?->due_date ?: $issuedDate->copy()->addDays(14);

        $items            = [];
        $totalItemsAmount = 0.0;

        if ($invoiceModel) {
            $orders = $invoiceModel->orders->sortBy(
                fn ($order) => $order->target_delivery_date ?: ($order->departure_time ?: $order->created_at)
            );

            foreach ($orders as $order) {
                $orderDate = $order->target_delivery_date ?: ($order->departure_time ?: $order->created_at);
                $shortDate = $orderDate ? Carbon::parse($orderDate)->format('d-M-y') : date('d-M-y');
                $branch    = $order->delivery_address ?: '';

                foreach ($order->orderItems as $item) {
                    $qty         = (float) $item->ordered_qty;
                    $price       = (float) $item->unit_price;
                    $amount      = (float) ($item->subtotal ?: ($qty * $price));
                    $product     = $item->product;
                    $productName = $product?->name ?: 'Komoditas Sayuran';
                    $grade       = $product?->grade ?: '';

                    $fullName = $productName . ($grade ? " ({$grade})" : '');
                    if ($branch && strlen($branch) < 40) {
                        $fullName .= " ({$branch})";
                    }

                    $items[] = [
                        'date'   => $shortDate,
                        'name'   => $fullName,
                        'qty'    => $qty,
                        'price'  => $price,
                        'amount' => $amount,
                    ];
                    $totalItemsAmount += $amount;
                }
            }
        }

        $shippingCost = 0.0;
        $taxAmount    = 0.0;
        $taxLabel     = null;

        if ($invoiceModel) {
            foreach ($invoiceModel->orders as $order) {
                $shippingCost += (float) ($order->shipping_cost ?? 0);
                $taxAmount    += (float) ($order->tax_amount ?? 0);
                if (!$taxLabel && !empty($order->tax_label)) {
                    $taxLabel = $order->tax_label;
                }
            }
        }

        $subtotal = $totalItemsAmount;

        $creditAdjustment = 0.0;
        if ($invoiceModel) {
            $creditAdjustment = (float) $invoiceModel->orders->flatMap->orderItems->sum(function ($it) {
                return ((float) ($it->returned_weight ?? 0)) * ((float) ($it->unit_price ?? 0));
            });
            if ($creditAdjustment <= 0 && ($subtotal - (float) $invoiceModel->grand_total) > 0 && $shippingCost <= 0) {
                $creditAdjustment = (float) ($subtotal - (float) $invoiceModel->grand_total);
            }
        }

        if ($invoiceModel && $shippingCost <= 0 && $taxAmount <= 0 && ($invoiceModel->grand_total - ($subtotal - $creditAdjustment)) > 0) {
            $shippingCost = (float) ($invoiceModel->grand_total - ($subtotal - $creditAdjustment));
        }

        $grandTotal = $invoiceModel
            ? (float) ($invoiceModel->grand_total ?: max(0, $subtotal + $shippingCost + $taxAmount - $creditAdjustment))
            : 0;

        $status = $invoiceModel ? $invoiceModel->status : 'UNPAID';
        if ($invoiceModel && $status !== 'PAID') {
            $hasVerifiedPayment = $invoiceModel->payments->where('status', 'LUNAS')->isNotEmpty();
            $allOrdersPaid = $invoiceModel->orders->isNotEmpty() && $invoiceModel->orders->every(fn ($o) => in_array($o->status, ['LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']));
            if ($hasVerifiedPayment || ($allOrdersPaid && in_array($invoiceModel->orders->first()?->payment_method, ['CASH', 'TUNAI']))) {
                $status = 'PAID';
            } elseif ($invoiceModel->due_date && Carbon::parse($invoiceModel->due_date)->endOfDay()->isPast()) {
                $status = 'OVERDUE';
            }
        }

        return [
            'company'            => $company,
            'brand'              => $company['full_brand'],
            'is_contract'        => false,
            'is_contract_client' => $isContractClient,
            'client_type_label'  => $clientTypeLabel,
            'client_type'        => $client?->client_type ?: ($isContractClient ? 'B2B_KONTRAK' : 'REGULER'),
            'invoice'            => [
                'id'        => $invoiceModel ? $invoiceModel->invoice_number : ($invoice ?: '-'),
                'po_number' => $poDisplay,
                'issued'    => $formatDateIndo($issuedDate),
                'due'       => $formatDateIndo($dueDate),
                'status'    => $status,
            ],
            'client'             => [
                'to'       => $picName,
                'office'   => $officeName,
                'name'     => $clientName,
                'contract' => $contracts->first()?->contract_number ?: '-',
            ],
            'items'              => $items,
            'regular'            => [
                'title'    => 'Daftar Pasokan Komoditas',
                'items'    => $items,
                'subtotal' => $totalItemsAmount,
            ],
            'subtotal'           => $subtotal,
            'credit_adjustment'  => $creditAdjustment,
            'shipping_cost'      => $shippingCost,
            'tax_amount'         => $taxAmount,
            'tax_label'          => $taxLabel ?: 'Bebas PPN 0% (DTP Pertanian)',
            'grand_total'        => $grandTotal,
        ];
    }
}
