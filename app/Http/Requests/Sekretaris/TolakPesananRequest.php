<?php

namespace App\Http\Requests\Sekretaris;

use Illuminate\Foundation\Http\FormRequest;

// Form request validasi penolakan pesanan oleh sekretaris
class TolakPesananRequest extends FormRequest
{
    // Otorisasi hanya untuk sekretaris
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'SEKRETARIS';
    }

    // Aturan validasi alasan penolakan pesanan
    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:255'],
        ];
    }

    // Pesan validasi bahasa Indonesia
    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Alasan penolakan pesanan wajib diisi.',
            'rejection_reason.max'      => 'Alasan penolakan maksimal 255 karakter.',
        ];
    }
}
