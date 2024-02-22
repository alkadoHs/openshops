<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;

class BranchSalesChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Sales/Branch';

    protected static ?int $sort = 9;

    protected function getData(): array
    {
        //monthly sales per branch
        $data = OrderItem::with('product.branch')->whereMonth('created_at', today())->get()->groupBy('product.branch.name')->map(function ($item) {
            return $item->reduce(function ($carry, $orderItem) {
                return $carry + ($orderItem->price * $orderItem->quantity);
            }, 0);
        });

        return [
            'labels' => $data->keys()->toArray(),
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data' => $data->values()->toArray(),
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public static function canView(): bool
    {
        return auth()->user()->role == 'admin' || auth()->user()->role == 'superuser';
    }
}
