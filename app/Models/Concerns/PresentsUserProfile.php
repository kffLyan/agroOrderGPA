<?php

namespace App\Models\Concerns;

use App\Enums\UserRole;

trait PresentsUserProfile
{
    /**
     * Objek Enum peran resmi
     */
    public function getRoleEnumAttribute(): ?UserRole
    {
        return UserRole::tryFrom((string) $this->role);
    }

    /**
     * Kode kunci role dalam format lowercase standar
     */
    public function getRoleKeyAttribute(): string
    {
        return $this->role_enum?->roleKey() ?? 'client';
    }

    /**
     * Singkatan badge peran 3 huruf
     */
    public function getBadgeAttribute(): string
    {
        return $this->role_enum?->badgeCode() ?? 'KLN';
    }

    /**
     * Nama divisi atau nama entitas perusahaan klien
     */
    public function getDivisionAttribute(): string
    {
        return match ($this->role_key) {
            'director'    => 'Direksi Pusat / Policy',
            'admin'       => 'Central Office / Keuangan',
            'coordinator' => 'STA & Timbangan Lapangan',
            'driver'      => 'Armada & Distribusi Fisik',
            default       => $this->company_name ?: ($this->name ?: 'Mitra B2B Buyer'),
        };
    }

    /**
     * Wilayah penugasan atau sentra operasional
     */
    public function getHubAttribute(): string
    {
        return match ($this->role_key) {
            'director'    => 'HQ Jakarta / All Hub',
            'admin'       => 'Kantor Pusat GPA',
            'coordinator' => 'STA Cianjur & Lembang',
            'driver'      => 'Pool Distribusi Bandung',
            default       => 'Jawa Barat & Jabodetabek',
        };
    }

    /**
     * Kode identitas sistem terformat (misal: DIR-001)
     */
    public function getCodeAttribute(): string
    {
        return strtoupper($this->badge) . '-' . str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Format NIK resmi pengguna
     */
    public function getNikAttribute(): string
    {
        return '3201-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT) . '-0001';
    }

    /**
     * Nama kontak narahubung / PIC
     */
    public function getContactAttribute(): string
    {
        return $this->pic_name ?: ($this->name ?: '—');
    }
}
