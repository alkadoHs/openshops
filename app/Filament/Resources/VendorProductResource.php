<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorProductResource\Pages;
use App\Filament\Resources\VendorProductResource\RelationManagers;
use App\Models\User;
use App\Models\VendorProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VendorProductResource extends Resource
{
    protected static ?string $model = VendorProduct::class;

    protected static string $title = 'Vendor Products';

    protected static ?string $navigationGroup = "Stocks";

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if(auth()->user()->role == 'vendor') {
                    $query->where('user_id', auth()->user()->id)->orderBy('created_at', 'desc');
                }
                $query->orderBy('created_at', 'desc');
            })
            ->columns([
                TextColumn::make('user.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable()
                    ->visible(auth()->user()->role == 'admin' || auth()->user()->role == 'superuser'),
                TextColumn::make('product.mainProduct.name')
                    ->label('Product')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('stock')
                    ->sortable()
                    ->summarize(
                        Sum::make()->label('Total Stock')
                    )
                    ->numeric(),
                TextColumn::make('product.mainProduct.retail_price')
                    ->label('R.Price')
                    ->sortable()
                    ->numeric()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('product.mainProduct.whole_price')
                    ->label('W.Price')
                    ->sortable()
                    ->numeric()
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->sortable()
                    ->dateTime()
                    ->toggleable(),
                    
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Vendor')
                    ->options(
                        User::where('role', 'vendor')->get()->pluck('name', 'id'))
                    ->native(false),
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Product')
                    ->options(VendorProduct::where('status', 'available')->with('product.mainProduct')->get()->pluck('product.mainProduct.name', 'product_id'))
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(auth()->user()->role == 'admin' || auth()->user()->role == 'superuser'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageVendorProducts::route('/'),
        ];
    }
}
