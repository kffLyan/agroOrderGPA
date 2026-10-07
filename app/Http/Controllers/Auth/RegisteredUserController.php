<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }
    
    public function store(Request $request): RedirectResponse
    {
        $businessName = $request->input('business_name', $request->input('company_name'));
        $clientType = strtoupper((string) ($request->input('client_type') ?: 'REGULER'));
        if (! in_array($clientType, ['REGULER', 'B2B_KONTRAK'], true)) {
            $clientType = 'REGULER';
        }
        $request->merge([
            'client_type' => $clientType,
        ]);

        if ($businessName && ! $request->has('company_name')) {
            $request->merge(['company_name' => $businessName]);
        }

        $request->validate([
            'name'         => ['required', 'string', 'max:120'],
            'email'        => ['nullable', 'string', 'lowercase', 'email', 'max:120', 'unique:users,email'],
            'phone'        => ['required', 'string', 'max:25'],
            'client_type'  => ['required', 'in:REGULER,B2B_KONTRAK'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'address'      => ['required', 'string'],
            'password'     => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required'        => 'Nama lengkap / kontak PIC wajib diisi.',
            'email.email'          => 'Format alamat email tidak valid.',
            'email.unique'         => 'Email ini sudah terdaftar di sistem GPA.',
            'phone.required'       => 'Nomor telepon / WhatsApp wajib diisi.',
            'client_type.required' => 'Jenis kemitraan klien wajib dipilih.',
            'address.required'     => 'Alamat domisili / pengiriman wajib diisi.',
            'password.required'    => 'Kata sandi wajib diisi.',
            'password.confirmed'   => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $email = $request->input('email');
        if (blank($email)) {
            $digits = preg_replace('/\D+/', '', (string) $request->phone);
            $slug = \Illuminate\Support\Str::limit((string) \Illuminate\Support\Str::slug($request->name), 20, '') ?: 'klien';
            $email = $slug.'.'.$digits.'@klien.agroorder.invalid';
            $suffix = 1;
            while (User::where('email', $email)->exists()) {
                $email = $slug.'.'.$digits.'+'.$suffix.'@klien.agroorder.invalid';
                $suffix++;
            }
        }

        // Simpan ke database sesuai skema tabel users yang ada
        $user = User::create([
            'name'         => $request->name,
            'email'        => $email,
            'phone'        => $request->phone,
            'client_type'  => $request->client_type,
            'company_name' => $request->company_name,
            'address'      => $request->address,
            'pic_name'     => $request->name,
            'pic_phone'    => $request->phone,
            'role'         => 'KLIEN',
            'is_active'    => true,
            'password'     => Hash::make($request->password),
        ]);

        $request->session()->forget('gpa.otp');

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}