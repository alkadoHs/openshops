<?php

namespace App\Filament\Widgets;

use App\Models\CreditOrder;
use App\Models\Expense;
use App\Models\ExpenseItem;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodayOrdersOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $todayOrders = Order::whereDate('created_at', today())->sum('paid');
        $monthlyOrders = Order::whereMonth('created_at', today())->sum('paid');
        $dailyExpenses = ExpenseItem::with('expenseItems')->whereDate('created_at', today())->sum('cost');
        $monthlyExpenses = ExpenseItem::with('expenseItems')->whereMonth('created_at', today())->sum('cost');

        $totalCreditOrderAmount = 0;

        $CreditOrders = CreditOrder::with(['order.orderItems', 'creditOrderPayments'])->get();
        // reduce(function ($totalCredit, $creditOrder) {
        //     $totalPrice = $creditOrder->order->orderItems()->sum('price');
        //     return $totalCredit + ($totalPrice - $creditOrder->order->paid);
        // }, 0 );

        foreach ($CreditOrders as $creditOrder) {
            $total = $creditOrder->order->orderItems()->get()->reduce(fn ($totalAll , $item) => ($item->price * $item->quantity) + $totalAll);
            $totalCreditOrderAmount += (($total) - $creditOrder->order->paid - $creditOrder->creditOrderPayments()->sum('amount'));
        }

        return [
            Stat::make("Today's Sales", number_format($todayOrders))
                ->description("Net: " . number_format($todayOrders - $dailyExpenses))
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success')
                ->chart([120000, 1300000, 900000, 3000000, 200000, 780000, 5000000]),
            
            Stat::make('Monthly Sales', number_format($monthlyOrders))
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->description("Net: " . number_format($monthlyOrders - $monthlyExpenses))
                ->color('primary')
                ->chart([189000, 3000000, 30000, 200000, 7800000]),

            Stat::make('Today Expenses', number_format($dailyExpenses))
                ->color('danger')
                ->chart([120000, 1300000, 900000, 3000000, 200000, 780000, 5000000]),

            Stat::make('Credit Sales', number_format($totalCreditOrderAmount))
                ->color('warning')
                ->chart([120000, 1300000, 900000, 3000000, 200000, 780000, 5000000]),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->role == 'admin' || auth()->user()->role == 'superuser';
    }
}
