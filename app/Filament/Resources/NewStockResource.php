<?php

namespace App\Filament\Resources;

use App\Models\MainProduct;
use Illuminate\Support\Facades\DB;
use App\Filament\Resources\NewStockResource\Pages;
use App\Models\NewStock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class NewStockResource extends Resource
{
    protected static ?string $model = NewStock::class;

    protected static ?string $navigationGroup = 'Stocks';

    protected static ?int $navigtationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('main_product_id')
                    ->label(__('Product'))
                    ->searchable()
                    ->options(
                        fn () => MainProduct::get()
                            ->pluck('name', 'id')
                    )
                    ->live(debounce: '1s')
                    ->required(),
                Forms\Components\Select::make('branch_id')
                    ->label(__('Branch'))
                    ->options(
                        fn (Get $get) => DB::table('branches')
                                    ->join('products', 'branches.id', '=', 'products.branch_id')
                                    ->where('products.main_product_id', $get('main_product_id'))
                                    ->select('branches.name', 'branches.id')
                                    ->get()
                                    ->pluck('name', 'id')
                    )
                    ->native(false)
                    ->required(),
                Forms\Components\TextInput::make('stock')
                    ->label(__('Stock'))
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mainProduct.name')
                    ->label(__('Product'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewStocks::route('/'),
            'create' => Pages\CreateNewStock::route('/create'),
            'edit' => Pages\EditNewStock::route('/{record}/edit'),
        ];
    }
}
