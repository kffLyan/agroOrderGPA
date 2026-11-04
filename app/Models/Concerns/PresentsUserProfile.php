<?php

namespace App\Models\Concerns;

use App\Enums\UserRole;

trait PresentsUserProfile
{
    // Mengambil objek enum UserRole resmi berdasarkan peran akun pengguna.
    public function getRoleEnumAttribute(): ?UserRole
    {
        return UserRole::tryFrom((string) $this->role);
    }

    // Mengembalikan identifier peran dalam format lowercase standar sistem.
    public function getRoleKeyAttribute(): string
    {
        return $this->role_enum?->roleKey() ?? 'klien';
    }

    // Mengembalikan singkatan kode lencana 3 huruf peran pengguna (DIR, SEK, KRD, ARM, KLN).
    public function getBadgeAttribute(): string
    {
        return $this->role_enum?->badgeCode() ?? 'KLN';
    }

    // Menentukan nama divisi struktural atau nama korporasi mitra klien.
    public function getDivisionAttribute(): string
    {
        return match (strtoupper((string) $this->role)) {
            'DIREKTUR'    => 'Direksi Pusat / Kebijakan',
            'SEKRETARIS'  => 'Sekretariat Operasional / Keuangan',
            'KOORDINATOR' => 'STA & Timbangan Lapangan',
            'ARMADA'      => 'Armada & Distribusi Fisik',
            default       => $this->company_name ?: ($this->name ?: 'Mitra B2B Buyer'),
        };
    }

    // Menentukan wilayah sentra operasional atau jangkauan logistik pengguna.
    public function getHubAttribute(): string
    {
        return match (strtoupper((string) $this->role)) {
            'DIREKTUR'    => 'Kantor Pusat GPA / Seluruh Hub',
            'SEKRETARIS'  => 'Kantor Pusat GPA',
            'KOORDINATOR' => 'STA Cianjur & Lembang',
            'ARMADA'      => 'Pool Distribusi Bandung',
            default       => 'Jawa Barat & Jabodetabek',
        };
    }

    // Menghasilkan kode identitas resmi pengguna dalam format BADGE-XXX.
    public function getCodeAttribute(): string
    {
        return strtoupper($this->badge) . '-' . str_pad((string) $this->id, 3, '0', STR_PAD_LEFT);
    }

    // Format NIK kepegawaian standar internal koperasi GPA.
    public function getNikAttribute(): string
    {
        return '3201-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT) . '-0001';
    }

    // Mengambil nama person-in-charge (PIC) kontak resmi pengguna.
    public function getContactAttribute(): string
    {
        return $this->pic_name ?: ($this->name ?: '—');
    }

    // Label tipe entitas kemitraan klien bebas underscore
    public function getClientTypeLabelAttribute(): string
    {
        return match ($this->client_type) {
            'B2B_KONTRAK' => 'Mitra B2B Kontrak',
            'REGULER'     => 'Pelanggan Reguler',
            default       => $this->client_type ? ucwords(strtolower(str_replace('_', ' ', $this->client_type))) : 'Pelanggan Reguler',
        };
    }
}
