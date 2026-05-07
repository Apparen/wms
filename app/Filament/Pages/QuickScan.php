<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Services\StockService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class QuickScan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-camera';
    protected static ?string $navigationLabel = 'Quick Scan';
    protected static ?string $title = 'Barcode Scanner';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.quick-scan';

    public $barcode = '';
    public $product = null;
    public $quantity = 1;
    public $type = 'in';

    public function scan()
    {
        $this->product = Product::where('barcode', $this->barcode)
            ->orWhere('sku', $this->barcode)  // Also allow SKU search
            ->first();

        if (!$this->product) {
            Notification::make()
                ->title('Product Not Found')
                ->body("No product found with barcode or SKU: {$this->barcode}")
                ->danger()
                ->send();

            $this->reset(['barcode', 'product', 'quantity']);
            $this->focusBarcode();
        } else {
            Notification::make()
                ->title('Product Found!')
                ->body("{$this->product->name} | Current stock: {$this->product->current_stock} {$this->product->unit}")
                ->success()
                ->duration(3000)
                ->send();
        }

        $this->barcode = '';
    }

    public function addStock()
    {
        if (!$this->product) {
            Notification::make()
                ->title('No Product Selected')
                ->body('Please scan a barcode first')
                ->warning()
                ->send();
            return;
        }

        // Redirect to stock movement create with pre-filled product
        return redirect()->route('filament.admin.resources.stock-movements.create', [
            'product_id' => $this->product->id,
            // 'type' => $this->type
        ]);
    }

    public function viewProduct()
    {
        if (!$this->product) {
            Notification::make()
                ->title('No Product Selected')
                ->body('Please scan a barcode first')
                ->warning()
                ->send();
            return;
        }

        return redirect()->route('filament.admin.resources.products.edit', [$this->product]);
    }

    protected function focusBarcode()
    {
        $this->dispatch('focus-barcode');
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('barcode')
                ->label('Scan Barcode or Enter SKU')
                ->placeholder('Position cursor here and scan barcode...')
                ->required()
                ->reactive()
                ->afterStateUpdated(fn() => $this->scan())
                ->autofocus()
                ->helperText('Scan a barcode or type SKU, then press Enter'),
        ];
    }
}
