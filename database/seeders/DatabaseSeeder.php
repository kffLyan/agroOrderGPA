<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
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
