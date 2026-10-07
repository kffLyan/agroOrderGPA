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
            'target_delivery_date' => ['required', 'date', 'after:today'],
            'delivery_address'     => ['required', 'string', 'max:500'],
            'payment_method'       => ['required', 'in:TRANSFER_BANK,QRIS,COD,TEMPO_TOP'],
            'payment_proof'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // Ukuran maks 5MB
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'exists:products,id'],
            'items.*.quantity'     => ['required', 'numeric', 'min:0.1'],
        ];
    }

    //  Pemeriksaan batas minimum pemesanan komoditas
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            foreach ($items as $index => $item) {
                if (isset($item['product_id'], $item['quantity'])) {
                    $product = Product::find($item['product_id']);
                    if ($product && (float) $item['quantity'] < (float) $product->minimum_order) {
                        $validator->errors()->add(
                            "items.{$index}.quantity",
                            "Kuantitas {$product->name} minimal " . number_format($product->minimum_order, 0) . " {$product->unit} (Rule 08.1)."
                        );
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
