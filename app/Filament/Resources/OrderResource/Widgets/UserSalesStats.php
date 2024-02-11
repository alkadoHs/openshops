<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Models\CreditOrderPayment;
use App\Models\Expense;
use App\Models\Order;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserSaleStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListOrders::class;
    }
    
    protected function getStats(): array
    {
        $todayUserOrders = Order::where('user_id', auth()->id())->whereDate('created_at', today())->get()->sum('paid');
        $userExpenses = Expense::where('user_id', auth()->id())->whereDate('created_at', today())->with('expenseItems')->get()->sum(function($expense) {
            return $expense->expenseItems->sum('cost');
        });
        $todayCreditPayments = CreditOrderPayment::where('user_id', auth()->id())->whereDate('created_at', today())->sum('amount');
        return [
            Stat::make('Total Sales', number_format($todayUserOrders))
                ->description("NET: " . number_format($todayUserOrders - $userExpenses))
                ->color('success'),
            Stat::make('Total Expenses', number_format($userExpenses)),
            Stat::make('Total Credit Payments', number_format($todayCreditPayments)),
        ];
    }
}
