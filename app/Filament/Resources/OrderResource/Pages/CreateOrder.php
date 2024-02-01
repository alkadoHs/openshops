<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\CreditOrder;
use App\Models\Order;
use Filament\Actions;
use Filament\Actions\Modal\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;


    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Order Created';
    }


    protected function afterCreate(): void
    {
        $totalPrice = $this->record->orderItems()->get()->reduce(fn ($total, $item) => $total + ($item->price * $item->quantity), 0);

        if($this->record->paid < $totalPrice) {
            //add order to the creditOrders table
            CreditOrder::create([
                'order_id' => $this->record->id,
            ]);
        }
    }   

}


