<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\StockMovement;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;

class ManageStock extends Page
{
    protected static ?string $navigationLabel = 'Manage Stock';
    protected static ?string $title = 'Warehouse Management Dashboard';
    // protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.manage-stock';

    // Get quick stats for each module
    public function getStats()
    {
        return [
            'products' => [
                'count' => Product::count(),
                'low_stock' => Product::all()->filter(fn($p) => $p->stock_status === 'Low Stock')->count(),
                'out_of_stock' => Product::all()->filter(fn($p) => $p->stock_status === 'Out of Stock')->count(),
            ],
            'warehouses' => [
                'count' => Warehouse::count(),
                'active' => Warehouse::where('is_active', true)->count(),
            ],
            'stock_movements' => [
                'count' => StockMovement::count(),
                'today' => StockMovement::whereDate('created_at', today())->count(),
                'month' => StockMovement::whereMonth('created_at', now()->month)->count(),
            ],
        ];
    }
}
