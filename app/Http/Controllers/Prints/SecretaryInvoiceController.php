<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SecretaryInvoiceController extends Controller
{
    public function __invoke(Request $request, string $invoice): View
    {
        $requestedFormat = $request->query('format'); // 'kontrak' | 'reguler' | 'standar' | null

        // Cari invoice berdasarkan nomor invoice_number atau ID primary key
        $invModel = Invoice::with(['user.contracts.product', 'orders.orderItems.product'])
            ->where('invoice_number', $invoice)
            ->orWhere('id', $invoice)
            ->first();

        // Fallback ke invoice terbaru yang ada di database jika nomor spesifik tidak ditemukan
        if (!$invModel) {
            $invModel = Invoice::with(['user.contracts.product', 'orders.orderItems.product'])
                ->latest()
                ->first();
        }

        // Data Resmi Legalitas Koperasi Produsen Green Pasundan Agriculture
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

        // Helper format tanggal bahasa Indonesia
        $formatIndoDayDate = function ($date) {
            if (!$date) return '-';
            return Carbon::parse($date)->translatedFormat('l, d F Y');
        };

        if ($invModel) {
            $client = $invModel->user;
            $clientName = $client?->company_name ?: ($client?->name ?: 'Mitra Klien');
            $picName = $client?->pic_name ?: ($client?->name ?: 'Pimpinan Purchasing');
            $contracts = $client?->contracts ? $client->contracts->where('status', 'ACTIVE') : collect();
            $contractsByProduct = $contracts->keyBy('product_id');

            // Penentuan format: Kontrak (B2B Split Kuota) vs Reguler (Standar Tunggal)
            $isContractClient = false;
            if ($requestedFormat === 'kontrak') {
                $isContractClient = true;
            } elseif ($requestedFormat === 'reguler' || $requestedFormat === 'standar') {
                $isContractClient = false;
            } else {
                $isContractClient = ($client?->client_type === 'KONTRAK' || $contracts->isNotEmpty());
            }

            // Nomor PO dari pesanan-pesanan riil yang terikat pada invoice
            $poNumbers = $invModel->orders->pluck('order_number')->filter()->unique()->values();
            $poDisplay = $poNumbers->isNotEmpty()
                ? $poNumbers->implode(', ')
                : ('PO-' . $invModel->invoice_number);

            $issuedDate = $invModel->created_at ?: now();
            $dueDate = $invModel->due_date ?: $issuedDate->copy()->addDays(14);

            $warehouseItems = []; // Tabel 1: Sayur Gudang / Pasokan Non-Kontrak
            $contractGroups = []; // Tabel 2: Sayur Kontrak (Alokasi Kuota)
            $regularItems = [];   // Untuk Invoice Klien Reguler murni

            $totalWarehouseAmount = 0;
            $totalContractAmount = 0;
            $totalRegularAmount = 0;

            // Sort pesanan berdasarkan tanggal pengantaran atau pembuatan
            $orders = $invModel->orders->sortBy(function ($ord) {
                return $ord->target_delivery_date ?: ($ord->departure_time ?: $ord->created_at);
            });

            foreach ($orders as $ord) {
                $orderDate = $ord->target_delivery_date ?: ($ord->departure_time ?: $ord->created_at);
                $dateShort = $orderDate ? Carbon::parse($orderDate)->format('d-M-y') : date('d-M-y');
                $branch = $ord->delivery_address ?: '';

                foreach ($ord->orderItems as $item) {
                    $qty = (float) $item->ordered_qty;
                    $price = (float) $item->unit_price;
                    $amount = (float) ($item->subtotal ?: ($qty * $price));
                    $product = $item->product;
                    $productName = $product?->name ?: 'Komoditas Sayuran';
                    $grade = $product?->grade ?: '';

                    $fullName = $productName . ($grade ? " ({$grade})" : '');
                    if ($branch && strlen($branch) < 40) {
                        $fullName .= " ({$branch})";
                    }

                    // Untuk format reguler
                    $regularItems[] = [
                        'date'   => $dateShort,
                        'name'   => $fullName,
                        'qty'    => $qty,
                        'price'  => $price,
                        'amount' => $amount,
                    ];
                    $totalRegularAmount += $amount;

                    // Cek apakah produk memiliki skema kontrak aktif untuk klien ini
                    $contract = $product ? $contractsByProduct->get($product->id) : null;

                    if ($contract && $isContractClient) {
                        $contractPrice = (float) ($contract->fixed_price_per_kg ?: $price);
                        $quota = (float) $contract->committed_volume_per_cycle;

                        if ($quota > 0 && $qty > $quota) {
                            $cQty = $quota;
                            $cPrice = $contractPrice;
                            $cAmount = $cQty * $cPrice;

                            $rQty = $qty - $quota;
                            $rPrice = (float) ($product->base_price ?: $price);
                            $rAmount = $rQty * $rPrice;
                            $rowTotal = $cAmount + $rAmount;
                        } else {
                            $cQty = $qty;
                            $cPrice = $contractPrice;
                            $cAmount = $qty * $contractPrice;

                            $rQty = 0;
                            $rPrice = 0;
                            $rAmount = 0;
                            $rowTotal = $cAmount;
                        }

                        $contractGroups[$dateShort][] = [
                            'name'     => $fullName,
                            'all_qty'  => $qty,
                            'c_qty'    => $cQty,
                            'c_price'  => $cPrice,
                            'c_amount' => $cAmount,
                            'r_qty'    => $rQty,
                            'r_price'  => $rPrice,
                            'r_amount' => $rAmount,
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

            // Olah grup data kontrak per tanggal jika format kontrak
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
                $subAllQty = array_sum(array_column($rows, 'all_qty'));
                $subCQty = array_sum(array_column($rows, 'c_qty'));
                $subCAmount = array_sum(array_column($rows, 'c_amount'));
                $subRQty = array_sum(array_column($rows, 'r_qty'));
                $subRAmount = array_sum(array_column($rows, 'r_amount'));
                $subTotal = array_sum(array_column($rows, 'total'));

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

                $contractGrandSubtotal['all_qty'] += $subAllQty;
                $contractGrandSubtotal['c_qty'] += $subCQty;
                $contractGrandSubtotal['c_amount'] += $subCAmount;
                $contractGrandSubtotal['r_qty'] += $subRQty;
                $contractGrandSubtotal['r_amount'] += $subRAmount;
                $contractGrandSubtotal['total'] += $subTotal;
            }

            $hasContractItems = !empty($contractPages);
            $hasWarehouseItems = !empty($warehouseItems);
            $grandTotalFinal = (float) ($invModel->grand_total ?: ($isContractClient ? ($totalWarehouseAmount + $totalContractAmount) : $totalRegularAmount));

            return view('prints.invoice', [
                'company'           => $company,
                'brand'             => $company['full_brand'],
                'is_contract'       => $isContractClient,
                'invoice'           => [
                    'id'          => $invModel->invoice_number,
                    'po_number'   => $poDisplay,
                    'issued'      => $formatIndoDayDate($issuedDate),
                    'due'         => $formatIndoDayDate($dueDate),
                    'status'      => $invModel->status,
                ],
                'client'            => [
                    'to'       => $picName,
                    'office'   => $clientName,
                    'name'     => $clientName,
                    'contract' => $contracts->first()?->contract_number ?: '-',
                ],
                'hasWarehouseItems' => $hasWarehouseItems,
                'warehouse'         => [
                    'title'    => 'Sayur Gudang Pasirimpun / Non-Kontrak',
                    'items'    => $warehouseItems,
                    'subtotal' => $totalWarehouseAmount,
                ],
                'hasContractItems'  => $hasContractItems,
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
                'grand_total'       => $grandTotalFinal,
            ]);
        }

        // Tampilan aman jika belum ada record invoice sama sekali di database
        $issuedNow = now();
        $dueNow = now()->addDays(14);
        return view('prints.invoice', [
            'company'           => $company,
            'brand'             => $company['full_brand'],
            'is_contract'       => ($requestedFormat === 'kontrak'),
            'invoice'           => [
                'id'          => $invoice ?: '-',
                'po_number'   => '-',
                'issued'      => $formatIndoDayDate($issuedNow),
                'due'         => $formatIndoDayDate($dueNow),
                'status'      => 'DRAFT',
            ],
            'client'            => [
                'to'       => '-',
                'office'   => '-',
                'name'     => '-',
                'contract' => '-',
            ],
            'hasWarehouseItems' => false,
            'warehouse'         => [
                'title'    => 'Sayur Gudang Pasirimpun / Non-Kontrak',
                'items'    => [],
                'subtotal' => 0,
            ],
            'hasContractItems'  => false,
            'contract'          => [
                'title'          => 'Sayur Kontrak Mitra GPA',
                'pages'          => [],
                'grand_subtotal' => ['all_qty' => 0, 'c_qty' => 0, 'c_amount' => 0, 'r_qty' => 0, 'r_amount' => 0, 'total' => 0],
            ],
            'regular'           => [
                'title'    => 'Rincian Pasokan Komoditas Reguler',
                'items'    => [],
                'subtotal' => 0,
            ],
            'grand_total'       => 0,
        ]);
    }
}
