<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Form request validasi pembuatan produk/komoditas baru
class SimpanProdukRequest extends FormRequest
{
    // Otorisasi peran koordinator dan direktur
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['KOORDINATOR', 'DIREKTUR'], true);
    }

    // Aturan validasi input data produk
    public function rules(): array
    {
        return [
            'sku'           => ['required', 'string', 'max:30', 'unique:products,sku'],
            'name'          => ['required', 'string', 'max:100'],
            'unit'          => ['required', 'string', 'max:20'],
            'grade'         => ['required', 'string', 'max:20'],
            'base_price'    => ['required', 'numeric', 'min:0'],
            'minimum_order' => ['required', 'numeric', 'min:0.1'],
            'image_file'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'preset_image'  => ['nullable', 'string', 'max:255'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'is_active'     => ['required'],
        ];
    }
}
