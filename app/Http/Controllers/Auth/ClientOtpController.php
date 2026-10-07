<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ClientOtpController extends Controller
{
    private const SESSION_KEY = 'gpa.otp';

    private const TTL_MINUTES = 5;

    public function send(Request $request): JsonResponse
    {
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

    public function resend(Request $request): JsonResponse
    {
        return $this->send($request);
    }

    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'code' => ['required', 'digits:6'],
        ], [
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'code.required' => 'Kode OTP wajib diisi.',
            'code.digits' => 'Kode OTP terdiri dari 6 digit angka.',
        ]);

        $phone = PhoneNumber::normalize($data['phone']);
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

    private function validatedPhone(Request $request): string
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:20'],
        ], [
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
        ]);

        $phone = PhoneNumber::normalize($request->input('phone'));

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
