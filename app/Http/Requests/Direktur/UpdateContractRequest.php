<?php

namespace App\Http\Requests\Direktur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('web')->check() && Auth::guard('web')->user()->role === 'DIREKTUR';
    }

    public function rules(): array
    {
        return [
            'fixed_price_per_kg'         => ['required', 'numeric', 'min:1000'],
            'top_days'                   => ['required', 'integer', 'min:0'],
            'committed_volume_per_cycle' => ['required', 'numeric', 'min:0.1'],
            'status'                     => ['required', 'in:PENDING_APPROVAL,ACTIVE,EXPIRED,TERMINATED'],
        ];
    }

    public function messages(): array
    {
        return [
            'fixed_price_per_kg.required'         => 'Harga kesepakatan per kg wajib diisi.',
            'fixed_price_per_kg.min'              => 'Harga per kg minimal Rp 1.000.',
            'top_days.required'                   => 'Termin pembayaran (TOP Days) wajib diisi.',
            'top_days.min'                        => 'Termin pembayaran tidak boleh bernilai negatif.',
            'committed_volume_per_cycle.required' => 'Komitmen volume per siklus wajib diisi.',
            'committed_volume_per_cycle.min'      => 'Komitmen volume minimal 0.1 Kg.',
            'status.required'                     => 'Status kontrak wajib ditentukan.',
            'status.in'                           => 'Status kontrak tidak valid.',
        ];
    }
}
