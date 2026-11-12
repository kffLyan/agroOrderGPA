<?php

namespace App\Enums;

enum ContractStatus: string
{
    case PENDING          = 'PENDING';
    case PENDING_APPROVAL = 'PENDING_APPROVAL';
    case ACTIVE           = 'ACTIVE';
    case REVISION         = 'REVISION';
    case REJECTED         = 'REJECTED';
    case TERMINATED       = 'TERMINATED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING, self::PENDING_APPROVAL => 'Menunggu Persetujuan Direktur',
            self::ACTIVE                          => 'Aktif Berjalan',
            self::REVISION                        => 'Memerlukan Revisi',
            self::REJECTED                        => 'Ditolak',
            self::TERMINATED                      => 'Berakhir / Diberhentikan',
        };
    }
}
