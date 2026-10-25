<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContractSeeder extends Seeder
{
    /**
     * Seed B2B partnership contracts.
     */
    public function run(): void
    {
        DB::table('contracts')->truncate();

        DB::table('contracts')->insert([
            // 3 Kontrak Pending Otorisasi Direktur Utama
            [
                'id'                         => 1,
                'user_id'                    => 9, // Hotel Grand Pangrango
                'product_id'                 => 1, // Selada Romaine
                'contract_number'            => 'CTR-B2B-2025-089',
                'fixed_price_per_kg'         => 13800.00,
                'top_days'                   => 30,
                'committed_volume_per_cycle' => 3200.00,
                'status'                     => 'PENDING_APPROVAL',
                'approved_by'                => null,
                'approved_at'                => null,
                'created_at'                 => '2026-10-10 09:15:00',
                'updated_at'                 => '2026-10-10 09:15:00',
            ],
            [
                'id'                         => 2,
                'user_id'                    => 10, // PT Boga Rasa Kulina Prima
                'product_id'                 => 3, // Tomat Beef
                'contract_number'            => 'CTR-B2B-2025-092',
                'fixed_price_per_kg'         => 15000.00,
                'top_days'                   => 45,
                'committed_volume_per_cycle' => 5000.00,
                'status'                     => 'PENDING_APPROVAL',
                'approved_by'                => null,
                'approved_at'                => null,
                'created_at'                 => '2026-10-12 11:30:00',
                'updated_at'                 => '2026-10-12 11:30:00',
            ],
            [
                'id'                         => 3,
                'user_id'                    => 8, // PT Aerofood ACS Indonesia
                'product_id'                 => 2, // Brokoli Super
                'contract_number'            => 'CTR-B2B-2025-095',
                'fixed_price_per_kg'         => 18000.00,
                'top_days'                   => 30,
                'committed_volume_per_cycle' => 4000.00,
                'status'                     => 'PENDING_APPROVAL',
                'approved_by'                => null,
                'approved_at'                => null,
                'created_at'                 => '2026-10-13 14:00:00',
                'updated_at'                 => '2026-10-13 14:00:00',
            ],
            // Kontrak Aktif B2B
            [
                'id'                         => 4,
                'user_id'                    => 8, // PT Aerofood ACS Indonesia
                'product_id'                 => 1, // Selada Romaine
                'contract_number'            => 'KTR-2026-ACS-001',
                'fixed_price_per_kg'         => 14000.00,
                'top_days'                   => 45,
                'committed_volume_per_cycle' => 14200.00,
                'status'                     => 'ACTIVE',
                'approved_by'                => 1,
                'approved_at'                => '2026-08-01 08:30:00',
                'created_at'                 => '2026-08-01 08:00:00',
                'updated_at'                 => '2026-08-01 08:30:00',
            ],
            [
                'id'                         => 5,
                'user_id'                    => 9, // Hotel Grand Pangrango
                'product_id'                 => 3, // Tomat Beef
                'contract_number'            => 'KTR-2026-HGP-004',
                'fixed_price_per_kg'         => 15500.00,
                'top_days'                   => 30,
                'committed_volume_per_cycle' => 9600.00,
                'status'                     => 'ACTIVE',
                'approved_by'                => 1,
                'approved_at'                => '2026-08-15 09:00:00',
                'created_at'                 => '2026-08-15 08:30:00',
                'updated_at'                 => '2026-08-15 09:00:00',
            ],
            [
                'id'                         => 6,
                'user_id'                    => 10, // PT Boga Rasa Kulina Prima
                'product_id'                 => 2, // Brokoli Super
                'contract_number'            => 'KTR-2026-BRK-007',
                'fixed_price_per_kg'         => 21000.00,
                'top_days'                   => 30,
                'committed_volume_per_cycle' => 7200.00,
                'status'                     => 'ACTIVE',
                'approved_by'                => 1,
                'approved_at'                => '2026-08-20 10:00:00',
                'created_at'                 => '2026-08-20 09:00:00',
                'updated_at'                 => '2026-08-20 10:00:00',
            ],
            [
                'id'                         => 7,
                'user_id'                    => 11, // PT Segar Makmur Ritelindo
                'product_id'                 => 5, // Kol Putih
                'contract_number'            => 'KTR-2026-SMR-019',
                'fixed_price_per_kg'         => 7500.00,
                'top_days'                   => 14,
                'committed_volume_per_cycle' => 11200.00,
                'status'                     => 'ACTIVE',
                'approved_by'                => 1,
                'approved_at'                => '2026-09-01 11:00:00',
                'created_at'                 => '2026-09-01 10:00:00',
                'updated_at'                 => '2026-09-01 11:00:00',
            ],
            [
                'id'                         => 8,
                'user_id'                    => 13, // Resto Dapur Sunda Asli
                'product_id'                 => 1, // Selada Romaine
                'contract_number'            => 'KTR-2026-DSA-019',
                'fixed_price_per_kg'         => 14200.00,
                'top_days'                   => 14,
                'committed_volume_per_cycle' => 7200.00,
                'status'                     => 'ACTIVE',
                'approved_by'                => 1,
                'approved_at'                => '2026-09-05 13:00:00',
                'created_at'                 => '2026-09-05 12:00:00',
                'updated_at'                 => '2026-09-05 13:00:00',
            ],
            [
                'id'                         => 9,
                'user_id'                    => 12, // CV Mitra Boga Nusantara
                'product_id'                 => 3, // Tomat Beef
                'contract_number'            => 'KTR-2026-MBN-008',
                'fixed_price_per_kg'         => 15000.00,
                'top_days'                   => 30,
                'committed_volume_per_cycle' => 8400.00,
                'status'                     => 'ACTIVE',
                'approved_by'                => 1,
                'approved_at'                => '2026-09-10 14:00:00',
                'created_at'                 => '2026-09-10 13:00:00',
                'updated_at'                 => '2026-09-10 14:00:00',
            ],
            [
                'id'                         => 10,
                'user_id'                    => 14, // PT AEON Indonesia
                'product_id'                 => 4, // Stroberi Ciwidey
                'contract_number'            => 'CTR-GPA-202609-0001',
                'fixed_price_per_kg'         => 24000.00,
                'top_days'                   => 30,
                'committed_volume_per_cycle' => 4500.00,
                'status'                     => 'ACTIVE',
                'approved_by'                => 1,
                'approved_at'                => '2026-09-01 08:30:00',
                'created_at'                 => '2026-09-01 08:00:00',
                'updated_at'                 => '2026-09-01 08:30:00',
            ],
        ]);
    }
}
