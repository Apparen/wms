<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardStats;
use App\Filament\Widgets\LowStockAlert;
use App\Filament\Widgets\RecentMovements;
use App\Filament\Widgets\TopProducts;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\StockMovement;
use App\Services\StockService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;

class Dashboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Stock Dashboard';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.dashboard';

    public function getStats(): array
    {
        $products = Product::all();
        $totalStockValue = 0;
        $lowStockCount = 0;
        $outOfStockCount = 0;
        $totalUnits = 0;

        foreach ($products as $product) {
            $stock = $product->current_stock;
            $totalUnits += $stock;
            $totalStockValue += $stock * $product->price;

            if ($stock <= 0) {
                $outOfStockCount++;
            } elseif ($stock <= $product->min_stock_level) {
                $lowStockCount++;
            }
        }

        return [
            'total_products' => $products->count(),
            'total_warehouses' => Warehouse::count(),
            'total_stock_value' => $totalStockValue,
            'total_units' => $totalUnits,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'recent_movements' => StockMovement::with(['product', 'warehouse', 'creator'])
                ->latest()
                ->take(10)
                ->get(),
            'top_products' => Product::with('warehouses')
                ->get()
                ->sortByDesc(fn($p) => $p->current_stock)
                ->take(5),
            'low_stock_products' => Product::get()
                ->filter(fn($p) => $p->stock_status === 'Low Stock')
                ->take(10),
        ];
    }
    // Override the widget columns - simpler approach
    public function getColumns(): int | string | array
    {
        return 2;
    }
    protected function getHeaderWidgets(): array
    {
        return [
            DashboardStats::class,
        ];
    }
    protected function getFooterWidgets(): array
    {
        return [
            TopProducts::class,
            LowStockAlert::class,
            RecentMovements::class,
        ];
    }
}
