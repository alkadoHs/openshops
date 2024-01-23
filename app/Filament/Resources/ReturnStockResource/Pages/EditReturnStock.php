<?php

namespace App\Filament\Resources\ReturnStockResource\Pages;

use App\Filament\Resources\ReturnStockResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReturnStock extends EditRecord
{
    protected static string $resource = ReturnStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
