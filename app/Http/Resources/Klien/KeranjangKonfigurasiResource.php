<?php

namespace App\Http\Resources\Klien;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Resource untuk merangkum konfigurasi awal keranjang belanja, jadwal pengiriman, dock penerima, dan limit plafon kredit
class KeranjangKonfigurasiResource extends JsonResource
{
    protected array $options;

    public function __construct($resource, array $options = [])
    {
        parent::__construct($resource);
        $this->options = $options;
    }

    public function toArray(Request $request): array
    {
        /** @var User|null $user */
        $user = $this->resource;
        $defaultAddress = $user?->address ?: '';
        $isB2b = (bool) $user?->is_b2b;
        $defaultDeliveryDate = $this->options['default_delivery_date'] ?? now()->addDay()->format('Y-m-d');

        $docks = $this->options['docks'] ?? [
            [
                'key'      => 'dock-utama',
                'name'     => $user?->company_name ?: ($user?->name ?: 'Alamat Terdaftar'),
                'address'  => $defaultAddress,
                'chip'     => 'Alamat Utama',
                'access'   => 'Akses Pengiriman Terdaftar',
            ],
        ];

        return [
            'date'              => $defaultDeliveryDate,
            'window'            => '04:00 - 06:00 WIB (Subuh)',
            'driver_note'       => 'Harap konfirmasi ke PIC dock penerimaan 30 menit sebelum armada tiba.',
            'dock'              => 'dock-utama',
            'docks'             => $docks,
            'default_address'   => $defaultAddress,
            'payment'           => $isB2b ? 'TEMPO_TOP' : 'TRANSFER_BANK',
            'status'            => 'Draf Siap Diajukan',
            'submitted_status'  => 'Menunggu Verifikasi Sekretaris',
            'tax_settings'      => $this->options['tax_settings'] ?? [],
            'shipping_settings' => $this->options['shipping_settings'] ?? [],
            'credit_limit'      => (float) ($this->options['credit_limit'] ?? 0.0),
            'credit_available'  => (float) ($this->options['credit_available'] ?? 0.0),
            'is_frozen'         => (bool) ($this->options['is_frozen'] ?? false),
        ];
    }
}

