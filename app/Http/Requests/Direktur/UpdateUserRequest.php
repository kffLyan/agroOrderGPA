<?php

namespace App\Http\Requests\Direktur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('web')->check() && Auth::guard('web')->user()->role === 'DIREKTUR';
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'name'         => ['required', 'string', 'max:100'],
            'email'        => ['required', 'string', 'email', 'max:100', Rule::unique('users', 'email')->ignore($userId)],
            'phone'        => ['required', 'string', 'max:20'],
            'role'         => ['required', 'in:DIREKTUR,SEKRETARIS,KOORDINATOR,ARMADA,KLIEN'],
            'is_active'    => ['required', 'boolean'],
            'password'     => ['nullable', 'string', 'min:8'],
            'client_type'  => ['nullable', 'required_if:role,KLIEN', 'in:REGULER,B2B_KONTRAK'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'address'      => ['nullable', 'string'],
            'pic_name'     => ['nullable', 'string', 'max:100'],
            'pic_phone'    => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'           => 'Nama pengguna wajib diisi.',
            'email.required'          => 'Alamat email wajib diisi.',
            'email.unique'            => 'Email sudah digunakan akun lain.',
            'phone.required'          => 'Nomor telepon wajib diisi.',
            'role.required'           => 'Wewenang peran wajib dipilih.',
            'password.min'            => 'Kata sandi baru minimal 8 karakter.',
            'client_type.required_if' => 'Jenis klien wajib dipilih jika peran adalah KLIEN.',
        ];
    }
}
