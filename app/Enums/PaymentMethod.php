<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case TRANSFER_BANK = 'TRANSFER_BANK';
    case QRIS          = 'QRIS';
    case CASH          = 'CASH';
    case COD           = 'COD';
    case TEMPO_TOP     = 'TEMPO_TOP';

    public function label(): string
    {
        return match ($this) {
            self::TRANSFER_BANK => 'Transfer Bank (BCA / Mandiri)',
            self::QRIS          => 'QRIS Instan',
            self::CASH          => 'Tunai / Cash (Kasir)',
            self::COD           => 'Cash on Delivery (COD)',
            self::TEMPO_TOP     => 'Tempo Pembayaran B2B (TOP)',
        };
    }
}
