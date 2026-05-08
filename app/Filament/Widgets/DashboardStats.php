<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Warehouse;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class DashboardStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $stats = Cache::remember('dashboard_stats', 300, function () {
            $products = Product::with('warehouses')->get();
            $totalStockValue = 0;
            $totalUnits = 0;
            foreach ($products as $product) {
                $stock = $product->current_stock;
                $totalUnits += $stock;
                $totalStockValue += $stock * $product->price;
            }
            return [
                'total_products' => Product::count(),
                'total_warehouses' => Warehouse::count(),
                'total_stock_value' => $totalStockValue,
                'total_units' => $totalUnits,
            ];
        });
        // Calculate total stock value
        $products = Product::all();
        $totalStockValue = 0;
        $totalUnits = 0;

        foreach ($products as $product) {
            $stock = $product->current_stock;
            $totalUnits += $stock;
            $totalStockValue += $stock * $product->price;
        }

        return [
            Stat::make('Total Products', Product::count())
                ->description('Active products in system')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 8, 9]),

            Stat::make('Total Warehouses', Warehouse::count())
                ->description('Storage locations')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('success'),

            Stat::make('Total Stock Value', 'Rp ' . number_format($totalStockValue, 0, ',', '.'))
                ->description('Inventory value')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            Stat::make('Total Units', number_format($totalUnits))
                ->description('Items in stock')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info'),
        ];
    }
}
