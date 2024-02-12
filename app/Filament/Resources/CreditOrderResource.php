<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreditOrderResource\Pages;
use App\Filament\Resources\CreditOrderResource\RelationManagers;
use App\Models\CreditOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CreditOrderResource extends Resource
{
    protected static ?string $model = CreditOrder::class;

    protected static string $title = 'Credit Sales';

    protected static ?string $navigationGroup = "Financials";

    protected static ?string $navigationLabel = 'Credit Sales';

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([

    //         ]);
    // }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid')
                    ->label('Amount paid')
                    ->numeric()
                    ->state(fn (CreditOrder $creditOrder) => $creditOrder->order->paid + $creditOrder->creditOrderPayments()->get()->reduce(fn ($total, $payment) => $total + $payment->amount, 0))
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_credit')
                    ->label('Total credit')
                    ->state(fn (CreditOrder $creditOrder) => $creditOrder->order->orderItems()->get()->reduce(fn ($total, $item) => $total + $item->price * $item->quantity, 0) - $creditOrder->order->paid)
                    ->numeric(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make('Add')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CreditOrderPaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCreditOrders::route('/'),
            // 'create' => Pages\CreateCreditOrder::route('/create'),
            'edit' => Pages\EditCreditOrder::route('/{record}/edit'),
        ];
    }
}
