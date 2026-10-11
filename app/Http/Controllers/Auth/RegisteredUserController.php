<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $email = $data['email'] ?? null;
        $emailIsPlaceholder = blank($email);

        $user = User::create([
            'client_type' => 'reguler',
            'name' => $data['name'],
            'business_name' => $data['business_name'],
            'email' => $emailIsPlaceholder ? $this->placeholderEmail($data['phone'], $data['name']) : $email,
            'email_is_placeholder' => $emailIsPlaceholder,
            'phone' => $data['phone'],
            'address' => $data['address'],
            'delivery_zone' => $data['delivery_zone'],
            'delivery_window' => $data['delivery_window'],
            'vehicle_access' => $data['vehicle_access'],
            'delivery_notes' => $data['delivery_notes'] ?? null,
            'payment_method' => $data['payment_method'],
            'preferred_commodities' => $data['commodities'],
            'otp_verified_at' => now(),
            'integrity_accepted_at' => now(),
            'password' => Hash::make($data['password']),
        ]);

        $request->session()->forget('gpa.otp');

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    private function placeholderEmail(string $phone, string $name): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        $slug = Str::limit((string) Str::slug($name), 20, '') ?: 'klien';
        $base = $slug.'.'.$digits;
        $email = $base.'@klien.agroorder.invalid';
        $suffix = 1;

        while (User::where('email', $email)->exists()) {
            $email = $base.'+'.$suffix.'@klien.agroorder.invalid';
            $suffix++;
        }

        return $email;
    }
}
