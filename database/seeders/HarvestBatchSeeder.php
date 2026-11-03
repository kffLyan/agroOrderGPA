<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HarvestBatchSeeder extends Seeder
{
    // Menyiapkan lot batch panen masuk dari kebun binaan dan buffer mitra luar untuk neraca stok gudang
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('harvest_batches')->truncate();

        DB::table('harvest_batches')->insert([
            // =========================================================================
            // MINGGU W0 (Pekan Berjalan / Hari Ini & 1-3 Hari Lalu)
            // =========================================================================
            [
                'id'                 => 1,
                'product_id'         => 1, // Selada Romaine
                'source_type'        => 'PETANI_BINAAN',
                'supplier_name'      => 'Kelompok Tani Hidroponik Panundaan',
                'batch_date'         => now()->toDateString(),
                'initial_quantity'   => 4500.00,
                'available_quantity' => 2800.00,
                'grade'              => 'Grade A',
                'qc_notes'           => 'Pemetikan subuh hari, daun segar hijau cerah, bebas pestisida.',
                'inputted_by'        => 4,
                'created_at'         => now()->subHours(5),
                'updated_at'         => now()->subHours(5),
            ],
            [
                'id'                 => 2,
                'product_id'         => 2, // Brokoli Super
                'source_type'        => 'PETANI_BINAAN',
                'supplier_name'      => 'Gapoktan Sayur Lembang Dataran Tinggi',
                'batch_date'         => now()->subDay()->toDateString(),
                'initial_quantity'   => 2200.00,
                'available_quantity' => 1100.00,
                'grade'              => 'Grade A',
                'qc_notes'           => 'Kuntum padat tanpa ulat, sortasi seragam standar Horeca.',
                'inputted_by'        => 4,
                'created_at'         => now()->subDay()->subHours(3),
                'updated_at'         => now()->subDay()->subHours(3),
            ],
            [
                'id'                 => 3,
                'product_id'         => 3, // Tomat Beef
                'source_type'        => 'PETANI_BINAAN',
                'supplier_name'      => 'Sentra Greenhouse Garut Tarogong',
                'batch_date'         => now()->subDays(2)->toDateString(),
                'initial_quantity'   => 3500.00,
                'available_quantity' => 1950.00,
                'grade'              => 'Grade A',
                'qc_notes'           => 'Daging padat tebal, tingkat kematangan 85%, warna merah merata.',
                'inputted_by'        => 5,
                'created_at'         => now()->subDays(2)->subHours(4),
                'updated_at'         => now()->subDays(2)->subHours(4),
            ],
            [
                'id'                 => 4,
                'product_id'         => 4, // Stroberi Ciwidey
                'source_type'        => 'PETANI_BINAAN',
                'supplier_name'      => 'Kelompok Tani Stroberi Alamendah',
                'batch_date'         => now()->toDateString(),
                'initial_quantity'   => 1200.00,
                'available_quantity' => 750.00,
                'grade'              => 'Grade A',
                'qc_notes'           => 'Manis brix 8.5, kemasan mika pack 500 gram higienis.',
                'inputted_by'        => 5,
                'created_at'         => now()->subHours(3),
                'updated_at'         => now()->subHours(3),
            ],
            [
                'id'                 => 5,
                'product_id'         => 5, // Kol Putih
                'source_type'        => 'BUFFER_LUAR',
                'supplier_name'      => 'Mitra Cadangan Rancabali Hub',
                'batch_date'         => now()->subDays(3)->toDateString(),
                'initial_quantity'   => 3000.00,
                'available_quantity' => 1400.00,
                'grade'              => 'Grade A',
                'qc_notes'           => 'Krop bulat padat, daun luar bersih, lolos uji timbangan tera.',
                'inputted_by'        => 4,
                'created_at'         => now()->subDays(3)->subHours(6),
                'updated_at'         => now()->subDays(3)->subHours(6),
            ],

            // =========================================================================
            // MINGGU W-1 (Pekan Lalu / 5-9 Hari Lalu)
            // =========================================================================
            [
                'id'                 => 6,
                'product_id'         => 1, // Selada Romaine
                'source_type'        => 'PETANI_BINAAN',
                'supplier_name'      => 'Kelompok Tani Hidroponik Panundaan',
                'batch_date'         => now()->subDays(6)->toDateString(),
                'initial_quantity'   => 5000.00,
                'available_quantity' => 400.00,
                'grade'              => 'Grade A',
                'qc_notes'           => 'Siklus panen hidroponik mingguan blok A & B.',
                'inputted_by'        => 4,
                'created_at'         => now()->subDays(6)->setTime(6, 30),
                'updated_at'         => now()->subDays(6)->setTime(6, 30),
            ],
            [
                'id'                 => 7,
                'product_id'         => 2, // Brokoli Super
                'source_type'        => 'BUFFER_LUAR',
                'supplier_name'      => 'Mitra Tani Sejahtera Parongpong',
                'batch_date'         => now()->subDays(7)->toDateString(),
                'initial_quantity'   => 2500.00,
                'available_quantity' => 200.00,
                'grade'              => 'Grade A',
                'qc_notes'           => 'Pasokan buffer mitigasi lonjakan order catering B2B.',
                'inputted_by'        => 4,
                'created_at'         => now()->subDays(7)->setTime(7, 0),
                'updated_at'         => now()->subDays(7)->setTime(7, 0),
            ],
            [
                'id'                 => 8,
                'product_id'         => 3, // Tomat Beef
                'source_type'        => 'PETANI_BINAAN',
                'supplier_name'      => 'Sentra Greenhouse Garut Tarogong',
                'batch_date'         => now()->subDays(8)->toDateString(),
                'initial_quantity'   => 4200.00,
                'available_quantity' => 350.00,
                'grade'              => 'Grade A',
                'qc_notes'           => 'Panen berkala greenhouse modul C, sortir tanpa cacat.',
                'inputted_by'        => 5,
                'created_at'         => now()->subDays(8)->setTime(6, 45),
                'updated_at'         => now()->subDays(8)->setTime(6, 45),
            ],
            [
                'id'                 => 9,
                'product_id'         => 5, // Kol Putih
                'source_type'        => 'PETANI_BINAAN',
                'supplier_name'      => 'Gapoktan Organik Pangalengan',
                'batch_date'         => now()->subDays(9)->toDateString(),
                'initial_quantity'   => 4500.00,
                'available_quantity' => 600.00,
                'grade'              => 'Grade A',
                'qc_notes'           => 'Kol krop padat tanpa semprotan kimiawi.',
                'inputted_by'        => 4,
                'created_at'         => now()->subDays(9)->setTime(8, 0),
                'updated_at'         => now()->subDays(9)->setTime(8, 0),
            ],

            // =========================================================================
            // MINGGU W-2 (12-16 Hari Lalu)
            // =========================================================================
            [
                'id'                 => 10,
                'product_id'         => 1, // Selada Romaine
                'source_type'        => 'PETANI_BINAAN',
                'supplier_name'      => 'Kelompok Tani Hidroponik Panundaan',
                'batch_date'         => now()->subDays(13)->toDateString(),
                'initial_quantity'   => 5200.00,
                'available_quantity' => 0.00,
                'grade'              => 'Grade A',
                'qc_notes'           => 'Alokasi penuh untuk distribusi ritel AEON dan Aerofood.',
                'inputted_by'        => 4,
                'created_at'         => now()->subDays(13)->setTime(6, 15),
                'updated_at'         => now()->subDays(13)->setTime(6, 15),
            ],
            [
                'id'                 => 11,
                'product_id'         => 3, // Tomat Beef
                'source_type'        => 'BUFFER_LUAR',
                'supplier_name'      => 'Mitra Cadangan Cikajang Garut',
                'batch_date'         => now()->subDays(14)->toDateString(),
                'initial_quantity'   => 3100.00,
                'available_quantity' => 0.00,
                'grade'              => 'Grade A',
                'qc_notes'           => 'Pasokan penyangga permintaan hotel Grand Pangrango.',
                'inputted_by'        => 5,
                'created_at'         => now()->subDays(14)->setTime(7, 30),
                'updated_at'         => now()->subDays(14)->setTime(7, 30),
            ],
            [
                'id'                 => 12,
                'product_id'         => 4, // Stroberi Ciwidey
                'source_type'        => 'PETANI_BINAAN',
                'supplier_name'      => 'Kelompok Tani Stroberi Alamendah',
                'batch_date'         => now()->subDays(15)->toDateString(),
                'initial_quantity'   => 1500.00,
                'available_quantity' => 0.00,
                'grade'              => 'Grade A',
                'qc_notes'           => 'Petik pagi hari, pendinginan langsung di reefer room.',
                'inputted_by'        => 5,
                'created_at'         => now()->subDays(15)->setTime(6, 0),
                'updated_at'         => now()->subDays(15)->setTime(6, 0),
            ],

            // =========================================================================
            // MINGGU W-3 (19-23 Hari Lalu)
            // =========================================================================
            [
                'id'                 => 13,
                'product_id'         => 2, // Brokoli Super
                'source_type'        => 'PETANI_BINAAN',
                'supplier_name'      => 'Gapoktan Sayur Lembang Dataran Tinggi',
                'batch_date'         => now()->subDays(20)->toDateString(),
                'initial_quantity'   => 3200.00,
                'available_quantity' => 0.00,
                'grade'              => 'Grade A',
                'qc_notes'           => 'Penyaluran pesanan rantai resto Boga Rasa.',
                'inputted_by'        => 4,
                'created_at'         => now()->subDays(20)->setTime(7, 15),
                'updated_at'         => now()->subDays(20)->setTime(7, 15),
            ],
            [
                'id'                 => 14,
                'product_id'         => 5, // Kol Putih
                'source_type'        => 'PETANI_BINAAN',
                'supplier_name'      => 'Gapoktan Organik Pangalengan',
                'batch_date'         => now()->subDays(21)->toDateString(),
                'initial_quantity'   => 4800.00,
                'available_quantity' => 0.00,
                'grade'              => 'Grade A',
                'qc_notes'           => 'Distribusi grosir ritel modern Segar Makmur.',
                'inputted_by'        => 4,
                'created_at'         => now()->subDays(21)->setTime(6, 45),
                'updated_at'         => now()->subDays(21)->setTime(6, 45),
            ],

            // =========================================================================
            // MINGGU W-4 (26-30 Hari Lalu)
            // =========================================================================
            [
                'id'                 => 15,
                'product_id'         => 1, // Selada Romaine
                'source_type'        => 'PETANI_BINAAN',
                'supplier_name'      => 'Kelompok Tani Hidroponik Panundaan',
                'batch_date'         => now()->subDays(27)->toDateString(),
                'initial_quantity'   => 6200.00,
                'available_quantity' => 0.00,
                'grade'              => 'Grade A',
                'qc_notes'           => 'Siklus panen raya bulanan panundaan blok 1-4.',
                'inputted_by'        => 4,
                'created_at'         => now()->subDays(27)->setTime(6, 0),
                'updated_at'         => now()->subDays(27)->setTime(6, 0),
            ],
            [
                'id'                 => 16,
                'product_id'         => 3, // Tomat Beef
                'source_type'        => 'BUFFER_LUAR',
                'supplier_name'      => 'Mitra Cadangan Cikajang Garut',
                'batch_date'         => now()->subDays(28)->toDateString(),
                'initial_quantity'   => 2800.00,
                'available_quantity' => 0.00,
                'grade'              => 'Grade A',
                'qc_notes'           => 'Buffer stok masuk STA Lembang.',
                'inputted_by'        => 5,
                'created_at'         => now()->subDays(28)->setTime(7, 0),
                'updated_at'         => now()->subDays(28)->setTime(7, 0),
            ],
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
