<?php

namespace App\Filament\Resources\CreditOrderResource\Pages;

use App\Filament\Resources\CreditOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCreditOrder extends EditRecord
{
    protected static string $resource = CreditOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
