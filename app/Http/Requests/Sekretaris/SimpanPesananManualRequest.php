<?php

namespace App\Http\Requests\Sekretaris;

use App\Models\HarvestBatch;
use App\Models\Product;
use App\Models\User;
use App\Services\TataKelolaService;
use App\Services\StockService;
use Illuminate\Foundation\Http\FormRequest;

// Form request validasi pembuatan pesanan manual oleh sekretaris
class SimpanPesananManualRequest extends FormRequest
{
    // Otorisasi hanya untuk sekretaris
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'SEKRETARIS';
    }

    // Normalisasi format payload items jika dikirim dalam bentuk JSON string
    protected function prepareForValidation(): void
    {
        if (is_string($this->items)) {
            $daftarTerurai = json_decode($this->items, true);
            if (is_array($daftarTerurai)) {
                $this->merge(['items' => $daftarTerurai]);
            }
        }

        if ($this->has('client_id')) {
            $clientId = $this->input('client_id');
            if ($clientId === '' || $clientId === 'null' || $clientId === null) {
                $this->merge(['client_id' => null]);
            }
        }
    }

    // Aturan validasi input pesanan manual
    public function rules(): array
    {
        return [
            'client_id'            => ['nullable', 'exists:users,id'],
            'client_name'          => ['nullable', 'string', 'max:255'],
            'client_contact'       => ['nullable', 'string', 'max:255'],
            'client_phone'         => ['nullable', 'string', 'max:50'],
            'delivery_address'     => ['nullable', 'string', 'max:500'],
            'target_delivery_date' => ['nullable', 'date'],
            'order_source'         => ['nullable', 'string', 'max:50'],
            'source_reference'     => ['nullable', 'string', 'max:100'],
            'payment_method'       => ['nullable', 'string', 'max:50'],
            'payment_channel_id'   => ['nullable', 'string', 'max:50'],
            'sender_bank'          => ['nullable', 'string', 'max:100'],
            'sender_account'       => ['nullable', 'string', 'max:100'],
            'payment_reference'    => ['nullable', 'string', 'max:100'],
            'payment_custom_note'  => ['nullable', 'string', 'max:255'],
            'cash_amount'          => ['nullable', 'numeric', 'min:0'],
            'cash_change'          => ['nullable', 'numeric', 'min:0'],
            'proof_file'           => ['nullable', 'file', 'mimes:jpeg,jpg,png,pdf', 'max:5120'],
            'payment_proof'        => ['nullable', 'file', 'mimes:jpeg,jpg,png,pdf', 'max:5120'],
            'proof_url'            => ['nullable', 'string', 'max:255'],
            'fleet_type'           => ['nullable', 'string', 'max:100'],
            'fee'                  => ['nullable', 'numeric', 'min:0'],
            'shipping_cost'        => ['nullable', 'numeric', 'min:0'],
            'status'               => ['nullable', 'string', 'in:DRAFT,MENUNGGU_VERIFIKASI,LUNAS,SIAP_KIRIM,VERIFIKASI'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'distinct', 'exists:products,id'],
            'items.*.quantity'     => ['required', 'numeric', 'min:0.1'],
            'items.*.price'        => ['nullable', 'numeric', 'min:0'],
        ];
    }

    // Pesan validasi bahasa Indonesia
    public function messages(): array
    {
        return [
            'items.required'              => 'Minimal 1 komoditas sayur wajib dimasukkan ke pesanan.',
            'items.*.product_id.distinct' => 'Komoditas sayur yang sama tidak boleh diinput ganda dalam satu pesanan.',
            'items.*.product_id.exists'   => 'Komoditas sayur yang dipilih tidak valid.',
            'items.*.quantity.min'        => 'Kuantitas pesanan minimal 0.1 kg.',
            'proof_file.max'              => 'Ukuran berkas bukti transfer maksimal 5 MB.',
            'proof_file.mimes'            => 'Format berkas bukti transfer harus berupa JPG, PNG, atau PDF.',
        ];
    }

    // Validasi lanjutan: Master freeze, plafon kredit klien, dan ketersediaan stok
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
            $idKlien = (int) ($this->input('client_id') ?: 0);
            if ($idKlien > 0 && $layananTataKelola->isClientFrozen($idKlien)) {
                $validator->errors()->add('client_id', 'Plafon kredit klien ini sedang dibekukan oleh Direktur Utama. Tidak dapat membuat pesanan baru.');
                return;
            }

            // 3. Validasi kuota stok panen komoditas di gudang
            $daftarItem = $this->input('items', []);
            if (!is_array($daftarItem)) {
                return;
            }

            $layananStok = app(StockService::class);
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
                    $adaBatchPanen = HarvestBatch::where('product_id', $idProduk)->exists();
                    $stokTersedia = (float) $layananStok->getAvailableStock($produk->id);
                    if ($adaBatchPanen && $totalKuantitasDiminta > $stokTersedia) {
                        $validator->errors()->add(
                            'items',
                            "Total kuantitas untuk {$produk->name} ({$totalKuantitasDiminta} {$produk->unit}) melebihi kuota panen yang tersedia ({$stokTersedia} {$produk->unit})."
                        );
                    }
                }
            }
        });
    }
}
