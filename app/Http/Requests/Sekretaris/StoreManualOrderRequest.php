<?php

namespace App\Http\Requests\Sekretaris;

use App\Models\HarvestBatch;
use App\Models\Product;
use App\Models\User;
use App\Services\GovernanceService;
use App\Services\StockService;
use Illuminate\Foundation\Http\FormRequest;

class StoreManualOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'SEKRETARIS';
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->items)) {
            $decoded = json_decode($this->items, true);
            if (is_array($decoded)) {
                $this->merge(['items' => $decoded]);
            }
        }
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id'            => ['nullable', 'exists:users,id'],
            'client_name'          => ['nullable', 'string', 'max:255'],
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

    /**
     * @return array<string, string>
     */
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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $gov = app(GovernanceService::class);

            // 1. Cek Sakelar Darurat Master Freeze dari Direktur Utama
            if ($gov->isMasterFreezeActive()) {
                $reason = $gov->getMasterFreezeReason() ?? 'Darurat Operasional';
                $validator->errors()->add('general', "Sistem pemesanan sedang dibekukan sementara oleh Direktur Utama ({$reason}).");
                return;
            }

            // 2. Cek Pembekuan Plafon Kredit Klien
            $clientId = (int) ($this->input('client_id') ?: (User::where('role', 'KLIEN')->first()?->id ?? 0));
            if ($clientId > 0 && $gov->isClientFrozen($clientId)) {
                $validator->errors()->add('client_id', 'Plafon kredit klien ini sedang dibekukan oleh Direktur Utama. Tidak dapat membuat pesanan baru.');
                return;
            }

            // 3. Validasi kuota stok panen komoditas
            $items = $this->input('items', []);
            if (!is_array($items)) {
                return;
            }

            $stockService = app(StockService::class);
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
                    $hasBatches = HarvestBatch::where('product_id', $productId)->exists();
                    $availableStock = (float) $stockService->getAvailableStock($product->id);
                    if ($hasBatches && $totalRequestedQty > $availableStock) {
                        $validator->errors()->add(
                            'items',
                            "Total kuantitas untuk {$product->name} ({$totalRequestedQty} {$product->unit}) melebihi kuota panen yang tersedia ({$availableStock} {$product->unit})."
                        );
                    }
                }
            }
        });
    }
}
