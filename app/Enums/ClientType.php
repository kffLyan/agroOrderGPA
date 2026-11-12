<?php

namespace App\Enums;

enum ClientType: string
{
    case B2B_KONTRAK = 'B2B_KONTRAK';
    case REGULER     = 'REGULER';

    public function label(): string
    {
        return match ($this) {
            self::B2B_KONTRAK => 'Mitra Kontrak B2B',
            self::REGULER     => 'Pelanggan Reguler',
        };
    }
}
