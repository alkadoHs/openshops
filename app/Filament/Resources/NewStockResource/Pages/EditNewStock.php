<?php

namespace App\Filament\Resources\NewStockResource\Pages;

use App\Filament\Resources\NewStockResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNewStock extends EditRecord
{
    protected static string $resource = NewStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
