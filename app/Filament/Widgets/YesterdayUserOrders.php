<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class YesterdayUserOrders extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Yesterday User Sales';

    protected static ?int $sort = 7;

    public function table(Table $table): Table
    {
        return $table
            ->query(
            User::with(['orders' => function (Builder $query) {
                return $query->whereDate('created_at', Carbon::yesterday());
            }, 'expenses.expenseItems' => function (Builder $query) {
                return $query->whereDate('created_at', Carbon::yesterday());
            }, 'creditOrderPayments' => function (Builder $query) {
                return $query->whereDate('created_at', Carbon::yesterday());
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
                // //filter by date
                // Tables\Filters\Filter::make('created_at')
                //     ->form([
                //         DatePicker::make('created_from')
                //             ->placeholder(fn ($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                //         DatePicker::make('created_until')
                //             ->placeholder(fn ($state): string => now()->format('M d, Y')),
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query
                //             ->when(
                //                 $data['created_from'] ?? null,
                //                 fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                //             )
                //             ->when(
                //                 $data['created_until'] ?? null,
                //                 fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                //             );
                //     })
                //     ->indicateUsing(function (array $data): array {
                //         $indicators = [];
                //         if ($data['created_from'] ?? null) {
                //             $indicators['created_from'] = 'Order from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                //         }
                //         if ($data['created_until'] ?? null) {
                //             $indicators['created_until'] = 'Order until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                //         }

                //         return $indicators;
                //     }),
                // //filter by user
               
            ]);
    }

    public static function canView(): bool
    {
        return auth()->user()->role == 'admin' || auth()->user()->role == 'superuser';
    }
}
