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
}
