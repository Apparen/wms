<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Services\StockService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStockMovement extends CreateRecord
{
    protected static string $resource = StockMovementResource::class;
    // This runs when the form is being built
    protected function afterFill(): void
    {
        // Check if product_id is in the URL
        if (request()->has('product_id')) {
            $productId = request()->get('product_id');

            // Set the product_id field value
            $this->data['product_id'] = $productId;

            // Also update the form
            $this->form->fill([
                'product_id' => $productId,
            ]);

            // Show notification
            $product = \App\Models\Product::find($productId);
            if ($product) {
                Notification::make()
                    ->info()
                    ->title('Product Selected')
                    ->body("Product '{$product->name}' has been automatically selected from barcode scan.")
                    ->send();
            }
        }
    }
    /**
     * Handle record creation using StockService
     */
    protected function handleRecordCreation(array $data): Model
    {
        // Choose the right service method based on type
        if ($data['type'] === 'in') {
            $result = StockService::add(
                $data['product_id'],
                $data['warehouse_id'],
                $data['quantity'],
                $data['reference_number'] ?? null,
                $data['notes'] ?? null
            );
        } else {
            $result = StockService::remove(
                $data['product_id'],
                $data['warehouse_id'],
                $data['quantity'],
                $data['reference_number'] ?? null,
                $data['notes'] ?? null
            );
        }

        // Check if operation was successful
        if (!$result['success']) {
            Notification::make()
                ->title('Stock Operation Failed')
                ->body($result['message'])
                ->danger()
                ->send();

            // Stop the creation process
            $this->halt();
        }

        // Show success notification
        Notification::make()
            ->title('Success!')
            ->body($result['message'] . " New stock: " . $result['new_stock'] . " units")
            ->success()
            ->send();

        // Return the already-created movement record
        return $result['movement'];
    }

    /**
     * Prevent Filament from creating another record after handleRecordCreation
     */
    protected function afterCreate(): void
    {
        // Do nothing - record already created by StockService
    }

    /**
     * Where to redirect after successful creation
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
