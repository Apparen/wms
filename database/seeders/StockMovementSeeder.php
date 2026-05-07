<?php

namespace Database\Seeders;

use App\Models\StockMovement;
use Illuminate\Database\Seeder;

class StockMovementSeeder extends Seeder
{
    public function run(): void
    {
        StockMovement::create([
            'product_id' => 1,
            'warehouse_id' => 1,
            'type' => 'in',
            'quantity' => 90,
            'created_by' => 1,
        ]);

        StockMovement::create([
            'product_id' => 1,
            'warehouse_id' => 3,
            'type' => 'in',
            'quantity' => 18,
            'created_by' => 1,
        ]);
    }
}
