<?php

namespace App\Filament\Resources\VendorTransferResource\Pages;

use App\Filament\Resources\VendorTransferResource;
use App\Models\Product;
use App\Models\VendorProduct;
use App\Models\VendorTransfer;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageVendorTransfers extends ManageRecords
{
    protected static string $resource = VendorTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->before(function (CreateAction $action,  $record, array $data) {
                    $product = Product::where('id', $data['product_id'])->first();

                    $vendorTransfer = VendorTransfer::where('product_id', $data['product_id'])
                                                    ->where('status', '!=', 'pending')
                                                    ->where('user_id', $data['user_id'])
                                                    ->first();

                    $pendingVendorTransfer = VendorTransfer::where('product_id', $data['product_id'])
                                                    ->where('status', 'pending')
                                                    ->where('user_id', $data['user_id'])
                                                    ->first();

                    if ($product->stock < $data['stock']) {
                        Notification::make()
                            ->danger()
                            ->title('Stock is not enough!')
                            ->body("Stock available is $product->stock.")
                            ->send();
                    
                        $action->halt();
                    } elseif($vendorTransfer) {
                        $vendorTransfer->update(['status' => 'pending', 'stock' => $data['stock']]);
                        
                        Notification::make()
                            ->success()
                            ->title('Successfully transfered!')
                            ->body('Product transfered to vendor successfully.')
                            ->send();
                    
                        $action->cancel();
                    } elseif($pendingVendorTransfer) {
                        $pendingVendorTransfer->increment('stock', $data['stock']);
                        
                        Notification::make()
                            ->success()
                            ->title('Successfully transfered!')
                            ->body('Product transfered to vendor successfully.')
                            ->send();
                    
                        $action->cancel();
                    }
                    
                    $product->decrement('stock', $data['stock']);
                }),
        ];
    }
}
