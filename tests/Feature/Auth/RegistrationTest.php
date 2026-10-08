<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Budi Setiawan',
            'business_name' => 'Dapur Rasa Nusantara',
            'phone' => '+6281234567890',
            'email' => 'purchasing@dapurrasa.co.id',
            'address' => 'Jl. Ir. H. Juanda No. 182, Dago, Coblong, Kota Bandung 40135',
            'delivery_zone' => 'BDG_RAYA',
            'delivery_window' => 'PRIORITAS_1',
            'vehicle_access' => 'VAN',
            'delivery_notes' => 'Masuk lewat gerbang barat, tekan bel 2x.',
            'commodities' => ['selada_romaine', 'tomat_beef'],
            'payment_method' => 'transfer',
            'password' => 'rahasia-kuat-123',
            'password_confirmation' => 'rahasia-kuat-123',
            'integrity_accepted' => '1',
        ], $overrides);
    }

    private function withVerifiedOtp(string $phone = '+6281234567890')
    {
        return $this->withSession([
            'gpa.otp' => [
                'phone' => $phone,
                'hash' => Hash::make('123456'),
                'verified' => true,
                'expires_at' => now()->addMinutes(5)->getTimestamp(),
            ],
        ]);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Daftar Akun Klien Baru');
        $response->assertSee('Portal Terpadu Rantai Pasok');
        $response->assertSee('Sudah memiliki akun AgroOrder GPA?');
        $response->assertSee(route('login'), false);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->withVerifiedOtp()->post('/register', $this->payload());

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $response->assertSessionHasNoErrors();

        $user = User::firstWhere('phone', '+6281234567890');

        $this->assertNotNull($user);
        $this->assertSame('Dapur Rasa Nusantara', $user->business_name);
        $this->assertSame('reguler', $user->client_type);
        $this->assertSame(['selada_romaine', 'tomat_beef'], $user->preferred_commodities);
        $this->assertNotNull($user->otp_verified_at);
        $this->assertNotNull($user->integrity_accepted_at);
        $this->assertFalse($user->email_is_placeholder);
    }

    public function test_registration_requires_verified_whatsapp_otp(): void
    {
        $response = $this->post('/register', $this->payload());

        $response->assertSessionHasErrors('phone');
        $this->assertGuest();
        $this->assertSame(0, User::count());
    }

    public function test_registration_requires_at_least_one_commodity(): void
    {
        $response = $this->withVerifiedOtp()->post('/register', $this->payload(['commodities' => []]));

        $response->assertSessionHasErrors('commodities');
        $this->assertGuest();
    }

    public function test_registration_rejects_invalid_commodity(): void
    {
        $response = $this->withVerifiedOtp()->post('/register', $this->payload(['commodities' => ['daging_sapi']]));

        $response->assertSessionHasErrors('commodities.0');
        $this->assertGuest();
    }

    public function test_phone_must_be_unique(): void
    {
        $this->withVerifiedOtp()->post('/register', $this->payload());
        $this->post('/logout');

        $response = $this->withVerifiedOtp()->post('/register', $this->payload([
            'name' => 'Sari Wulandari',
            'email' => 'sari@katerin.test',
        ]));

        $response->assertSessionHasErrors('phone');
        $this->assertSame(1, User::count());
    }

    public function test_phone_is_normalized_from_local_format(): void
    {
        $this->withVerifiedOtp('+6281234567890')->post('/register', $this->payload([
            'phone' => '0812-3456-7890',
        ]));

        $this->assertSame(1, User::where('phone', '+6281234567890')->count());
    }

    public function test_email_is_optional_and_falls_back_to_placeholder(): void
    {
        $this->withVerifiedOtp()->post('/register', $this->payload(['email' => null]));

        $user = User::firstWhere('phone', '+6281234567890');

        $this->assertNotNull($user);
        $this->assertTrue($user->email_is_placeholder);
        $this->assertStringEndsWith('@klien.agroorder.invalid', $user->email);
    }

    public function test_integrity_pact_is_mandatory(): void
    {
        $response = $this->withVerifiedOtp()->post('/register', $this->payload(['integrity_accepted' => null]));

        $response->assertSessionHasErrors('integrity_accepted');
        $this->assertGuest();
    }

    public function test_otp_can_be_sent_and_verified(): void
    {
        $send = $this->postJson('/register/otp/send', ['phone' => '0812-3456-7890']);

        $send->assertOk();
        $send->assertJsonPath('phone', '+6281234567890');

        $code = $send->json('dev_code');

        $this->assertSame(6, strlen((string) $code));

        $verify = $this->postJson('/register/otp/verify', [
            'phone' => '+6281234567890',
            'code' => $code,
        ]);

        $verify->assertOk();
        $verify->assertJsonPath('phone', '+6281234567890');
    }

    public function test_otp_verification_rejects_wrong_code(): void
    {
        $this->postJson('/register/otp/send', ['phone' => '+6281234567890']);

        $this->postJson('/register/otp/verify', ['phone' => '+6281234567890', 'code' => '000000'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }

    public function test_otp_send_rejects_invalid_phone(): void
    {
        $this->postJson('/register/otp/send', ['phone' => '123'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('phone');
    }
}
