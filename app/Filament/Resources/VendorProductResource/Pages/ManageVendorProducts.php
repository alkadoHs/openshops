<?php

namespace App\Filament\Resources\VendorProductResource\Pages;

use App\Filament\Resources\VendorProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageVendorProducts extends ManageRecords
{
    protected static string $resource = VendorProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
