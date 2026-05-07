<?php

namespace App\Filament\Resources\StockMovements;

use App\Filament\Resources\StockMovements\Pages\CreateStockMovement;
use App\Filament\Resources\StockMovements\Pages\EditStockMovement;
use App\Filament\Resources\StockMovements\Pages\ListStockMovements;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\StockService;
use BackedEnum;
use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteAction as ActionsDeleteAction;
use Filament\Actions\DeleteBulkAction as ActionsDeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction as ActionsViewAction;
use Filament\Forms\Form;  // ✅ Fixed
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;  // ✅ Fixed
use Filament\Tables\Actions\DeleteAction;  // ✅ Fixed
use Filament\Tables\Actions\DeleteBulkAction;  // ✅ Fixed
use Filament\Tables\Actions\ViewAction;  // ✅ Fixed
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'stock-movement';

    public static function form(Schema $form): Schema  // ✅ Fixed signature
    {
        return $form
            ->schema([
                Hidden::make('created_by')
                    ->default(Auth::id()),

                Select::make('product_id')
                    ->label('Product')
                    // ->options(Product::pluck('name', 'id'))
                    ->options(function () {
                        return Product::all()->mapWithKeys(function ($product) {
                            $label = $product->name;
                            if ($product->sku) $label .= " [SKU: {$product->sku}]";
                            if ($product->barcode) $label .= " [BC: {$product->barcode}]";
                            return [$product->id => $label];
                        });
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->disabled(fn() => request()->has('product_id')) // Disable if came from scan
                    ->helperText(fn() => request()->has('product_id') ? 'Product pre-selected from barcode scan' : null)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $warehouseId = $get('warehouse_id');
                        if ($state && $warehouseId) {
                            $stock = StockService::getCurrentStock($state, $warehouseId);
                            // Store in session or use helper text
                        }
                    }),

                Select::make('warehouse_id')
                    ->label('Warehouse')
                    ->options(function ($get) {
                        $productId = $get('product_id');
                        $type = $get('type');

                        $query = Warehouse::where('is_active', true);

                        // If performing a 'Stock Out', only show warehouses that have the product
                        if ($type === 'out' && $productId) {
                            $query->whereHas('products', function ($q) use ($productId) {
                                $q->where('product_id', $productId)
                                    ->where('current_stock', '>', 0); // Optional: only if they have stock
                            });
                        }
                        return $query->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get) {
                        $productId = $get('product_id');
                        if ($state && $productId) {
                            $stock = StockService::getCurrentStock($productId, $state);
                        }
                    }),

                Select::make('type')
                    ->label('Movement Type')
                    ->options([
                        'in' => '📥 Stock In (Add)',
                        'out' => '📤 Stock Out (Remove)',
                    ])
                    ->required()
                    ->native(false),

                TextInput::make('quantity')
                    ->label('Quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->helperText(function ($get) {
                        $type = $get('type');
                        $productId = $get('product_id');
                        $warehouseId = $get('warehouse_id');

                        if (!$productId || !$warehouseId) {
                            return "⚠️ Please select both product and warehouse first";
                        }

                        // Check if product exists in this warehouse
                        $exists = \Illuminate\Support\Facades\DB::table('product_warehouse')
                            ->where('product_id', $productId)
                            ->where('warehouse_id', $warehouseId)
                            ->exists();

                        $currentStock = StockService::getCurrentStock($productId, $warehouseId);

                        if ($type === 'out') {
                            if (!$exists) {
                                return "❌ This product is NOT available in this warehouse. Please select a different warehouse or add stock first.";
                            }

                            if ($currentStock == 0) {
                                return "❌ This product has ZERO stock in this warehouse. Please add stock first or select a different warehouse.";
                            }

                            return "✅ Available stock in this warehouse: {$currentStock} units. You can remove up to {$currentStock} units.";
                        }

                        // For Stock IN
                        if (!$exists) {
                            return "📦 This product is not yet in this warehouse. Stock IN will add it to this warehouse.";
                        }

                        return "📊 Current stock in this warehouse: {$currentStock} units. Stock IN will increase this.";
                    }),
                TextInput::make('reference_number')
                    ->label('Reference Number (PO/Invoice #)')
                    ->maxLength(255)
                    ->helperText('Optional: Purchase Order, Invoice, or Transfer number'),

                Textarea::make('notes')
                    ->label('Notes')
                    ->columnSpanFull()
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('warehouse.name')
                    ->label('Warehouse')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'in' => '📥 Stock In',
                        'out' => '📤 Stock Out',
                    }),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reference_number')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ActionsViewAction::make(),

                ActionsDeleteAction::make()
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Stock Movement')
                    ->modalDescription(function ($record) {
                        $action = $record->type === 'in' ? 'decrease' : 'increase';
                        $amount = $record->quantity;
                        return "⚠️ **Warning: Stock will be reversed!**\n\n" .
                            "Deleting this movement will:\n" .
                            "- {$action} stock by {$amount} units\n" .
                            "- Product: {$record->product->name}\n" .
                            "- Warehouse: {$record->warehouse->name}\n\n" .
                            "This action cannot be undone.";
                    })
                    ->modalSubmitActionLabel('Yes, Delete & Reverse Stock')
                    ->action(function ($record) {
                        try {
                            $record->delete();

                            Notification::make()
                                ->success()
                                ->title('Movement Deleted')
                                ->body('Stock has been adjusted and movement removed.')
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot Delete')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockMovements::route('/'),
            'create' => CreateStockMovement::route('/create'),
            'edit' => EditStockMovement::route('/{record}/edit'),
            'view' => Pages\ViewStockMovement::route('/{record}'),
        ];
    }
}
