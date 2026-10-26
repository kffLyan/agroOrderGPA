<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Seed the master products (5 Core Commodities GPA).
     */
    public function run(): void
    {
        DB::table('products')->truncate();

        DB::table('products')->insert([
            [
                'id'            => 1,
                'sku'           => 'GPA-VGT-RMN01',
                'name'          => 'Selada Romaine Hydroponic Super',
                'unit'          => 'kg',
                'grade'         => 'Grade A',
                'base_price'    => 14500.00,
                'minimum_order' => 10.00,
                'image_url'     => 'products/selada.jpg',
                'description'   => 'Selada romaine segar renyah hidroponik dataran tinggi Ciwidey, bebas residu pestisida.',
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'id'            => 2,
                'sku'           => 'GPA-VGT-BRK02',
                'name'          => 'Brokoli Super Lembang',
                'unit'          => 'kg',
                'grade'         => 'Grade A',
                'base_price'    => 22000.00,
                'minimum_order' => 5.00,
                'image_url'     => 'products/brokoli.jpg',
                'description'   => 'Kuntum brokoli hijau padat tanpa ulat, pemetikan pagi hari dari kebun binaan Lembang.',
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'id'            => 3,
                'sku'           => 'GPA-FRT-TMT03',
                'name'          => 'Tomat Beef Garut & Sayur',
                'unit'          => 'kg',
                'grade'         => 'Grade A',
                'base_price'    => 16000.00,
                'minimum_order' => 5.00,
                'image_url'     => 'products/tomat.jpg',
                'description'   => 'Tomat beef berdaging padat dan tebal dari sentra greenhouse Garut.',
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'id'            => 4,
                'sku'           => 'GPA-BER-STR04',
                'name'          => 'Stroberi Ciwidey Sweet Grade',
                'unit'          => 'pack',
                'grade'         => 'Grade A',
                'base_price'    => 25000.00,
                'minimum_order' => 2.00,
                'image_url'     => 'products/stroberi.jpg',
                'description'   => 'Stroberi manis petik langsung dari petani lokal Ciwidey kemasan mika pack 500 gram higienis.',
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'id'            => 5,
                'sku'           => 'GPA-VGT-KOL05',
                'name'          => 'Kol Putih Organik Pangalengan',
                'unit'          => 'kg',
                'grade'         => 'Grade A',
                'base_price'    => 8000.00,
                'minimum_order' => 10.00,
                'image_url'     => 'products/kol.jpg',
                'description'   => 'Kol putih krop bulat padat berserat halus standar pasokan supermarket.',
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);
    }
}
