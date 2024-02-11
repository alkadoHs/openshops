<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use App\Models\Salary;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProfitsOverView extends BaseWidget
{

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $todayProfit = 0;
        $todayOrderItems = OrderItem::with('product.mainProduct')->whereDate('created_at', today())->get();

        //profit = (price - product->mainProduct->buy_price) * quantity
        foreach ($todayOrderItems as $orderItem) {
            $todayProfit += (($orderItem->price - $orderItem->product->mainProduct->buy_price) * $orderItem->quantity);
        }

        $monthlyProfit = 0;
        $monthlyOrderItems = OrderItem::with('product.mainProduct')->whereMonth('created_at', today())->get();

        //profit = (price - product->mainProduct->buy_price) * quantity
        foreach ($monthlyOrderItems as $orderItem) {
            $monthlyProfit += (($orderItem->price - $orderItem->product->mainProduct->buy_price) * $orderItem->quantity);
        }

        $monthlySalaries = Salary::whereMonth('created_at', today())->sum('amount');

        return [
            Stat::make("Today's Profit", number_format($todayProfit))
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success')
                ->chart([120000, 1300000, 900000, 3000000, 200000, 780000, 5000000]),

            Stat::make('Monthly Profit', number_format($monthlyProfit))
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('primary')
                ->chart([189000, 3000000, 30000, 200000, 7800000]),

            Stat::make('Monthly Salaries', number_format($monthlySalaries))
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->role == 'admin' || auth()->user()->role == 'superuser';
    }
}
