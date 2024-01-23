<?php

namespace App\Filament\Resources\BranchTransferResource\Pages;

use App\Filament\Resources\BranchTransferResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBranchTransfer extends EditRecord
{
    protected static string $resource = BranchTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
