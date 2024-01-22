<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;


     public function mutateFormDataBeforeCreate(array $data): array
    {
        $productExist = Product::where('main_product_id', $data['main_product_id'])->where('branch_id', $data['branch_id'])->exists();
        if ($productExist) {
            Notification::make()
                ->danger()
                ->color('danger')
                ->title('Failed to create this product.')
                ->body('This product is already registered to this branch.')
                ->send();

            $this->halt();
        }

        return $data;
    }
}
