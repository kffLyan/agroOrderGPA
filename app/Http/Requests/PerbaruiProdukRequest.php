<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Form request validasi pembaruan data produk/komoditas
class PerbaruiProdukRequest extends FormRequest
{
    // Otorisasi peran koordinator dan direktur
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['KOORDINATOR', 'DIREKTUR'], true);
    }

    // Aturan validasi pembaruan data produk
    public function rules(): array
    {
        $idProduk = $this->route('product')?->id ?? $this->route('product');

        return [
            'sku'           => ['required', 'string', 'max:30', Rule::unique('products', 'sku')->ignore($idProduk)],
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
