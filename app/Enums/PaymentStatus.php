<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case MENUNGGU_VERIFIKASI = 'MENUNGGU_VERIFIKASI';
    case LUNAS               = 'LUNAS';
    case DITOLAK             = 'DITOLAK';

    public function label(): string
    {
        return match ($this) {
            self::MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
            self::LUNAS               => 'Lunas / Terverifikasi',
            self::DITOLAK             => 'Ditolak',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::MENUNGGU_VERIFIKASI => 'bg-warning-soft text-warning-deep border-warning',
            self::LUNAS               => 'bg-accent text-success-deep border-accent-edge',
            self::DITOLAK             => 'bg-danger text-white border-danger',
        };
    }
}
