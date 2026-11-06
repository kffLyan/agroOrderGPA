<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PrintFakturController extends Controller
{
    // Menyiapkan cetak faktur penagihan resmi (invoice) format kontrak maupun reguler dengan validasi kepemilikan
    public function __invoke(Request $request, string $invoice): View
    {
        $invoiceModel = Invoice::where('invoice_number', $invoice)
            ->orWhere('id', $invoice)
            ->first();

        // Memvalidasi kepemilikan faktur (Anti-IDOR) agar klien hanya dapat mencetak fakturnya sendiri
        if (auth()->user()?->role === 'KLIEN' && (!$invoiceModel || $invoiceModel->user_id !== auth()->id())) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat faktur pelanggan lain.');
        }

        $format = $request->query('format');
        $data   = $this->buildPrintData($invoice, $format);

        return view('prints.invoice', $data);
    }

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

        $formatDateIndo = fn ($date) => $date ? Carbon::parse($date)->translatedFormat('l, d F Y') : '-';

        $client             = $invoiceModel?->user;
        $clientName         = $client?->company_name ?: ($client?->name ?: ($invoiceModel ? 'Mitra Klien' : '-'));
        $picName            = $client?->pic_name ?: ($client?->name ?: ($invoiceModel ? 'Pimpinan Purchasing' : '-'));
        $contracts          = $client?->contracts ? $client->contracts->where('status', 'ACTIVE') : collect();
        $contractsByProduct = $contracts->keyBy('product_id');

        $isContract = match ($requestedFormat) {
            'kontrak'            => true,
            'reguler', 'standar' => false,
            default              => ($client?->client_type === 'KONTRAK' || $contracts->isNotEmpty()),
        };

        $poNumbers = $invoiceModel ? $invoiceModel->orders->pluck('order_number')->filter()->unique()->values() : collect();
        $poDisplay = $poNumbers->isNotEmpty()
            ? $poNumbers->implode(', ')
            : ($invoiceModel ? ('PO-' . $invoiceModel->invoice_number) : '-');

        $issuedDate = $invoiceModel?->created_at ?: now();
        $dueDate    = $invoiceModel?->due_date ?: $issuedDate->copy()->addDays(14);

        $warehouseItems = [];
        $contractGroups = [];
        $regularItems   = [];
        $totalWarehouse = 0.0;
        $totalContract  = 0.0;
        $totalRegular   = 0.0;

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

                    $regularItems[] = [
                        'date'   => $shortDate,
                        'name'   => $fullName,
                        'qty'    => $qty,
                        'price'  => $price,
                        'amount' => $amount,
                    ];
                    $totalRegular += $amount;

                    $contract = $product ? $contractsByProduct->get($product->id) : null;

                    if ($contract && $isContract) {
                        $contractPrice = (float) ($contract->fixed_price_per_kg ?: $price);
                        $contractQuota = (float) $contract->committed_volume_per_cycle;

                        if ($contractQuota > 0 && $qty > $contractQuota) {
                            $contractQty    = $contractQuota;
                            $contractPrice  = $contractPrice;
                            $contractAmount = $contractQty * $contractPrice;

                            $regularQty     = $qty - $contractQuota;
                            $regularPrice   = (float) ($product->base_price ?: $price);
                            $regularAmount  = $regularQty * $regularPrice;
                            $lineTotal      = $contractAmount + $regularAmount;
                        } else {
                            $contractQty    = $qty;
                            $contractPrice  = $contractPrice;
                            $contractAmount = $qty * $contractPrice;
                            $regularQty     = 0;
                            $regularPrice   = 0;
                            $regularAmount  = 0;
                            $lineTotal      = $contractAmount;
                        }

                        $contractGroups[$shortDate][] = [
                            'name'     => $fullName,
                            'all_qty'  => $qty,
                            'c_qty'    => $contractQty,
                            'c_price'  => $contractPrice,
                            'c_amount' => $contractAmount,
                            'r_qty'    => $regularQty,
                            'r_price'  => $regularPrice,
                            'r_amount' => $regularAmount,
                            'total'    => $lineTotal,
                        ];

                        $totalContract += $lineTotal;
                    } else {
                        $warehouseItems[] = [
                            'date'   => $shortDate,
                            'name'   => $fullName,
                            'qty'    => $qty,
                            'price'  => $price,
                            'amount' => $amount,
                        ];

                        $totalWarehouse += $amount;
                    }
                }
            }
        }

        $contractPages = [];
        $contractSummary = [
            'all_qty'  => 0,
            'c_qty'    => 0,
            'c_amount' => 0,
            'r_qty'    => 0,
            'r_amount' => 0,
            'total'    => 0,
        ];

        foreach ($contractGroups as $groupDate => $rows) {
            $subTotalQty     = array_sum(array_column($rows, 'all_qty'));
            $subTotalContractQty = array_sum(array_column($rows, 'c_qty'));
            $subTotalContractNom = array_sum(array_column($rows, 'c_amount'));
            $subTotalRegularQty  = array_sum(array_column($rows, 'r_qty'));
            $subTotalRegularNom  = array_sum(array_column($rows, 'r_amount'));
            $subTotalLine        = array_sum(array_column($rows, 'total'));

            $contractPages[] = [
                'date'     => $groupDate,
                'rows'     => $rows,
                'subtotal' => [
                    'all_qty'  => $subTotalQty,
                    'c_qty'    => $subTotalContractQty,
                    'c_amount' => $subTotalContractNom,
                    'r_qty'    => $subTotalRegularQty,
                    'r_amount' => $subTotalRegularNom,
                    'total'    => $subTotalLine,
                ],
            ];

            $contractSummary['all_qty']  += $subTotalQty;
            $contractSummary['c_qty']    += $subTotalContractQty;
            $contractSummary['c_amount'] += $subTotalContractNom;
            $contractSummary['r_qty']    += $subTotalRegularQty;
            $contractSummary['r_amount'] += $subTotalRegularNom;
            $contractSummary['total']    += $subTotalLine;
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

        $subtotal = $isContract ? ($totalWarehouse + $totalContract) : $totalRegular;
        if ($invoiceModel && $shippingCost <= 0 && $taxAmount <= 0 && ($invoiceModel->grand_total - $subtotal) > 0) {
            $shippingCost = (float) ($invoiceModel->grand_total - $subtotal);
        }

        $grandTotal = $invoiceModel
            ? (float) ($invoiceModel->grand_total ?: ($subtotal + $shippingCost + $taxAmount))
            : 0;

        return [
            'company'           => $company,
            'brand'             => $company['full_brand'],
            'is_contract'       => $isContract,
            'invoice'           => [
                'id'        => $invoiceModel ? $invoiceModel->invoice_number : ($invoice ?: '-'),
                'po_number' => $poDisplay,
                'issued'    => $formatDateIndo($issuedDate),
                'due'       => $formatDateIndo($dueDate),
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
                'subtotal' => $totalWarehouse,
            ],
            'hasContractItems'  => !empty($contractPages),
            'contract'          => [
                'title'          => 'Sayur Kontrak Mitra GPA',
                'pages'          => $contractPages,
                'grand_subtotal' => $contractSummary,
            ],
            'regular'           => [
                'title'    => 'Rincian Pasokan Komoditas Reguler',
                'items'    => $regularItems,
                'subtotal' => $totalRegular,
            ],
            'subtotal'          => $subtotal,
            'shipping_cost'     => $shippingCost,
            'tax_amount'        => $taxAmount,
            'tax_label'         => $taxLabel ?: 'Bebas PPN 0% (DTP Pertanian)',
            'grand_total'       => $grandTotal,
        ];
    }
}
