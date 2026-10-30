<?php

namespace App\Enums;

enum UserRole: string
{
    case DIREKTUR    = 'DIREKTUR';
    case SEKRETARIS  = 'SEKRETARIS';
    case KOORDINATOR = 'KOORDINATOR';
    case ARMADA      = 'ARMADA';
    case KLIEN       = 'KLIEN';

    public function label(): string
    {
        return match ($this) {
            self::DIREKTUR    => 'Direktur Utama / Eksekutif',
            self::SEKRETARIS  => 'Sekretaris Operasional',
            self::KOORDINATOR => 'Koordinator Lapangan',
            self::ARMADA      => 'Armada Logistik',
            self::KLIEN       => 'Mitra Klien',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::DIREKTUR    => 'direktur.dashboard',
            self::SEKRETARIS  => 'sekretaris.dashboard',
            self::KOORDINATOR => 'koordinator.dashboard',
            self::ARMADA      => 'armada.dashboard',
            self::KLIEN       => 'klien.dashboard',
        };
    }

    public function roleKey(): string
    {
        return match ($this) {
            self::DIREKTUR    => 'director',
            self::SEKRETARIS  => 'admin',
            self::KOORDINATOR => 'coordinator',
            self::ARMADA      => 'driver',
            self::KLIEN       => 'client',
        };
    }

    public function badgeCode(): string
    {
        return match ($this) {
            self::DIREKTUR    => 'DIR',
            self::SEKRETARIS  => 'ADM',
            self::KOORDINATOR => 'KRD',
            self::ARMADA      => 'DVR',
            self::KLIEN       => 'KLN',
        };
    }
}
