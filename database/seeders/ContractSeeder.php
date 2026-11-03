<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContractSeeder extends Seeder
{
    // Menyiapkan kontrak kemitraan B2B dengan komitmen kuota pasokan dan kesepakatan harga grosir tetap
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('contracts')->truncate();

        DB::table('contracts')->insert([
            // 3 Kontrak Pending Otorisasi Direktur Utama
            [
                'id'                         => 1,
                'user_id'                    => 9, // Hotel Grand Pangrango
                'product_id'                 => 1, // Selada Romaine
                'contract_number'            => 'CTR-B2B-2026-091',
                'fixed_price_per_kg'         => 14000.00,
                'top_days'                   => 30,
                'committed_volume_per_cycle' => 3500.00,
                'status'                     => 'PENDING_APPROVAL',
                'approved_by'                => null,
                'approved_at'                => null,
                'created_at'                 => now()->subDays(2),
                'updated_at'                 => now()->subDays(2),
            ],
            [
                'id'                         => 2,
                'user_id'                    => 10, // PT Boga Rasa Kulina Prima
                'product_id'                 => 3, // Tomat Beef
                'contract_number'            => 'CTR-B2B-2026-094',
                'fixed_price_per_kg'         => 15500.00,
                'top_days'                   => 45,
                'committed_volume_per_cycle' => 5000.00,
                'status'                     => 'PENDING_APPROVAL',
                'approved_by'                => null,
                'approved_at'                => null,
                'created_at'                 => now()->subDays(1),
                'updated_at'                 => now()->subDays(1),
            ],
            [
                'id'                         => 3,
                'user_id'                    => 12, // CV Mitra Boga Nusantara
                'product_id'                 => 2, // Brokoli Super
                'contract_number'            => 'CTR-B2B-2026-098',
                'fixed_price_per_kg'         => 21000.00,
                'top_days'                   => 30,
                'committed_volume_per_cycle' => 4200.00,
                'status'                     => 'PENDING_APPROVAL',
                'approved_by'                => null,
                'approved_at'                => null,
                'created_at'                 => now()->subHours(6),
                'updated_at'                 => now()->subHours(6),
            ],
            // 7 Kontrak Aktif B2B Berjalan
            [
                'id'                         => 4,
                'user_id'                    => 8, // PT Aerofood ACS Indonesia
                'product_id'                 => 1, // Selada Romaine
                'contract_number'            => 'KTR-2026-ACS-001',
                'fixed_price_per_kg'         => 14000.00,
                'top_days'                   => 45,
                'committed_volume_per_cycle' => 14000.00,
                'status'                     => 'ACTIVE',
                'approved_by'                => 1,
                'approved_at'                => now()->subMonths(2),
                'created_at'                 => now()->subMonths(2),
                'updated_at'                 => now()->subMonths(2),
            ],
            [
                'id'                         => 5,
                'user_id'                    => 8, // PT Aerofood ACS Indonesia
                'product_id'                 => 2, // Brokoli Super
                'contract_number'            => 'KTR-2026-ACS-002',
                'fixed_price_per_kg'         => 21500.00,
                'top_days'                   => 45,
                'committed_volume_per_cycle' => 6000.00,
                'status'                     => 'ACTIVE',
                'approved_by'                => 1,
                'approved_at'                => now()->subMonths(2),
                'created_at'                 => now()->subMonths(2),
                'updated_at'                 => now()->subMonths(2),
            ],
            [
                'id'                         => 6,
                'user_id'                    => 9, // Hotel Grand Pangrango
                'product_id'                 => 3, // Tomat Beef
                'contract_number'            => 'KTR-2026-HGP-004',
                'fixed_price_per_kg'         => 15500.00,
                'top_days'                   => 30,
                'committed_volume_per_cycle' => 9600.00,
                'status'                     => 'ACTIVE',
                'approved_by'                => 1,
                'approved_at'                => now()->subMonths(2),
                'created_at'                 => now()->subMonths(2),
                'updated_at'                 => now()->subMonths(2),
            ],
            [
                'id'                         => 7,
                'user_id'                    => 10, // PT Boga Rasa Kulina Prima
                'product_id'                 => 2, // Brokoli Super
                'contract_number'            => 'KTR-2026-BRK-007',
                'fixed_price_per_kg'         => 21000.00,
                'top_days'                   => 30,
                'committed_volume_per_cycle' => 7200.00,
                'status'                     => 'ACTIVE',
                'approved_by'                => 1,
                'approved_at'                => now()->subMonths(2),
                'created_at'                 => now()->subMonths(2),
                'updated_at'                 => now()->subMonths(2),
            ],
            [
                'id'                         => 8,
                'user_id'                    => 11, // PT Segar Makmur Ritelindo
                'product_id'                 => 5, // Kol Putih
                'contract_number'            => 'KTR-2026-SMR-010',
                'fixed_price_per_kg'         => 7500.00,
                'top_days'                   => 30,
                'committed_volume_per_cycle' => 10000.00,
                'status'                     => 'ACTIVE',
                'approved_by'                => 1,
                'approved_at'                => now()->subMonths(1)->subDays(15),
                'created_at'                 => now()->subMonths(1)->subDays(15),
                'updated_at'                 => now()->subMonths(1)->subDays(15),
            ],
            [
                'id'                         => 9,
                'user_id'                    => 13, // Resto Dapur Sunda Asli
                'product_id'                 => 3, // Tomat Beef
                'contract_number'            => 'KTR-2026-DSA-012',
                'fixed_price_per_kg'         => 15000.00,
                'top_days'                   => 30,
                'committed_volume_per_cycle' => 4500.00,
                'status'                     => 'ACTIVE',
                'approved_by'                => 1,
                'approved_at'                => now()->subMonths(1),
                'created_at'                 => now()->subMonths(1),
                'updated_at'                 => now()->subMonths(1),
            ],
            [
                'id'                         => 10,
                'user_id'                    => 14, // PT AEON Indonesia
                'product_id'                 => 4, // Stroberi Ciwidey
                'contract_number'            => 'KTR-2026-AEO-015',
                'fixed_price_per_kg'         => 24500.00,
                'top_days'                   => 30,
                'committed_volume_per_cycle' => 3000.00,
                'status'                     => 'ACTIVE',
                'approved_by'                => 1,
                'approved_at'                => now()->subMonths(1),
                'created_at'                 => now()->subMonths(1),
                'updated_at'                 => now()->subMonths(1),
            ],
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
