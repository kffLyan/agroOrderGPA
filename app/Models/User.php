<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'client_type',
        'name',
        'business_name',
        'email',
        'phone',
        'email_is_placeholder',
        'address',
        'delivery_zone',
        'delivery_window',
        'vehicle_access',
        'delivery_notes',
        'payment_method',
        'preferred_commodities',
        'otp_verified_at',
        'integrity_accepted_at',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_is_placeholder' => 'boolean',
            'preferred_commodities' => 'array',
            'otp_verified_at' => 'datetime',
            'integrity_accepted_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
