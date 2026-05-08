<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class StockService
{
    /**
     * Add stock to a product in a warehouse
     */
    public static function add(
        $productId,
        $warehouseId,
        $quantity,
        $referenceNumber = null,
        $notes = null
    ) {
        return self::update($productId, $warehouseId, $quantity, 'in', $referenceNumber, $notes);
    }

    /**
     * Remove stock from a product in a warehouse
     */
    public static function remove(
        $productId,
        $warehouseId,
        $quantity,
        $referenceNumber = null,
        $notes = null
    ) {
        return self::update($productId, $warehouseId, $quantity, 'out', $referenceNumber, $notes);
    }

    /**
     * Main stock update logic (private - only called internally)
     */
    private static function update(
        $productId,
        $warehouseId,
        $quantity,
        $type,
        $referenceNumber = null,
        $notes = null
    ) {
        // Start a database transaction
        DB::beginTransaction();

        try {
            // Get current stock from pivot table
            $pivot = DB::table('product_warehouse')
                ->where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->first();

            $currentStock = $pivot ? $pivot->current_stock : 0;

            // Calculate new stock
            if ($type === 'in') {
                $newStock = $currentStock + $quantity;
            } else {
                $newStock = $currentStock - $quantity;

                // IMPORTANT: Check if we have enough stock
                if ($newStock < 0) {
                    throw new \Exception(
                        "Insufficient stock! Current: {$currentStock}, Attempting to remove: {$quantity}"
                    );
                }
            }

            // Update or create pivot record
            if ($pivot) {
                DB::table('product_warehouse')
                    ->where('product_id', $productId)
                    ->where('warehouse_id', $warehouseId)
                    ->update([
                        'current_stock' => $newStock,
                        'updated_at' => now(),
                    ]);
                Cache::forget("product_stock_{$productId}");
            } else {
                // Product not in this warehouse yet - attach it
                DB::table('product_warehouse')->insert([
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'current_stock' => $type === 'in' ? $quantity : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Create movement record for audit trail
            $movement = StockMovement::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'type' => $type,
                'quantity' => $quantity,
                'reference_number' => $referenceNumber,
                'notes' => $notes,
                'created_by' => Auth::id() ?? 1, // Use 1 as fallback if no auth
            ]);

            // Commit the transaction
            DB::commit();

            return [
                'success' => true,
                'movement' => $movement,
                'new_stock' => $newStock,
                'message' => "Stock successfully added!" . ($type === 'in' ? 'in' : 'out')
            ];
        } catch (\Exception $e) {
            // Something went wrong - rollback everything
            DB::rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e
            ];
        }
    }

    /**
     * Get current stock for a product in a specific warehouse
     */
    public static function getCurrentStock($productId, $warehouseId)
    {
        $pivot = DB::table('product_warehouse')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        return $pivot ? $pivot->current_stock : 0;
    }

    /**
     * Get total stock for a product across all warehouses
     */
    public static function getTotalStock($productId)
    {
        return (int) DB::table('product_warehouse')
            ->where('product_id', $productId)
            ->sum('current_stock');
    }
}
