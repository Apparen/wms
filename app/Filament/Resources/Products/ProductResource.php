<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Tables\Actions\EditAction as ActionsEditAction;
use Filament\Tables\Actions\ViewAction as ActionsViewAction;
use Filament\Tables\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction as ActionsDeleteBulkAction;
use Filament\Tables\Filters\SelectFilter as FiltersSelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Fillament\Tables\Filters\SelectFilter;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'product';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('sku')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),
                TextInput::make('barcode')  // ← Add this
                    ->label('Barcode')
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->helperText('Scan or enter product barcode. Must be unique per product.'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->nullable()
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0),
                TextInput::make('cost')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0),
                Select::make('unit')
                    ->options([
                        'pcs' => 'Pieces',
                        'kg' => 'Kilogram',
                        'box' => 'Box',
                        'pack' => 'Pack',
                        'liter' => 'Liter',
                    ])
                    ->required()
                    ->default('pcs'),
                TextInput::make('min_stock_level')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ->recordAction(null)
            ->recordUrl(null) // Disable default row click action
            ->columns([
                TextColumn::make('sku')
                    ->searchable()
                    ->extraAttributes([
                        'style' => 'user-select:text',
                        'onclick' => 'event.stopPropagation()',
                    ])
                    ->sortable(),
                TextColumn::make('barcode')  // ← Add this
                    ->label('Barcode')
                    ->searchable()
                    ->extraAttributes([
                        'style' => 'user-select:text',
                        'onclick' => 'event.stopPropagation()',
                    ])
                    ->toggleable(),
                TextColumn::make('name')
                    ->searchable()
                    ->extraAttributes([
                        'style' => 'user-select:text',
                        'onclick' => 'event.stopPropagation()',
                    ])
                    ->sortable(),
                TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('unit')
                    ->badge(),
                TextColumn::make('current_stock')
                    ->label('Total Stock')
                    ->getStateUsing(fn($record) => $record->current_stock . ' ' . $record->unit)
                    ->sortable()
                    ->badge()
                    ->color(fn($record): string => match (true) {
                        $record->current_stock <= 0 => 'danger',
                        $record->current_stock <= $record->min_stock_level => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('stock_status')
                    ->badge()
                    ->color(fn($record) => $record->stock_status_color)
                    ->getStateUsing(fn($record) => $record->stock_status),
                TextColumn::make('min_stock_level')
                    ->label('Min Stock')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                FiltersSelectFilter::make('stock_status')
                    ->label('Stock Status')
                    ->options([
                        'out' => 'Out of Stock',
                        'low' => 'Low Stock',
                        'in' => 'In Stock',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!$data['value']) return $query;

                        if ($data['value'] === 'out') {
                            return $query->whereRaw('(SELECT SUM(current_stock) FROM product_warehouse WHERE product_id = products.id) <= 0');
                        }
                        if ($data['value'] === 'low') {
                            return $query->whereRaw('(SELECT SUM(current_stock) FROM product_warehouse WHERE product_id = products.id) > 0')
                                ->whereRaw('(SELECT SUM(current_stock) FROM product_warehouse WHERE product_id = products.id) <= min_stock_level');
                        }
                        if ($data['value'] === 'in') {
                            return $query->whereRaw('(SELECT SUM(current_stock) FROM product_warehouse WHERE product_id = products.id) > min_stock_level');
                        }
                        return $query;
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
