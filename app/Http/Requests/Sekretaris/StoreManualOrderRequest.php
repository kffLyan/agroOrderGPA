<?php

namespace App\Http\Requests\Sekretaris;

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
            'status'               => ['nullable', 'string', 'in:DRAFT,MENUNGGU_VERIFIKASI'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'exists:products,id'],
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
            'items.required'            => 'Minimal 1 komoditas sayur wajib dimasukkan ke pesanan.',
            'items.*.product_id.exists' => 'Komoditas sayur yang dipilih tidak valid.',
            'items.*.quantity.min'      => 'Kuantitas pesanan minimal 0.1 kg.',
            'proof_file.max'            => 'Ukuran berkas bukti transfer maksimal 5 MB.',
            'proof_file.mimes'          => 'Format berkas bukti transfer harus berupa JPG, PNG, atau PDF.',
        ];
    }
}
