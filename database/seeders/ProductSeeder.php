<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'sku' => 'TSH-OVR-BLK-01',
                'barcode' => '8901234567890',
                'name' => 'Oversized Black T-Shirt',
                'description' => 'Premium heavy cotton oversized tee',
                'price' => 150000,
                'cost' => 85000,
                'min_stock_level' => 10,
                'unit' => 'pcs',
            ],
            [
                'sku' => 'HD-NAV-L-02',
                'barcode' => '8991001234510',
                'name' => 'Navy Blue Hoodie',
                'description' => 'Warm fleece hoodie with front pocket',
                'price' => 350000,
                'cost' => 210000,
                'min_stock_level' => 5,
                'unit' => 'pcs',
            ],
            [
                'sku' => 'FAB-CTN-RAW-05',
                'barcode' => '8991001234541',
                'name' => 'Raw Organic Cotton',
                'description' => 'Raw material for garment production',
                'price' => 120000,
                'cost' => 90000,
                'min_stock_level' => 50,
                'unit' => 'kg',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
