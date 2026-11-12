<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Concerns\HasCreditProfile;
use App\Models\Concerns\PresentsUserProfile;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    // Trait factory dan notifikasi untuk autentikasi serta pengujian model User
    use HasCreditProfile, HasFactory, Notifiable, PresentsUserProfile;

    // Atribut kolom yang dapat diisi secara mass-assignment
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'client_type',
        'company_name',
        'address',
        'pic_name',
        'pic_phone',
        'is_active',
        'email_verified_at',
    ];

    // Atribut data tersembunyi saat model diserialisasi ke array atau JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Konversi tipe data atribut model pengguna
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean'
        ];
    }

    // Atribut accessor dinamis yang otomatis dilampirkan pada representasi array model
    protected $appends = [
        'role_key',
        'badge',
        'division',
        'hub',
        'code',
        'nik',
    ];

    // Memeriksa apakah pengguna memiliki salah satu dari peran wewenang yang ditentukan
    public function hasRole(string|UserRole ...$roles): bool
    {
        $roleStrings = array_map(fn($role) => $role instanceof UserRole ? $role->value : $role, $roles);
        return in_array($this->role, $roleStrings, true);
    }

    // Relasi ke seluruh transaksi pemesanan yang dibuat oleh pengguna
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // Relasi ke kontrak perjanjian kerja sama (PKS) komoditas B2B
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    // Relasi ke dokumen faktur penagihan yang diterbitkan untuk pengguna
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
