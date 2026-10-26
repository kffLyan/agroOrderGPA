<?php

namespace App\Http\Requests\Sekretaris;

use Illuminate\Foundation\Http\FormRequest;

class RejectPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'SEKRETARIS';
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rejection_note' => ['nullable', 'string', 'max:255'],
            'note'           => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rejection_note.required' => 'Catatan alasan penolakan bukti pembayaran wajib diisi.',
            'rejection_note.max'      => 'Catatan alasan penolakan maksimal 255 karakter.',
        ];
    }
}
