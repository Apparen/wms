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
        // Get all products and filter in PHP (simpler, less SQL complexity)
        $allProducts = Product::with(['warehouses'])->get();

        $lowStockProducts = $allProducts->filter(function ($product) {
            // Check if any warehouse has low stock (but not zero)
            return $product->warehouses->contains(function ($warehouse) use ($product) {
                return $warehouse->pivot->current_stock > 0
                    && $warehouse->pivot->current_stock <= $product->min_stock_level;
            });
        })->values();

        return $table
            ->query(Product::query()->whereIn('id', $lowStockProducts->pluck('id')))
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),

                Tables\Columns\TextColumn::make('low_stock_warehouses')
                    ->label('Low In Location')
                    ->badge()
                    ->color('danger')
                    ->getStateUsing(function ($record) {
                        $lowWarehouses = $record->warehouses->filter(function ($warehouse) use ($record) {
                            return $warehouse->pivot->current_stock > 0
                                && $warehouse->pivot->current_stock <= $record->min_stock_level;
                        });

                        return $lowWarehouses->pluck('name')->implode(', ');
                    }),

                Tables\Columns\TextColumn::make('lowest_stock')
                    ->label('Current Stock')
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(function ($record) {
                        $lowWarehouses = $record->warehouses->filter(function ($warehouse) use ($record) {
                            return $warehouse->pivot->current_stock > 0
                                && $warehouse->pivot->current_stock <= $record->min_stock_level;
                        });

                        $lowestStock = $lowWarehouses->min(fn($w) => $w->pivot->current_stock);
                        return $lowestStock . ' ' . $record->unit;
                    }),

                Tables\Columns\TextColumn::make('min_stock_level')
                    ->label('Min Required')
                    ->getStateUsing(fn($record) => $record->min_stock_level . ' ' . $record->unit),
            ])
            ->heading('⚠️ Low Stock Products')
            ->emptyStateHeading('No low stock products')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->emptyStateDescription('All products are well stocked!');
    }
}
