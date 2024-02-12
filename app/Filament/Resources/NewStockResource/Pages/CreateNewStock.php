<?php

namespace App\Filament\Resources\NewStockResource\Pages;

use App\Filament\Resources\NewStockResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateNewStock extends CreateRecord
{
    protected static string $resource = NewStockResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        Product::where('main_product_id', $data['main_product_id'])
            ->where('branch_id', $data['branch_id'])
            ->update([
                'stock' => \DB::raw('stock + ' . $data['stock']),
                'new_stock' => $data['stock'],
            ]);
        return static::getModel()::create($data);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'New stock added successfully!';
    }
    
}
