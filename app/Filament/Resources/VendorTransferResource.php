<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorTransferResource\Pages;
use App\Filament\Resources\VendorTransferResource\RelationManagers;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorTransfer;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VendorTransferResource extends Resource
{
    protected static ?string $model = VendorTransfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                   ->label('Vendor')
                   ->options(User::where('role', 'vendor')->get()->pluck('name', 'id'))
                   ->native(false)
                   ->required(),
                Forms\Components\Hidden::make('branch_id')
                    ->default(auth()->user()->branch_id),
                Forms\Components\Select::make('product_id')
                   ->label('Product')
                   ->searchable()
                   ->options(
                        Product::where('branch_id', auth()->user()->branch_id)
                                ->where('stock', '!=', 0)
                                ->with('mainProduct')
                                ->get()
                                ->pluck('mainProduct.name', 'id')
                        )
                   ->native(false)
                   ->required(),
                Forms\Components\TextInput::make('stock')
                    ->numeric()
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Vendor')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('product.mainProduct.name')
                    ->label('Product')
                    ->sortable(),
                TextColumn::make('stock'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger'
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageVendorTransfers::route('/'),
        ];
    }
}
