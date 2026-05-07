<?php

namespace App\Console\Commands;

use App\Services\StockService;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Console\Command;

class TestStockService extends Command
{
    protected $signature = 'stock:test';
    protected $description = 'Test the StockService functionality';

    public function handle()
    {
        $this->info('🧪 Testing StockService');
        $this->newLine();

        // Get or create product
        $product = Product::first();
        if (!$product) {
            $product = Product::create([
                'sku' => 'TEST001',
                'name' => 'Test Product',
                'price' => 100000,
                'cost' => 80000,
                'min_stock_level' => 10,
                'unit' => 'pcs',
            ]);
            $this->info('✅ Created test product: ' . $product->name);
        }

        // Get or create warehouse
        $warehouse = Warehouse::first();
        if (!$warehouse) {
            $warehouse = Warehouse::create([
                'name' => 'Test Warehouse',
                'location' => 'Jakarta',
                'is_active' => true,
            ]);
            $this->info('✅ Created test warehouse: ' . $warehouse->name);
        }

        $this->newLine();
        $this->info('📦 Initial stock: ' . StockService::getCurrentStock($product->id, $warehouse->id));

        // Test 1: Add stock
        $this->info("\n📥 Adding 50 units...");
        $result = StockService::add($product->id, $warehouse->id, 50, 'TEST-001', 'Initial stock');

        if ($result['success']) {
            $this->info('✅ ' . $result['message']);
            $this->info('   New stock: ' . $result['new_stock']);
        } else {
            $this->error('❌ Failed: ' . $result['message']);
        }

        // Test 2: Remove stock
        $this->info("\n📤 Removing 20 units...");
        $result = StockService::remove($product->id, $warehouse->id, 20, 'TEST-002', 'Customer purchase');

        if ($result['success']) {
            $this->info('✅ ' . $result['message']);
            $this->info('   New stock: ' . $result['new_stock']);
        } else {
            $this->error('❌ Failed: ' . $result['message']);
        }

        // Test 3: Try to remove more than available
        $this->info("\n⚠️  Trying to remove 100 units (should fail)...");
        $result = StockService::remove($product->id, $warehouse->id, 100, 'TEST-003', 'Should fail');

        if (!$result['success']) {
            $this->info('✅ Correctly failed: ' . $result['message']);
        } else {
            $this->error('❌ Should have failed but didn\'t!');
        }

        // Final stock check
        $this->newLine();
        $this->info('📊 Final Results:');
        $this->info('   Product: ' . $product->name);
        $this->info('   Warehouse: ' . $warehouse->name);
        $this->info('   Current stock: ' . StockService::getCurrentStock($product->id, $warehouse->id));
        $this->info('   Total across all warehouses: ' . StockService::getTotalStock($product->id));

        $this->newLine();
        $this->info('🎉 Test complete!');
    }
}
