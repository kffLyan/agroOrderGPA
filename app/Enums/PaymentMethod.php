<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case TRANSFER_BANK = 'TRANSFER_BANK';
    case QRIS          = 'QRIS';
    case CASH          = 'CASH';
    case COD           = 'COD';
    case TEMPO_TOP     = 'TEMPO_TOP';
    case SK_DIREKTUR   = 'SK_DIREKTUR';

    public function label(): string
    {
        return match ($this) {
            self::TRANSFER_BANK => 'Transfer Bank (BCA / Mandiri)',
            self::QRIS          => 'QRIS Instan',
            self::CASH          => 'Tunai / Cash (Kasir)',
            self::COD           => 'Cash on Delivery (COD)',
            self::TEMPO_TOP     => 'Tempo Pembayaran B2B (TOP)',
            self::SK_DIREKTUR   => 'Dispensasi Khusus SK Direktur',
        };
    }

    /**
     * Resolusi metode pembayaran dari input teks / channel ID
     */
    public static function fromRaw(?string $method, ?string $channelId = null): self
    {
        $raw = strtolower((string) $method);
        $ch = strtolower((string) $channelId);

        return match (true) {
            $raw === 'cash' || $raw === 'tunai' || $ch === 'cash' => self::CASH,
            $raw === 'qris' || $ch === 'qris'                     => self::QRIS,
            $raw === 'cod'                                        => self::COD,
            $raw === 'top14' || $raw === 'tempo_top'              => self::TEMPO_TOP,
            $raw === 'custom' || $raw === 'sk_direktur'           => self::SK_DIREKTUR,
            default                                               => self::TRANSFER_BANK,
        };
    }
}
