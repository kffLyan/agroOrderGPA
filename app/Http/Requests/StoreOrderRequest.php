<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreOrderRequest extends FormRequest
{

    // Otorisasi hanya untuk pengguna dengan sesi login aktif dan wewenang KLIEN
    public function authorize(): bool
    {
        return Auth::guard('web')->check() && Auth::guard('web')->user()->role === 'KLIEN';
    }

    // Aturan validasi data input pesanan
    public function rules(): array
    {
        return [
            'submission_token'     => ['nullable', 'string', 'max:100'],
            'target_delivery_date' => ['required', 'date', 'after:today'],
            'delivery_address'     => ['required', 'string', 'max:500'],
            'payment_method'       => ['required', 'in:TRANSFER_BANK,QRIS,COD,TEMPO_TOP'],
            'payment_proof'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // Ukuran maks 5MB
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'distinct', 'exists:products,id'],
            'items.*.quantity'     => ['required', 'numeric', 'min:0.1'],
        ];
    }

    // Pemeriksaan stok, pencegahan item ganda, dan status Master Freeze
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // 1. Cek Sakelar Darurat Master Freeze dari Direktur Utama
            $gov = app(\App\Services\GovernanceService::class);
            $settings = $gov->getSettings();
            if (!empty($settings['master_freeze_active'])) {
                $reason = $settings['master_freeze_reason'] ?? 'Darurat Operasional';
                $validator->errors()->add('general', "Sistem pemesanan sedang dibekukan sementara oleh Direktur Utama ({$reason}).");
                return;
            }

            $items = $this->input('items', []);
            $stockService = app(\App\Services\StockService::class);

            // 2. Agregasi total kuantitas per komoditas untuk mencegah eksploitasi pecahan kuota
            $aggregatedQuantities = [];
            foreach ($items as $item) {
                if (isset($item['product_id'], $item['quantity'])) {
                    $pid = (int) $item['product_id'];
                    $aggregatedQuantities[$pid] = ($aggregatedQuantities[$pid] ?? 0.0) + (float) $item['quantity'];
                }
            }

            foreach ($aggregatedQuantities as $productId => $totalRequestedQty) {
                $product = Product::find($productId);
                if ($product) {
                    $availableStock = (float) $stockService->getAvailableStock($product->id);
                    if ($totalRequestedQty > $availableStock) {
                        $validator->errors()->add(
                            'items',
                            "Total kuantitas untuk {$product->name} ({$totalRequestedQty} {$product->unit}) melebihi kuota panen yang tersedia ({$availableStock} {$product->unit})."
                        );
                    }
                }
            }

            foreach ($items as $index => $item) {
                if (isset($item['product_id'], $item['quantity'])) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $requestedQty = (float) $item['quantity'];
                        $availableStock = (float) $stockService->getAvailableStock($product->id);

                        if ($availableStock <= 0) {
                            $validator->errors()->add(
                                "items.{$index}.quantity",
                                "Stok {$product->name} saat ini habis / kuota 0 {$product->unit}."
                            );
                        } elseif ($requestedQty > $availableStock) {
                            $validator->errors()->add(
                                "items.{$index}.quantity",
                                "Stok {$product->name} tidak mencukupi (sisa kuota: {$availableStock} {$product->unit}, diminta: {$requestedQty} {$product->unit})."
                            );
                        } elseif ($availableStock < (float) $product->minimum_order) {
                            $validator->errors()->add(
                                "items.{$index}.quantity",
                                "Sisa stok {$product->name} ({$availableStock} {$product->unit}) belum memenuhi batas minimum pemesanan (" . number_format($product->minimum_order, 0) . " {$product->unit})."
                            );
                        } elseif ($requestedQty < (float) $product->minimum_order) {
                            $validator->errors()->add(
                                "items.{$index}.quantity",
                                "Kuantitas {$product->name} minimal " . number_format($product->minimum_order, 0) . " {$product->unit}."
                            );
                        }
                    }
                }
            }
        });
    }

    // Kustomisasi pesan kesalahan validasi
    public function messages(): array
    {
        return [
            'target_delivery_date.required' => 'Tanggal pengiriman wajib ditentukan.',
            'target_delivery_date.after'    => 'Jadwal pengiriman minimal H+1 dari tanggal pemesanan.',
            'delivery_address.required'     => 'Alamat pengiriman / titik bongkar muat wajib diisi.',
            'payment_method.required'       => 'Metode pembayaran wajib dipilih.',
            'payment_proof.max'             => 'Ukuran bukti transfer pembayaran maksimal 5 MB.',
            'items.required'                => 'Pilih minimal satu komoditas sayuran.',
            'items.*.product_id.required'   => 'Komoditas sayuran wajib dipilih.',
            'items.*.product_id.exists'     => 'Komoditas sayuran tidak terdaftar pada katalog.',
            'items.*.quantity.required'     => 'Kuantitas pemesanan wajib diisi.',
            'items.*.quantity.min'          => 'Kuantitas pemesanan harus lebih besar dari 0.',
        ];
    }
}
