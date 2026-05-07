<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'type',
        'quantity',
        'reference_number',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];
    // Add this to reverse stock when movement is deleted
    protected static function booted()
    {
        static::deleting(function ($movement) {
            DB::beginTransaction();

            try {
                $pivot = DB::table('product_warehouse')
                    ->where('product_id', $movement->product_id)
                    ->where('warehouse_id', $movement->warehouse_id)
                    ->first();

                if (!$pivot) {
                    throw new \Exception("Product not found in warehouse");
                }

                $currentStock = $pivot->current_stock;

                if ($movement->type === 'in') {
                    // Removing a Stock IN movement: stock goes DOWN
                    $newStock = $currentStock - $movement->quantity;

                    if ($newStock < 0) {
                        throw new \Exception(
                            "Cannot delete this Stock IN movement because it would make stock negative! " .
                                "Current stock: {$currentStock}, Would become: {$newStock}"
                        );
                    }

                    DB::table('product_warehouse')
                        ->where('product_id', $movement->product_id)
                        ->where('warehouse_id', $movement->warehouse_id)
                        ->decrement('current_stock', $movement->quantity);
                } else {
                    // Removing a Stock OUT movement: stock goes UP (always safe)
                    DB::table('product_warehouse')
                        ->where('product_id', $movement->product_id)
                        ->where('warehouse_id', $movement->warehouse_id)
                        ->increment('current_stock', $movement->quantity);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        });
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
