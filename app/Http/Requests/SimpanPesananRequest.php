<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Services\TataKelolaService;
use App\Services\StockService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

// Form request validasi checkout pesanan baru klien
class SimpanPesananRequest extends FormRequest
{
    // Otorisasi hanya untuk pengguna login dengan role KLIEN
    public function authorize(): bool
    {
        return Auth::guard('web')->check() && Auth::guard('web')->user()->role === 'KLIEN';
    }

    // Aturan validasi format data input formulir pesanan
    public function rules(): array
    {
        return [
            'submission_token'     => ['nullable', 'string', 'max:100'],
            'target_delivery_date' => ['required', 'date', 'after:today'],
            'delivery_address'     => ['required', 'string', 'max:500'],
            'payment_method'       => ['required', 'in:TRANSFER_BANK,QRIS,COD,TEMPO_TOP'],
            'payment_proof'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'shipping_cost'        => ['nullable', 'numeric', 'min:0'],
            'tax_rate'             => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_amount'           => ['nullable', 'numeric', 'min:0'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'distinct', 'exists:products,id'],
            'items.*.quantity'     => ['required', 'numeric', 'min:0.1'],
        ];
    }

    // Validasi lanjutan: master freeze, plafon kredit klien, dan ketersediaan stok
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $layananTataKelola = app(TataKelolaService::class);

            // 1. Cek sakelar darurat Master Freeze dari Direktur Utama
            if ($layananTataKelola->isMasterFreezeActive()) {
                $alasanBeku = $layananTataKelola->getMasterFreezeReason() ?? 'Darurat Operasional';
                $validator->errors()->add('general', "Sistem pemesanan sedang dibekukan sementara oleh Direktur Utama ({$alasanBeku}).");
                return;
            }

            // 2. Cek pembekuan plafon kredit klien
            if ($this->user() && $layananTataKelola->isClientFrozen((int) $this->user()->id)) {
                $validator->errors()->add('general', 'Plafon kredit Anda sedang dibekukan sementara oleh Direktur Utama. Pengajuan pesanan baru tidak dapat diproses.');
                return;
            }

            $daftarItem = $this->input('items', []);
            $layananStok = app(StockService::class);

            // 3. Agregasi total kuantitas per produk untuk cek stok
            $totalKuantitasPerProduk = [];
            foreach ($daftarItem as $item) {
                if (isset($item['product_id'], $item['quantity'])) {
                    $idProduk = (int) $item['product_id'];
                    $totalKuantitasPerProduk[$idProduk] = ($totalKuantitasPerProduk[$idProduk] ?? 0.0) + (float) $item['quantity'];
                }
            }

            foreach ($totalKuantitasPerProduk as $idProduk => $totalKuantitasDiminta) {
                $produk = Product::find($idProduk);
                if ($produk) {
                    $stokTersedia = (float) $layananStok->getAvailableStock($produk->id);
                    if ($totalKuantitasDiminta > $stokTersedia) {
                        $validator->errors()->add(
                            'items',
                            "Total kuantitas untuk {$produk->name} ({$totalKuantitasDiminta} {$produk->unit}) melebihi kuota panen yang tersedia ({$stokTersedia} {$produk->unit})."
                        );
                    }
                }
            }

            foreach ($daftarItem as $indeks => $item) {
                if (isset($item['product_id'], $item['quantity'])) {
                    $produk = Product::find($item['product_id']);
                    if ($produk) {
                        $kuantitasDiminta = (float) $item['quantity'];
                        $stokTersedia = (float) $layananStok->getAvailableStock($produk->id);

                        if ($stokTersedia <= 0) {
                            $validator->errors()->add(
                                "items.{$indeks}.quantity",
                                "Stok {$produk->name} saat ini habis / kuota 0 {$produk->unit}."
                            );
                        } elseif ($kuantitasDiminta > $stokTersedia) {
                            $validator->errors()->add(
                                "items.{$indeks}.quantity",
                                "Stok {$produk->name} tidak mencukupi (sisa kuota: {$stokTersedia} {$produk->unit}, diminta: {$kuantitasDiminta} {$produk->unit})."
                            );
                        } elseif ($stokTersedia < (float) $produk->minimum_order) {
                            $validator->errors()->add(
                                "items.{$indeks}.quantity",
                                "Sisa stok {$produk->name} ({$stokTersedia} {$produk->unit}) belum memenuhi batas minimum pemesanan (" . number_format($produk->minimum_order, 0) . " {$produk->unit})."
                            );
                        } elseif ($kuantitasDiminta < (float) $produk->minimum_order) {
                            $validator->errors()->add(
                                "items.{$indeks}.quantity",
                                "Kuantitas {$produk->name} minimal " . number_format($produk->minimum_order, 0) . " {$produk->unit}."
                            );
                        }
                    }
                }
            }
        });
    }

    // Pesan validasi dalam bahasa Indonesia
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
