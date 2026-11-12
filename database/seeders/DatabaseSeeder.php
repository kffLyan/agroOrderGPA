<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    // Menjalankan seeder master pengguna, komoditas, kontrak, panen, dan transaksi secara berurutan
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            ContractSeeder::class,
            HarvestBatchSeeder::class,
            OrderSeeder::class,
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
