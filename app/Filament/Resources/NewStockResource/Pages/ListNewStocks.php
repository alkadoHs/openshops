<?php

namespace App\Filament\Resources\NewStockResource\Pages;

use App\Filament\Resources\NewStockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNewStocks extends ListRecords
{
    protected static string $resource = NewStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
