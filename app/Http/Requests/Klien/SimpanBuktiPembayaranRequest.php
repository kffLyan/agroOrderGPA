<?php

namespace App\Http\Requests\Klien;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

// Form Request validasi unggah berkas bukti pembayaran klien
class SimpanBuktiPembayaranRequest extends FormRequest
{
    // Otorisasi hanya untuk pengguna terotentikasi dengan peran KLIEN
    public function authorize(): bool
    {
        return Auth::guard('web')->check() && Auth::guard('web')->user()->role === 'KLIEN';
    }

    // Aturan validasi format data input formulir bukti pembayaran
    public function rules(): array
    {
        return [
            'invoice_po'     => ['required', 'string'],
            'amount'         => ['required', 'numeric', 'min:1'],
            'sender_bank'    => ['required', 'string', 'max:100'],
            'sender_account' => ['required', 'string', 'max:50'],
            'reference'      => ['nullable', 'string', 'max:100'],
            'transfer_at'    => ['nullable', 'string', 'max:50'],
            'note'           => ['nullable', 'string', 'max:500'],
            'proof_file'     => ['nullable', 'file', 'mimes:jpeg,jpg,png,pdf', 'max:5120'],
        ];
    }
}

