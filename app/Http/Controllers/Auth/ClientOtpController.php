<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\RegistrationOtpMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ClientOtpController extends Controller
{
    private const SESSION_KEY = 'gpa.otp';
    private const EMAIL_SESSION_KEY = 'gpa.email_otp';

    private const TTL_MINUTES = 5;

    // Mengirimkan kode OTP verifikasi registrasi melalui WhatsApp atau email sesuai preferensi klien.
    public function send(Request $request): JsonResponse
    {
        if ($request->filled('email')) {
            return $this->sendEmailOtp($request);
        }

        $phone = $this->validatedPhone($request);
        $code = (string) random_int(100000, 999999);

        $request->session()->put(self::SESSION_KEY, [
            'phone' => $phone,
            'hash' => Hash::make($code),
            'verified' => false,
            'expires_at' => now()->addMinutes(self::TTL_MINUTES)->getTimestamp(),
        ]);

        return $this->sentResponse($phone, $code, 'Kode OTP telah dikirim ke WhatsApp Anda.');
    }

    // Mengirimkan 6-digit kode OTP verifikasi kepemilikan email pendaftaran klien.
    public function sendEmailOtp(Request $request): JsonResponse
    {
        $emailRule = 'email:rfc';

        $request->validate([
            'email' => ['required', 'string', 'lowercase', $emailRule, 'max:120', 'unique:users,email'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Alamat email ini sudah terdaftar di sistem GPA. Silakan login.',
        ]);

        $email = strtolower(trim((string) $request->input('email')));
        $code = (string) random_int(100000, 999999);

        $request->session()->put(self::EMAIL_SESSION_KEY, [
            'email' => $email,
            'hash' => Hash::make($code),
            'verified' => false,
            'expires_at' => now()->addMinutes(self::TTL_MINUTES)->getTimestamp(),
        ]);

        try {
            Mail::to($email)->send(new RegistrationOtpMail($code, $email));
        } catch (\Throwable $exception) {
            report($exception);
            if (! app()->environment(['local', 'testing'])) {
                $detail = config('app.debug') ? ' (' . $exception->getMessage() . ')' : '';
                throw ValidationException::withMessages([
                    'email' => 'Gagal mengirim email verifikasi: server email belum terhubung' . $detail . '. Periksa konfigurasi MAIL di .env.',
                ]);
            }
        }

        return response()->json([
            'message' => 'Kode OTP 6-digit telah dikirim ke inbox email Anda.',
            'email' => $email,
            'expires_in' => self::TTL_MINUTES * 60,
            'dev_code' => app()->environment(['local', 'testing']) ? $code : null,
        ]);
    }

    // Mengirimkan ulang kode OTP verifikasi aktif ke kontak klien.
    public function resend(Request $request): JsonResponse
    {
        return $this->send($request);
    }

    // Memvalidasi kesesuaian kode OTP 6-digit WhatsApp yang diinput dengan token sesi.
    public function verify(Request $request): JsonResponse
    {
        if ($request->filled('email')) {
            return $this->verifyEmailOtp($request);
        }

        $data = $request->validate([
            'phone' => ['required', 'string'],
            'code' => ['required', 'digits:6'],
        ], [
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'code.required' => 'Kode OTP wajib diisi.',
            'code.digits' => 'Kode OTP terdiri dari 6 digit angka.',
        ]);

        $phone = $this->normalizePhone($data['phone']);
        $otp = $request->session()->get(self::SESSION_KEY);

        if ($phone === null || ! is_array($otp) || ($otp['phone'] ?? null) !== $phone) {
            throw ValidationException::withMessages([
                'code' => 'Kode OTP tidak sesuai dengan nomor yang diminta. Silakan kirim ulang kode.',
            ]);
        }

        if (($otp['expires_at'] ?? 0) < now()->getTimestamp()) {
            throw ValidationException::withMessages([
                'code' => 'Kode OTP sudah kedaluwarsa. Silakan kirim ulang kode.',
            ]);
        }

        if (! Hash::check($data['code'], $otp['hash'])) {
            throw ValidationException::withMessages([
                'code' => 'Kode OTP salah. Periksa kembali 6 digit kode Anda.',
            ]);
        }

        $request->session()->put(self::SESSION_KEY, array_merge($otp, [
            'verified' => true,
            'verified_at' => now()->getTimestamp(),
        ]));

        return response()->json([
            'message' => 'Nomor WhatsApp terverifikasi.',
            'phone' => $phone,
        ]);
    }

    // Memvalidasi kesesuaian kode OTP 6-digit email pendaftaran dengan token sesi aktif.
    public function verifyEmailOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'code'  => ['required', 'digits:6'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format alamat email tidak valid.',
            'code.required'  => 'Kode OTP wajib diisi.',
            'code.digits'    => 'Kode OTP terdiri dari 6 digit angka.',
        ]);

        $email = strtolower(trim((string) $data['email']));
        $otp = $request->session()->get(self::EMAIL_SESSION_KEY);

        if (! is_array($otp) || ($otp['email'] ?? null) !== $email) {
            throw ValidationException::withMessages([
                'code' => 'Kode OTP tidak sesuai dengan email yang diminta. Silakan kirim ulang kode.',
            ]);
        }

        if (($otp['expires_at'] ?? 0) < now()->getTimestamp()) {
            throw ValidationException::withMessages([
                'code' => 'Kode OTP sudah kedaluwarsa (berlaku 5 menit). Silakan kirim ulang kode.',
            ]);
        }

        if (! Hash::check($data['code'], $otp['hash'])) {
            throw ValidationException::withMessages([
                'code' => 'Kode OTP salah. Periksa kembali 6 digit kode yang dikirim ke email Anda.',
            ]);
        }

        $request->session()->put(self::EMAIL_SESSION_KEY, array_merge($otp, [
            'verified' => true,
            'verified_at' => now()->getTimestamp(),
        ]));

        return response()->json([
            'message' => 'Alamat email berhasil diverifikasi.',
            'email'   => $email,
            'verified' => true,
        ]);
    }

    // Melakukan normalisasi nomor telepon seluler ke format internasional E.164 (+62).
    private function normalizePhone(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw);

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return strlen($digits) >= 10 ? '+' . $digits : null;
    }

    // Memvalidasi dan memastikan format nomor WhatsApp terisi dengan benar.
    private function validatedPhone(Request $request): string
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:20'],
        ], [
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
        ]);

        $phone = $this->normalizePhone($request->input('phone'));

        if ($phone === null) {
            throw ValidationException::withMessages([
                'phone' => 'Nomor WhatsApp tidak valid. Gunakan format 9-15 digit setelah kode negara.',
            ]);
        }

        return $phone;
    }

    private function sentResponse(string $phone, string $code, string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'phone' => $phone,
            'expires_in' => self::TTL_MINUTES * 60,
            'dev_code' => app()->environment(['local', 'testing']) ? $code : null,
        ]);
    }
}
