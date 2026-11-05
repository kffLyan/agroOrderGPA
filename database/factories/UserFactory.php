<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// Factory pembuatan instance model User untuk pengujian otomatis
class UserFactory extends Factory
{
    // Nilai cache hash password default untuk mempercepat eksekusi factory pengujian
    protected static ?string $password;

    // Mendefinisikan atribut default model User dengan data palsu yang realistis
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    // Menandai model pengguna dengan status alamat email belum diverifikasi
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
