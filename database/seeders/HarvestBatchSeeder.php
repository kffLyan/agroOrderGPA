<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HarvestBatchSeeder extends Seeder
{
    /**
     * Seed harvest batches and warehouse inventory lots.
     */
    public function run(): void
    {
        DB::table('harvest_batches')->truncate();

        DB::table('harvest_batches')->insert([
            // Komoditas 1: Selada Romaine (Binaan: 18000 kg, Buffer: 3710 kg)
            [
                'id'                 => 1,
                'product_id'         => 1,
                'source_type'        => 'PETANI_BINAAN',
                'supplier_name'      => 'Kelompok Tani Hidroponik Panundaan',
                'batch_date'         => '2026-10-01',
                'initial_quantity'   => 18000.00,
                'available_quantity' => 1450.00,
                'inputted_by'        => 4,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => 2,
                'product_id'         => 1,
                'source_type'        => 'BUFFER_LUAR',
                'supplier_name'      => 'Mitra Cadangan Rancabali Hub',
                'batch_date'         => '2026-10-05',
                'initial_quantity'   => 3710.00,
                'available_quantity' => 800.00,
                'inputted_by'        => 4,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            // Komoditas 2: Brokoli Super (Binaan: 6100 kg, Buffer: 1140 kg)
            [
                'id'                 => 3,
                'product_id'         => 2,
                'source_type'        => 'PETANI_BINAAN',
                'supplier_name'      => 'Gapoktan Sayur Lembang Dataran Tinggi',
                'batch_date'         => '2026-10-02',
                'initial_quantity'   => 6100.00,
                'available_quantity' => 480.00,
                'inputted_by'        => 4,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => 4,
                'product_id'         => 2,
                'source_type'        => 'BUFFER_LUAR',
                'supplier_name'      => 'Mitra Tani Sejahtera Parongpong',
                'batch_date'         => '2026-10-04',
                'initial_quantity'   => 1140.00,
                'available_quantity' => 210.00,
                'inputted_by'        => 4,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            // Komoditas 3: Tomat Beef (Binaan: 9800 kg, Buffer: 2260 kg)
            [
                'id'                 => 5,
                'product_id'         => 3,
                'source_type'        => 'PETANI_BINAAN',
                'supplier_name'      => 'Sentra Greenhouse Garut Tarogong',
                'batch_date'         => '2026-10-03',
                'initial_quantity'   => 9800.00,
                'available_quantity' => 820.00,
                'inputted_by'        => 4,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => 6,
                'product_id'         => 3,
                'source_type'        => 'BUFFER_LUAR',
                'supplier_name'      => 'Mitra Cadangan Cikajang Garut',
                'batch_date'         => '2026-10-06',
                'initial_quantity'   => 2260.00,
                'available_quantity' => 350.00,
                'inputted_by'        => 4,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            // Komoditas 4: Stroberi Ciwidey (Binaan: 3900 kg, Buffer: 930 kg)
            [
                'id'                 => 7,
                'product_id'         => 4,
                'source_type'        => 'PETANI_BINAAN',
                'supplier_name'      => 'Kelompok Tani Stroberi Alamendah',
                'batch_date'         => '2026-10-05',
                'initial_quantity'   => 3900.00,
                'available_quantity' => 210.00,
                'inputted_by'        => 4,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => 8,
                'product_id'         => 4,
                'source_type'        => 'BUFFER_LUAR',
                'supplier_name'      => 'Mitra Buffer Patuha Ciwidey',
                'batch_date'         => '2026-10-07',
                'initial_quantity'   => 930.00,
                'available_quantity' => 95.00,
                'inputted_by'        => 4,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            // Komoditas 5: Kol Putih Organik (Binaan: 2200 kg, Buffer: 210 kg)
            [
                'id'                 => 9,
                'product_id'         => 5,
                'source_type'        => 'PETANI_BINAAN',
                'supplier_name'      => 'Gapoktan Organik Pangalengan Lestari',
                'batch_date'         => '2026-10-04',
                'initial_quantity'   => 2200.00,
                'available_quantity' => 600.00,
                'inputted_by'        => 4,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => 10,
                'product_id'         => 5,
                'source_type'        => 'BUFFER_LUAR',
                'supplier_name'      => 'Mitra Kebun Situ Cileunca',
                'batch_date'         => '2026-10-08',
                'initial_quantity'   => 210.00,
                'available_quantity' => 50.00,
                'inputted_by'        => 4,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
        ]);
    }
}
