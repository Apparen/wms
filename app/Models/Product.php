<?php

namespace App\Models;

use App\Services\StockService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    //
    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'description',
        'price',
        'cost',
        'min_stock_level',
        'unit'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'min_stock_level' => 'integer',
    ];
    // public function getCurrentStockAttribute(): int
    // {
    //     return (int) DB::table('product_warehouse')
    //         ->where('product_id', $this->id)
    //         ->sum('current_stock');
    // }

    // // 🔥 Calculate stock per warehouse
    // public function getStockPerWarehouseAttribute(): array
    // {
    //     return DB::table('product_warehouse')
    //         ->join('warehouses', 'product_warehouse.warehouse_id', '=', 'warehouses.id')
    //         ->where('product_warehouse.product_id', $this->id)
    //         ->select('warehouses.name', 'product_warehouse.current_stock')
    //         ->get()
    //         ->toArray();
    // }

    // // 🔥 Automatic stock status based on actual current stock
    // public function getStockStatusAttribute(): string
    // {
    //     $stock = $this->current_stock;  // Uses the accessor above
    //     if ($stock <= 0) return 'Out of Stock';
    //     if ($stock <= $this->min_stock_level) return 'Low Stock';
    //     return 'In Stock';
    // }

    // // 🔥 Color for badge
    // public function getStockStatusColorAttribute(): string
    // {
    //     return match ($this->stock_status) {
    //         'Out of Stock' => 'danger',
    //         'Low Stock' => 'warning',
    //         'In Stock' => 'success',
    //     };
    // }
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class)
            ->withPivot('current_stock')
            ->withTimestamps();
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
    /**
     * Get total stock across all warehouses
     * This uses our StockService for consistency!
     */
    public function getCurrentStockAttribute(): int
    {
        return StockService::getTotalStock($this->id);
    }

    /**
     * Get stock status based on current stock and min level
     */
    public function getStockStatusAttribute(): string
    {
        $stock = $this->current_stock;
        if ($stock <= 0) return 'Out of Stock';
        if ($stock <= $this->min_stock_level) return 'Low Stock';
        return 'In Stock';
    }

    /**
     * Get color for stock status badge
     */
    public function getStockStatusColorAttribute(): string
    {
        return match ($this->stock_status) {
            'Out of Stock' => 'danger',
            'Low Stock' => 'warning',
            'In Stock' => 'success',
        };
    }
    protected static function booted()
    {
        static::addGlobalScope('with_current_stock', function ($query) {
            $query->addSelect([
                'current_stock' => \Illuminate\Support\Facades\DB::table('product_warehouse')
                    ->whereColumn('product_id', 'products.id')
                    ->selectRaw('COALESCE(SUM(current_stock), 0)')
                    ->limit(1)
            ]);
        });
    }
    /**
     * Get stock breakdown by warehouse
     */
    public function getStockByWarehouseAttribute(): array
    {
        $warehouses = [];
        foreach ($this->warehouses as $warehouse) {
            $warehouses[] = [
                'name' => $warehouse->name,
                'stock' => $warehouse->pivot->current_stock
            ];
        }
        return $warehouses;
    }
}
