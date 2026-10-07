<?php

namespace App\Http\Requests\Direktur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('web')->check() && Auth::guard('web')->user()->role === 'DIREKTUR';
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:100'],
            'email'        => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'phone'        => ['required', 'string', 'max:20'],
            'password'     => ['required', 'string', 'min:8'],
            'role'         => ['required', 'in:DIREKTUR,SEKRETARIS,KOORDINATOR,ARMADA,KLIEN'],
            'client_type'  => ['nullable', 'required_if:role,KLIEN', 'in:REGULER,B2B_KONTRAK'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'address'      => ['nullable', 'string'],
            'pic_name'     => ['nullable', 'string', 'max:100'],
            'pic_phone'    => ['nullable', 'string', 'max:20'],
            'is_active'    => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'           => 'Nama pengguna wajib diisi.',
            'email.required'          => 'Alamat email wajib diisi.',
            'email.unique'            => 'Email ini sudah terdaftar di sistem GPA.',
            'phone.required'          => 'Nomor telepon / WhatsApp wajib diisi.',
            'password.required'       => 'Kata sandi awal akun wajib diisi.',
            'password.min'            => 'Kata sandi minimal berjumlah 8 karakter.',
            'role.required'           => 'Wewenang peran akun wajib ditentukan.',
            'client_type.required_if' => 'Jenis klien (Reguler/B2B) wajib dipilih jika peran adalah KLIEN.',
        ];
    }
}
