<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Branch;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?int $navigtationSort = 3;

    protected static ?string $navigationGroup = 'Stocks';

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';


    public static function getNavigationBadge(): ?string
    {
        return number_format(static::getModel()::count());
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('main_product_id')
                    ->relationship('mainProduct', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                        Forms\Components\TextInput::make('buy_price')
                        ->required()
                        ->numeric(),
                        Forms\Components\TextInput::make('retail_price')
                        ->required()
                        ->numeric(),
                        Forms\Components\TextInput::make('whole_price')
                        ->required()
                        ->numeric(),
                    ])
                    ->required(),
                Forms\Components\Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->native(false)
                    ->required(),
                Forms\Components\TextInput::make('stock')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('stock_limit')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('new_stock')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->visibleOn('edit'),
                Forms\Components\TextInput::make('damages')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => auth()->user()->role == 'admin' || auth()->user()->role == 'superuser' ? $query->orderBy('created_at', 'desc') : $query->where('branch_id', auth()->user()->branch_id)->orderBy('created_at', 'desc'))
            ->columns([
                Tables\Columns\TextColumn::make('mainProduct.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('branch.name')
                    ->sortable()
                    ->visible(auth()->user()->role == "admin" || auth()->user()->role == "superuser"),
                Tables\Columns\TextColumn::make('stock')
                    ->sortable()
                    ->toggleable()
                    ->summarize([
                        Sum::make()->label('Total Stock')
                    ]),
                Tables\Columns\TextColumn::make('mainProduct.retail_price')
                    ->label('R.Price')
                    ->numeric()
                    ->sortable()
                    ->visible(auth()->user()->role != 'admin' && auth()->user()->role != 'superuser')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('mainProduct.whole_price')
                    ->label('W.Price')
                    ->numeric()
                    ->sortable()
                    ->visible(auth()->user()->role != 'admin' && auth()->user()->role != 'superuser')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('stock_limit')
                    ->numeric()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('new_stock')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->summarize([
                        Sum::make()->label('New Stock')
                    ]),
                Tables\Columns\TextColumn::make('damages')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->summarize([
                        Sum::make()->label('Total Damages')
                    ]),
                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->numeric()
                    ->toggleable()
                    ->visible(auth()->user()->role == "admin" || auth()->user()->role == "superuser")
                    ->state(function (Product $record): int {
                        return $record->mainProduct->buy_price * $record->stock;
                    }),
                Tables\Columns\TextColumn::make('Retail Revenue')
                    ->label('R.Revenue')
                    ->numeric()
                    ->visible(auth()->user()->role == "admin" || auth()->user()->role == "superuser")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->state(function (Product $record): int {
                        return $record->mainProduct->retail_price * $record->stock;
                    }),
                Tables\Columns\TextColumn::make('Whole Revenue')
                    ->label('W.Revenue')
                    ->numeric()
                    ->visible(auth()->user()->role == "admin" || auth()->user()->role == "superuser")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->state(function (Product $record): int {
                        return $record->mainProduct->whole_price * $record->stock;
                    }),
                Tables\Columns\TextColumn::make('R Profit')
                    ->label('R.Profit')
                    ->numeric()
                    ->toggleable()
                    ->visible(auth()->user()->role == "admin" || auth()->user()->role == "superuser")
                    ->state(function (Product $record): int {
                        return ($record->mainProduct->retail_price - $record->mainProduct->buy_price) * $record->stock;
                    }),
                Tables\Columns\TextColumn::make('W Profit')
                    ->label('W.Profit')
                    ->numeric()
                    ->toggleable()
                    ->visible(auth()->user()->role == "admin" || auth()->user()->role == "superuser")
                    ->state(function (Product $record): int {
                        return ($record->mainProduct->whole_price - $record->mainProduct->buy_price) * $record->stock;
                    }),
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
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->options(
                        Branch::all()->pluck('name', 'id')
                    )
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->role == 'admin' || auth()->user()->role == 'superuser'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
