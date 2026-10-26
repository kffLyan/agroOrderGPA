<?php

namespace App\Http\Requests\Direktur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('web')->check() && Auth::guard('web')->user()->role === 'DIREKTUR';
    }

    public function rules(): array
    {
        return [
            'user_id'                    => ['required', 'exists:users,id'],
            'product_id'                 => ['required', 'exists:products,id'],
            'contract_number'            => ['required', 'string', 'max:50', 'unique:contracts,contract_number'],
            'fixed_price_per_kg'         => ['required', 'numeric', 'min:1000'],
            'top_days'                   => ['required', 'integer', 'min:0'],
            'committed_volume_per_cycle' => ['required', 'numeric', 'min:0.1'],
            'status'                     => ['nullable', 'in:ACTIVE,PENDING_APPROVAL'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required'                    => 'Klien kemitraan B2B wajib dipilih.',
            'product_id.required'                 => 'Komoditas sayuran kontrak wajib ditentukan.',
            'contract_number.required'            => 'Nomor kontrak PKS wajib diisi.',
            'contract_number.unique'              => 'Nomor kontrak ini sudah terdaftar.',
            'fixed_price_per_kg.required'         => 'Harga kesepakatan per kg wajib diisi.',
            'top_days.required'                   => 'Termin pembayaran (TOP Days) wajib diisi.',
            'committed_volume_per_cycle.required' => 'Komitmen volume per siklus wajib diisi.',
        ];
    }
}
