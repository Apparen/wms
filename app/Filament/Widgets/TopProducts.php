<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopProducts extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU'),
                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->getStateUsing(fn($record) => $record->current_stock . ' ' . $record->unit),
                Tables\Columns\TextColumn::make('stock_value')
                    ->label('Total Value')
                    ->money('IDR')
                    ->getStateUsing(fn($record) => $record->current_stock * $record->price),
            ])
            ->defaultSort('current_stock', 'desc')
            ->heading('🏆 Top Products by Stock')
            ->paginated([5, 10, 25]);
    }
}
