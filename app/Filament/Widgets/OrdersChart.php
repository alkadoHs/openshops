<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Sales';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        //monthly orders
        $data = Trend::model(Order::class)
                    ->between(
                        start: now()->subYear(),
                        end: now()
                    )
                    ->perMonth()
                    ->sum('paid');
        return [
        'datasets' => [
            [
                'label' => 'Orders',
                'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
            ],
        ],
        'labels' => $data->map(fn (TrendValue $value) => $value->date),
    ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
