<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\CreditOrder;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $totalPrice = $this->record->orderItems()->get()->reduce(fn ($total, $item) => $total + ($item->price * $item->quantity), 0);

        //check if order exists in creditOrders table
        $creditOrder = CreditOrder::where('order_id', $this->record->id)->first();
        if ($creditOrder) {
            //if order exists in creditOrders table, check if paid is greater than or equal to total price
            if ($this->record->paid >= $totalPrice) {
                //if paid is greater than or equal to total price, delete order from creditOrders table
                $creditOrder->delete();
            }
        } elseif ($this->record->paid < $totalPrice) {
            //if paid is less than total price, add order to creditOrders table
            CreditOrder::create([
                'order_id' => $this->record->id,
            ]);
        }
        
    }
}
