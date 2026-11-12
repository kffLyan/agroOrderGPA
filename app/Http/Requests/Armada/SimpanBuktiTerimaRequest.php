<?php

namespace App\Http\Requests\Armada;

use Illuminate\Foundation\Http\FormRequest;

// Form request validasi simpan bukti terima (PoD) oleh armada
class SimpanBuktiTerimaRequest extends FormRequest
{
    // Otorisasi peran armada, sekretaris, dan direktur
    public function authorize(): bool
    {
        return $this->user() !== null && in_array($this->user()->role, ['ARMADA', 'SEKRETARIS', 'DIREKTUR'], true);
    }

    // Aturan validasi bukti terima pengiriman
    public function rules(): array
    {
        return [
            'condition'                 => ['nullable', 'string'],
            'signature'                 => ['nullable', 'string'],
            'received_by_name'          => ['nullable', 'string', 'max:255'],
            'manifest_photo'            => ['nullable', 'image', 'max:10240'],
            'dock_photo'                => ['nullable', 'image', 'max:10240'],
            'pod_photo'                 => ['nullable', 'image', 'max:10240'],
            'returns'                   => ['nullable', 'array'],
            'returns.*.returned_weight' => ['nullable', 'numeric', 'min:0'],
            'returns.*.return_reason'   => ['nullable', 'string', 'max:255'],
        ];
    }
}
