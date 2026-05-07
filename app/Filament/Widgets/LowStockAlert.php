<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockAlert extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $lowStockProducts = Product::all()->filter(function ($product) {
            return $product->stock_status === 'Low Stock';
        });

        return $table
            ->query(Product::query()->whereHas('warehouses', function ($q) {
                $q->whereColumn('current_stock', '<=', 'products.min_stock_level')
                    ->where('current_stock', '>', 0);
            }))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Current Stock')
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(fn($record) => $record->current_stock . ' ' . $record->unit),
                Tables\Columns\TextColumn::make('min_stock_level')
                    ->label('Min Required')
                    ->getStateUsing(fn($record) => $record->min_stock_level . ' ' . $record->unit),
                Tables\Columns\TextColumn::make('stock_status')
                    ->badge()
                    ->color('warning'),
            ])
            ->heading('⚠️ Low Stock Products')
            ->emptyStateHeading('No low stock products')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->emptyStateDescription('All products are well stocked!');
    }
}
