<?php

namespace App\Filament\Resources\MainProductResource\Pages;

use App\Filament\Resources\MainProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMainProduct extends EditRecord
{
    protected static string $resource = MainProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
