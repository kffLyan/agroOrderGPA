<?php

namespace App\Http\Requests\Sekretaris;

use Illuminate\Foundation\Http\FormRequest;

// Form request validasi penugasan driver dan armada pada surat jalan
class TugaskanDriverRequest extends FormRequest
{
    // Otorisasi hanya untuk sekretaris
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->role === 'SEKRETARIS';
    }

    // Aturan validasi penugasan driver
    public function rules(): array
    {
        return [
            'driver_id'            => ['nullable', 'exists:users,id'],
            'vehicle_plate_number' => ['nullable', 'string', 'max:20'],
        ];
    }
}
