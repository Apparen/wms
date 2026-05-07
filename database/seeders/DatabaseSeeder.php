<?php

namespace Database\Seeders;

use Database\Seeders\WarehouseSeeder as SeedersWarehouseSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SeedersWarehouseSeeder::class,
            ProductSeeder::class,
            ProductWarehouseSeeder::class,
            StockMovementSeeder::class,
            SeedersWarehouseSeeder::class,
        ]);
    }
}
