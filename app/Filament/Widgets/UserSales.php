<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Database\Eloquent\Builder;

class UserSales extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 6;
    public function table(Table $table): Table
    {
        return $table
            ->query(
            User::with(['orders' => function (Builder $query) {
                return $query->where('created_at', '>=', today());
            }, 'expenses.expenseItems' => function (Builder $query) {
                return $query->where('created_at', '>=', today());
            }, 'creditOrderPayments' => function (Builder $query) {
                return $query->where('created_at', '>=', today());
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
                ])
                ->filters([
                    //filter by month, year, week, day
                    Tables\Filters\Filter::make('day')
                        ->query(fn (Builder $query, $state) => User::with(['orders' => function (Builder $query) {
                                return $query->where('created_at', '>=', now()->subDays(1));
                            }, 'expenses.expenseItems' => function (Builder $query) {
                                return $query->where('created_at', '>=', now()->subDays(1));
                            }, 'creditOrderPayments' => function (Builder $query) {
                                return $query->where('created_at', '>=', now()->subDays(1));
                            }]))
                        ->label('Today'),

                    Tables\Filters\Filter::make('week')
                        ->query(fn (Builder $query, $state) => User::with(['orders' => function (Builder $query) {
                                return $query->where('created_at', '>=', now()->subDays(7));
                            }, 'expenses.expenseItems' => function (Builder $query) {
                                return $query->where('created_at', '>=', now()->subDays(7));
                            }, 'creditOrderPayments' => function (Builder $query) {
                                return $query->where('created_at', '>=', now()->subDays(7));
                            }]))
                        ->label('This Week'),

                    Tables\Filters\Filter::make('month')
                        ->query(fn (Builder $query, $state) => User::with(['orders' => function (Builder $query) {
                                return $query->where('created_at', '>=', now()->subDays(30));
                            }, 'expenses.expenseItems' => function (Builder $query) {
                                return $query->where('created_at', '>=', now()->subDays(30));
                            }, 'creditOrderPayments' => function (Builder $query) {
                                return $query->where('created_at', '>=', now()->subDays(30));
                            }]))
                        ->label('This Month'),

                    Tables\Filters\Filter::make('year')
                        ->query(fn (Builder $query, $state) => User::with(['orders' => function (Builder $query) {
                                return $query->where('created_at', '>=', now()->subDays(365));
                            }, 'expenses.expenseItems' => function (Builder $query) {
                                return $query->where('created_at', '>=', now()->subDays(365));
                            }, 'creditOrderPayments' => function (Builder $query) {
                                return $query->where('created_at', '>=', now()->subDays(365));
                            }]))
                        ->label('This Year'),
                ]);
    }
}
