<?php

namespace App\Http\Requests\Sekretaris;

use Illuminate\Foundation\Http\FormRequest;

class AssignDriverRequest extends FormRequest
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
            'driver_id'            => ['nullable', 'exists:users,id'],
            'vehicle_plate_number' => ['nullable', 'string', 'max:20'],
        ];
    }
}
