<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Database\Eloquent\Builder;

class MonthlyUserSales extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Monthly User Sales';

    protected static ?int $sort = 7;
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
            User::with(['orders' => function (Builder $query) {
                return $query->whereMonth('created_at', today());
            }, 'expenses.expenseItems' => function (Builder $query) {
                return $query->whereMonth('created_at', today());
            }, 'creditOrderPayments' => function (Builder $query) {
                return $query->whereMonth('created_at', today());
            }])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Seller') 
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('sales')
                    ->state(fn (User $user) => $user->orders->reduce(fn ($total, $order) => $total + $order->paid, 0))
                    ->numeric(),
                Tables\Columns\TextColumn::make('expenses')
                    ->state(fn (User $user) => $user->expenses->reduce(fn ($total, $expense) => $total + $expense->expenseItems->reduce(fn ($total, $expenseItem) => $total + $expenseItem->cost, 0), 0))
                    ->numeric(),
                Tables\Columns\TextColumn::make('Net Sales')
                    ->state(fn (User $user) => $user->orders->reduce(fn ($total, $order) => $total + $order->paid, 0) - $user->expenses->reduce(fn ($total, $expense) => $total + $expense->expenseItems->reduce(fn ($total, $expenseItem) => $total + $expenseItem->cost, 0), 0))
                    ->numeric(),
                Tables\Columns\TextColumn::make('credit_received')
                    ->state(fn (User $user) => $user->creditOrderPayments->reduce(fn ($total, $creditOrderPayment) => $total + $creditOrderPayment->amount, 0))
                    ->numeric(),
                Tables\Columns\TextColumn::make('Total Sales')
                    ->state(fn (User $user) => $user->orders->reduce(fn ($total, $order) => $total + $order->paid, 0) - $user->expenses->reduce(fn ($total, $expense) => $total + $expense->expenseItems->reduce(fn ($total, $expenseItem) => $total + $expenseItem->cost, 0), 0) + $user->creditOrderPayments->reduce(fn ($total, $creditOrderPayment) => $total + $creditOrderPayment->amount, 0))
                    ->numeric(),
                ]);
    }

    public static function canView(): bool
    {
        return auth()->user()->role == 'admin' || auth()->user()->role == 'superuser';
    }
}

