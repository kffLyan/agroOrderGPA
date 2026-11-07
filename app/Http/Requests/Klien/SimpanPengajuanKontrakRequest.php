<?php

namespace App\Http\Requests\Klien;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

// Form Request validasi pengajuan proposal kontrak kemitraan B2B oleh klien
class SimpanPengajuanKontrakRequest extends FormRequest
{
    // Otorisasi hanya untuk pengguna login berstatus KLIEN
    public function authorize(): bool
    {
        return Auth::guard('web')->check() && Auth::guard('web')->user()->role === 'KLIEN';
    }

    // Aturan validasi pengajuan kontrak suplai rutin komoditas
    public function rules(): array
    {
        return [
            'product_id'                 => ['required', 'exists:products,id'],
            'committed_volume_per_cycle' => ['required', 'numeric', 'min:50', 'max:50000'],
            'proposed_price_per_kg'      => ['nullable', 'numeric', 'min:100', 'max:10000000'],
            'top_days'                   => ['required', 'integer', 'in:14,30,45'],
            'company_name'               => ['nullable', 'string', 'max:150'],
            'address'                    => ['nullable', 'string', 'max:255'],
            'pic_name'                   => ['nullable', 'string', 'max:100'],
            'pic_phone'                  => ['nullable', 'string', 'max:25'],
        ];
    }

    // Pesan error ramah pengguna dalam bahasa Indonesia
    public function messages(): array
    {
        return [
            'product_id.required'                 => 'Pilih komoditas utama yang ingin dikontrakkan.',
            'product_id.exists'                   => 'Komoditas yang dipilih tidak valid.',
            'committed_volume_per_cycle.required' => 'Komitmen volume per siklus wajib diisi.',
            'committed_volume_per_cycle.min'      => 'Minimal komitmen volume adalah 50 kg per siklus pengiriman.',
            'top_days.required'                   => 'Pilih termin pembayaran (TOP) yang diajukan.',
            'top_days.in'                         => 'Pilihan TOP harus 14, 30, atau 45 hari.',
        ];
    }
}

