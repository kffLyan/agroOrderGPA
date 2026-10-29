<?php

namespace App\Enums;

// Daftar status pesanan resmi (memakai Enum PHP 8 agar pilihan status terkunci dan bebas typo)
enum OrderStatus: string
{
    case MENUNGGU_VERIFIKASI  = 'MENUNGGU_VERIFIKASI';
    case MENUNGGU_PEMBAYARAN  = 'MENUNGGU_PEMBAYARAN';
    case LUNAS                = 'LUNAS';
    case SIAP_KIRIM           = 'SIAP_KIRIM';
    case DALAM_PENGIRIMAN     = 'DALAM_PENGIRIMAN';
    case SELESAI              = 'SELESAI';
    case SELESAI_CATATAN      = 'SELESAI_CATATAN';
    case BATAL                = 'BATAL';

    /**
     * Label representasi bahasa Indonesia yang ramah pengguna.
     */
    public function label(): string
    {
        return match ($this) {
            self::MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
            self::MENUNGGU_PEMBAYARAN => 'Menunggu Pembayaran',
            self::LUNAS               => 'Lunas Terverifikasi',
            self::SIAP_KIRIM          => 'Siap Dikirim',
            self::DALAM_PENGIRIMAN    => 'Dalam Pengiriman',
            self::SELESAI             => 'Selesai Sempurna',
            self::SELESAI_CATATAN     => 'Selesai dengan Catatan (Retur)',
            self::BATAL               => 'Dibatalkan',
        };
    }

    /**
     * Kelas styling warna badge Tailwind CSS.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::MENUNGGU_VERIFIKASI => 'bg-warning-soft text-warning-deep border-warning',
            self::MENUNGGU_PEMBAYARAN => 'bg-info-soft text-info-deep border-info',
            self::LUNAS               => 'bg-accent text-success-ink border-accent-edge',
            self::SIAP_KIRIM          => 'bg-teal-50 text-teal-800 border-teal-200',
            self::DALAM_PENGIRIMAN    => 'bg-blue-50 text-blue-800 border-blue-200',
            self::SELESAI             => 'bg-success/15 text-success-deep border-success/30',
            self::SELESAI_CATATAN     => 'bg-amber-50 text-amber-800 border-amber-300',
            self::BATAL               => 'bg-danger/10 text-danger border-danger/30',
        };
    }

    /**
     * Memeriksa apakah status termasuk dalam pengiriman yang aktif atau selesai.
     */
    public function isCompletedOrDelivering(): bool
    {
        return in_array($this, [
            self::DALAM_PENGIRIMAN,
            self::SELESAI,
            self::SELESAI_CATATAN,
        ], true);
    }
}
