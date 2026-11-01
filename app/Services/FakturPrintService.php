<?php

namespace App\Services;

use App\Models\Invoice;
use Carbon\Carbon;

class FakturPrintService
{
    // Membangun payload data terstruktur untuk lembar cetak faktur tagihan resmi (format kontrak dan reguler)
    public function buildPrintData(string $invoice, ?string $requestedFormat = null): array
    {
        $invoiceModel = Invoice::with(['user.contracts.product', 'orders.orderItems.product'])
            ->where('invoice_number', $invoice)
            ->orWhere('id', $invoice)
            ->first();

        if (!$invoiceModel) {
            $invoiceModel = Invoice::with(['user.contracts.product', 'orders.orderItems.product'])
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

        $formatIndoDayDate = fn ($date) => $date ? Carbon::parse($date)->translatedFormat('l, d F Y') : '-';

        $client             = $invoiceModel?->user;
        $clientName         = $client?->company_name ?: ($client?->name ?: ($invoiceModel ? 'Mitra Klien' : '-'));
        $picName            = $client?->pic_name ?: ($client?->name ?: ($invoiceModel ? 'Pimpinan Purchasing' : '-'));
        $contracts          = $client?->contracts ? $client->contracts->where('status', 'ACTIVE') : collect();
        $contractsByProduct = $contracts->keyBy('product_id');

        $isContractClient = match ($requestedFormat) {
            'kontrak'           => true,
            'reguler', 'standar' => false,
            default             => ($client?->client_type === 'KONTRAK' || $contracts->isNotEmpty()),
        };

        $poNumbers = $invoiceModel ? $invoiceModel->orders->pluck('order_number')->filter()->unique()->values() : collect();
        $poDisplay = $poNumbers->isNotEmpty()
            ? $poNumbers->implode(', ')
            : ($invoiceModel ? ('PO-' . $invoiceModel->invoice_number) : '-');

        $issuedDate = $invoiceModel?->created_at ?: now();
        $dueDate    = $invoiceModel?->due_date ?: $issuedDate->copy()->addDays(14);

        $warehouseItems       = [];
        $contractGroups       = [];
        $regularItems         = [];
        $totalWarehouseAmount = 0.0;
        $totalContractAmount  = 0.0;
        $totalRegularAmount   = 0.0;

        if ($invoiceModel) {
            $orders = $invoiceModel->orders->sortBy(
                fn ($order) => $order->target_delivery_date ?: ($order->departure_time ?: $order->created_at)
            );

            foreach ($orders as $order) {
                $orderDate = $order->target_delivery_date ?: ($order->departure_time ?: $order->created_at);
                $dateShort = $orderDate ? Carbon::parse($orderDate)->format('d-M-y') : date('d-M-y');
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

                    $regularItems[] = [
                        'date'   => $dateShort,
                        'name'   => $fullName,
                        'qty'    => $qty,
                        'price'  => $price,
                        'amount' => $amount,
                    ];
                    $totalRegularAmount += $amount;

                    $contract = $product ? $contractsByProduct->get($product->id) : null;

                    if ($contract && $isContractClient) {
                        $contractPrice = (float) ($contract->fixed_price_per_kg ?: $price);
                        $quota         = (float) $contract->committed_volume_per_cycle;

                        if ($quota > 0 && $qty > $quota) {
                            $contractQty    = $quota;
                            $contractPriceFinal = $contractPrice;
                            $contractAmount = $contractQty * $contractPriceFinal;

                            $regularQty     = $qty - $quota;
                            $regularPrice   = (float) ($product->base_price ?: $price);
                            $regularAmount  = $regularQty * $regularPrice;
                            $rowTotal       = $contractAmount + $regularAmount;
                        } else {
                            $contractQty        = $qty;
                            $contractPriceFinal = $contractPrice;
                            $contractAmount     = $qty * $contractPrice;
                            $regularQty         = 0;
                            $regularPrice       = 0;
                            $regularAmount      = 0;
                            $rowTotal           = $contractAmount;
                        }

                        $contractGroups[$dateShort][] = [
                            'name'     => $fullName,
                            'all_qty'  => $qty,
                            'c_qty'    => $contractQty,
                            'c_price'  => $contractPriceFinal,
                            'c_amount' => $contractAmount,
                            'r_qty'    => $regularQty,
                            'r_price'  => $regularPrice,
                            'r_amount' => $regularAmount,
                            'total'    => $rowTotal,
                        ];

                        $totalContractAmount += $rowTotal;
                    } else {
                        $warehouseItems[] = [
                            'date'   => $dateShort,
                            'name'   => $fullName,
                            'qty'    => $qty,
                            'price'  => $price,
                            'amount' => $amount,
                        ];

                        $totalWarehouseAmount += $amount;
                    }
                }
            }
        }

        $contractPages = [];
        $contractGrandSubtotal = [
            'all_qty'  => 0,
            'c_qty'    => 0,
            'c_amount' => 0,
            'r_qty'    => 0,
            'r_amount' => 0,
            'total'    => 0,
        ];

        foreach ($contractGroups as $cDate => $rows) {
            $subAllQty  = array_sum(array_column($rows, 'all_qty'));
            $subCQty    = array_sum(array_column($rows, 'c_qty'));
            $subCAmount = array_sum(array_column($rows, 'c_amount'));
            $subRQty    = array_sum(array_column($rows, 'r_qty'));
            $subRAmount = array_sum(array_column($rows, 'r_amount'));
            $subTotal   = array_sum(array_column($rows, 'total'));

            $contractPages[] = [
                'date'     => $cDate,
                'rows'     => $rows,
                'subtotal' => [
                    'all_qty'  => $subAllQty,
                    'c_qty'    => $subCQty,
                    'c_amount' => $subCAmount,
                    'r_qty'    => $subRQty,
                    'r_amount' => $subRAmount,
                    'total'    => $subTotal,
                ],
            ];

            $contractGrandSubtotal['all_qty']  += $subAllQty;
            $contractGrandSubtotal['c_qty']    += $subCQty;
            $contractGrandSubtotal['c_amount'] += $subCAmount;
            $contractGrandSubtotal['r_qty']    += $subRQty;
            $contractGrandSubtotal['r_amount'] += $subRAmount;
            $contractGrandSubtotal['total']    += $subTotal;
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

        $subtotalAmount = $isContractClient ? ($totalWarehouseAmount + $totalContractAmount) : $totalRegularAmount;
        if ($invoiceModel && $shippingCost <= 0 && $taxAmount <= 0 && ($invoiceModel->grand_total - $subtotalAmount) > 0) {
            $shippingCost = (float) ($invoiceModel->grand_total - $subtotalAmount);
        }

        $grandTotalFinal = $invoiceModel
            ? (float) ($invoiceModel->grand_total ?: ($subtotalAmount + $shippingCost + $taxAmount))
            : 0;

        return [
            'company'           => $company,
            'brand'             => $company['full_brand'],
            'is_contract'       => $isContractClient,
            'invoice'           => [
                'id'        => $invoiceModel ? $invoiceModel->invoice_number : ($invoice ?: '-'),
                'po_number' => $poDisplay,
                'issued'    => $formatIndoDayDate($issuedDate),
                'due'       => $formatIndoDayDate($dueDate),
                'status'    => $invoiceModel ? $invoiceModel->status : 'DRAFT',
            ],
            'client'            => [
                'to'       => $picName,
                'office'   => $clientName,
                'name'     => $clientName,
                'contract' => $contracts->first()?->contract_number ?: '-',
            ],
            'hasWarehouseItems' => !empty($warehouseItems),
            'warehouse'         => [
                'title'    => 'Sayur Gudang Pasirimpun / Non-Kontrak',
                'items'    => $warehouseItems,
                'subtotal' => $totalWarehouseAmount,
            ],
            'hasContractItems'  => !empty($contractPages),
            'contract'          => [
                'title'          => 'Sayur Kontrak Mitra GPA',
                'pages'          => $contractPages,
                'grand_subtotal' => $contractGrandSubtotal,
            ],
            'regular'           => [
                'title'    => 'Rincian Pasokan Komoditas Reguler',
                'items'    => $regularItems,
                'subtotal' => $totalRegularAmount,
            ],
            'subtotal'          => $subtotalAmount,
            'shipping_cost'     => $shippingCost,
            'tax_amount'        => $taxAmount,
            'tax_label'         => $taxLabel ?: 'Bebas PPN 0% (DTP Pertanian)',
            'grand_total'       => $grandTotalFinal,
        ];
    }
}
