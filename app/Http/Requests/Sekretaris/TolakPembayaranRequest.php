<?php

namespace App\Http\Requests\Sekretaris;

use Illuminate\Foundation\Http\FormRequest;

// Form request validasi penolakan bukti pembayaran oleh sekretaris
class TolakPembayaranRequest extends FormRequest
{
    // Otorisasi hanya untuk sekretaris
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'SEKRETARIS';
    }

    // Aturan validasi alasan penolakan pembayaran
    public function rules(): array
    {
        return [
            'rejection_note' => ['nullable', 'string', 'max:255'],
            'note'           => ['nullable', 'string', 'max:255'],
        ];
    }

    // Pesan validasi bahasa Indonesia
    public function messages(): array
    {
        return [
            'rejection_note.required' => 'Catatan alasan penolakan bukti pembayaran wajib diisi.',
            'rejection_note.max'      => 'Catatan alasan penolakan maksimal 255 karakter.',
        ];
    }
}
