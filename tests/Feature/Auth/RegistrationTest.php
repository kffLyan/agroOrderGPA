<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'phone'                 => '081234567890',
            'address'               => 'Jl. Perkebunan Sayur No. 1, Ciwidey',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->assertEquals('REGULER', auth()->user()->client_type);
        $this->assertNull(auth()->user()->company_name);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_succeeds_when_business_name_is_empty(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Perorangan User',
            'email'                 => 'perorangan@example.com',
            'phone'                 => '081234567891',
            'business_name'         => '',
            'address'               => 'Jl. Perkebunan Sayur No. 2, Ciwidey',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->assertNull(auth()->user()->company_name);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_requires_email(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Test User',
            'email'                 => '',
            'phone'                 => '081234567890',
            'address'               => 'Jl. Perkebunan Sayur No. 1, Ciwidey',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_unverified_klien_is_redirected_to_verify_email_screen(): void
    {
        $unverifiedKlien = \App\Models\User::factory()->unverified()->create([
            'role' => 'KLIEN',
        ]);

        $response = $this->actingAs($unverifiedKlien)->get('/klien/dashboard');
        $response->assertRedirect(route('verification.notice'));
    }

    public function test_registration_otp_can_be_sent_to_email(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $response = $this->postJson('/register/otp/send', [
            'email' => 'calon.klien@gpa.co.id',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'email', 'expires_in', 'dev_code']);

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\RegistrationOtpMail::class, function ($mail) {
            return $mail->hasTo('calon.klien@gpa.co.id');
        });
    }

    public function test_registration_otp_can_be_verified_with_correct_code(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $sendResponse = $this->postJson('/register/otp/send', [
            'email' => 'verifikasi@gpa.co.id',
        ]);

        $devCode = $sendResponse->json('dev_code');
        $this->assertNotEmpty($devCode);

        $verifyResponse = $this->postJson('/register/otp/verify', [
            'email' => 'verifikasi@gpa.co.id',
            'code'  => $devCode,
        ]);

        $verifyResponse->assertStatus(200);
        $verifyResponse->assertJson([
            'verified' => true,
        ]);
    }

    public function test_registration_otp_fails_with_wrong_code(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $this->postJson('/register/otp/send', [
            'email' => 'wrong.code@gpa.co.id',
        ]);

        $verifyResponse = $this->postJson('/register/otp/verify', [
            'email' => 'wrong.code@gpa.co.id',
            'code'  => '999999',
        ]);

        $verifyResponse->assertStatus(422);
    }

    public function test_registration_with_verified_otp_auto_verifies_email(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $sendResponse = $this->postJson('/register/otp/send', [
            'email' => 'sukses.daftar@gpa.co.id',
        ]);
        $devCode = $sendResponse->json('dev_code');

        $this->postJson('/register/otp/verify', [
            'email' => 'sukses.daftar@gpa.co.id',
            'code'  => $devCode,
        ]);

        $response = $this->post('/register', [
            'name'                  => 'Klien Terverifikasi',
            'email'                 => 'sukses.daftar@gpa.co.id',
            'phone'                 => '081298765432',
            'business_name'         => 'PT Sayuran Nusantara',
            'address'               => 'Jl. Raya Ciwidey No. 88, Bandung',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $user = auth()->user();
        $this->assertNotNull($user->email_verified_at);
        $this->assertEquals('sukses.daftar@gpa.co.id', $user->email);
    }
}
