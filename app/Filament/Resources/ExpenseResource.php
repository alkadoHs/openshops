<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\Expense;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static string $title = 'Expenses';

    protected static ?string $navigationGroup = "Financials";

    protected static ?string $navigationIcon = 'heroicon-o-backspace';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                  Forms\Components\Group::make()
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->user()->id),
                        Forms\Components\Section::make()
                            ->columns(1)
                            ->schema([
                                Forms\Components\Repeater::make('expenseItems')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\TextInput::make('item')
                                            ->placeholder('eg. chackula')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('cost')
                                            ->required()
                                            ->numeric()
                                            ->live(onBlur: true),
                                    ])
                                    ->addActionLabel('Add Item')
                                    ->columns(2)
                                    ->deleteAction(fn (Get $get, Set $set) => static::updateTotal($get, $set))
                                ])
                                ->live()
                            ]),
                        Forms\Components\TextInput::make('total')
                            ->label('Total Cost')
                            ->prefix('Tsh')
                            ->afterStateHydrated(fn (Get $get, Set $set) => static::updateTotal($get, $set))
                            ->readOnly(),
                    ]);
    }


    public static function updateTotal(Get $get, Set $set): void
    {
        $expenseItems = collect($get('expenseItems'))->filter(fn ($item) => !empty($item['cost']) && !empty($item['item']));
        $total = $expenseItems->sum('cost');
        $set('total', number_format($total));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing( function ( Builder $query) {
                if(auth()->user()->role != 'admin' && auth()->user()->role != 'superuser'){
                    return $query->where('user_id', auth()->user()->id)->orderBy('created_at', 'desc');
                }

                return $query->orderBy('created_at', 'desc');
            })
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total expense')
                    ->label('total')
                    ->state(function (Expense $record) {
                        //compute the total expense items
                        return number_format($record->expenseItems->sum('cost'));
                    } ),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //filter by date
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->placeholder(fn ($state): string => 'Dec 18, ' . now()->subYear()->format('Y')),
                        Forms\Components\DatePicker::make('created_until')
                            ->placeholder(fn ($state): string => now()->format('M d, Y')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Order from ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Order until ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
                //filter by user
                Tables\Filters\SelectFilter::make('user_id')
                    ->placeholder('Filter by user')
                    ->options(
                        fn () => \App\Models\User::all()->pluck('name', 'id')
                    )
                    ->native(false)
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'view' => Pages\ViewExpense::route('/{record}'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
