<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => PhoneNumber::normalize($this->input('phone')),
            'email' => filled($this->input('email')) ? mb_strtolower(trim((string) $this->input('email'))) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'business_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+62\d{9,15}$/', Rule::unique(User::class, 'phone')],
            'email' => ['nullable', 'string', 'email', 'max:150', Rule::unique(User::class, 'email')],
            'address' => ['required', 'string', 'max:500'],
            'delivery_zone' => ['required', Rule::in(array_keys(config('clients.zones')))],
            'delivery_window' => ['required', Rule::in(array_keys(config('clients.windows')))],
            'vehicle_access' => ['required', Rule::in(array_keys(config('clients.vehicles')))],
            'delivery_notes' => ['nullable', 'string', 'max:500'],
            'commodities' => ['required', 'array', 'min:1'],
            'commodities.*' => ['string', Rule::in(array_keys(config('clients.commodities')))],
            'payment_method' => ['required', Rule::in(array_keys(config('clients.payment_methods')))],
            'password' => ['required', 'confirmed', Password::defaults()],
            'integrity_accepted' => ['required', 'accepted'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $otp = $this->session()->get('gpa.otp');

            $verified = is_array($otp)
                && ($otp['phone'] ?? null) === $this->input('phone')
                && (bool) ($otp['verified'] ?? false);

            if (! $verified) {
                $validator->errors()->add('phone', 'Verifikasi kode OTP WhatsApp terlebih dahulu sebelum menyelesaikan pendaftaran.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama penanggung jawab wajib diisi.',
            'business_name.required' => 'Nama usaha wajib diisi.',
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'phone.regex' => 'Nomor WhatsApp tidak valid. Gunakan format 9-15 digit setelah kode negara.',
            'phone.unique' => 'Nomor WhatsApp ini sudah terdaftar. Gunakan nomor lain atau hubungi tim operasional GPA.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'address.required' => 'Alamat lengkap penerimaan bahan baku wajib diisi.',
            'delivery_zone.required' => 'Zona atau wilayah pengiriman wajib dipilih.',
            'delivery_window.required' => 'Jendela waktu penerimaan wajib dipilih.',
            'vehicle_access.required' => 'Akses kendaraan bongkar muat wajib dipilih.',
            'commodities.required' => 'Pilih minimal satu komoditas inti sebagai estimasi kebutuhan harian.',
            'commodities.min' => 'Pilih minimal satu komoditas inti sebagai estimasi kebutuhan harian.',
            'commodities.*.in' => 'Komoditas yang dipilih tidak tersedia di katalog hari ini.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'password.required' => 'Kata sandi akun portal wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'integrity_accepted.required' => 'Anda wajib menyetujui pakta integritas penimbangan bersih (Rule 04 & 05).',
            'integrity_accepted.accepted' => 'Anda wajib menyetujui pakta integritas penimbangan bersih (Rule 04 & 05).',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama penanggung jawab',
            'business_name' => 'nama usaha',
            'phone' => 'nomor WhatsApp',
            'email' => 'alamat email',
            'address' => 'alamat lengkap penerimaan',
            'delivery_zone' => 'zona pengiriman',
            'delivery_window' => 'jendela waktu penerimaan',
            'vehicle_access' => 'akses kendaraan',
            'delivery_notes' => 'catatan khusus',
            'commodities' => 'preferensi komoditas',
            'payment_method' => 'metode pembayaran',
            'password' => 'kata sandi',
        ];
    }
}
