<?php

namespace App\Filament\Widgets;

use App\Models\ExpenseItem;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ExpensesChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Expenses';

    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $data = Trend::model(ExpenseItem::class)
                    ->between(
                        start: now()->subYear(),
                        end: now()
                    )
                    ->perMonth()
                    ->sum('cost');
        return [
            "datasets" => [
                [
                "label" => "Expenses",
                "data" => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            "labels" => $data->map(fn (TrendValue $value) => $value->date)
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public static function canView(): bool
    {
        return auth()->user()->role == 'admin' || auth()->user()->role == 'superuser';
    }
}
