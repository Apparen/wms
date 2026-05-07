<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditStockMovement extends EditRecord
{
    protected static string $resource = StockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Prevent editing
        Notification::make()
            ->warning()
            ->title('Editing Disabled')
            ->body('Stock movements cannot be edited to maintain inventory integrity. Please create a new adjustment if needed.')
            ->send();

        return $data;
    }
}
