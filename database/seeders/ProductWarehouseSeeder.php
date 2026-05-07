<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductWarehouseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('product_warehouse')->insert([
            [
                'warehouse_id' => 1,
                'product_id' => 1,
                'current_stock' => 90,
            ],
            [
                'warehouse_id' => 3,
                'product_id' => 1,
                'current_stock' => 18,
            ],
        ]);
    }
}
