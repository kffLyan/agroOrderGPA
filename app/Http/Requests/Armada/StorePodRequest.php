<?php

namespace App\Http\Requests\Armada;

use Illuminate\Foundation\Http\FormRequest;

class StorePodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && in_array($this->user()->role, ['ARMADA', 'SEKRETARIS', 'DIREKTUR']);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'condition'                 => ['nullable', 'string'],
            'signature'                 => ['nullable', 'string'],
            'received_by_name'          => ['nullable', 'string', 'max:255'],
            'manifest_photo'            => ['nullable', 'image', 'max:10240'],
            'dock_photo'                => ['nullable', 'image', 'max:10240'],
            'returns'                   => ['nullable', 'array'],
            'returns.*.returned_weight' => ['nullable', 'numeric', 'min:0'],
            'returns.*.return_reason'   => ['nullable', 'string', 'max:255'],
        ];
    }
}
