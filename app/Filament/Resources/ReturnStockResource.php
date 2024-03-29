<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturnStockResource\Pages;
use App\Filament\Resources\ReturnStockResource\RelationManagers;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ReturnStock;
use App\Models\User;
use App\Models\VendorProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReturnStockResource extends Resource
{
    protected static ?string $model = ReturnStock::class;

    protected static string $title = 'Stock Returns';

    protected static ?string $navigationGroup = "Transfers";

    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->user()->id),
                Forms\Components\Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->native(false)
                    ->required(),
                Forms\Components\Select::make('product_id')
                    ->options(
                        VendorProduct::where('user_id', auth()->user()->id)
                                        ->where('stock', '!=', 0)
                                        ->with('product.mainProduct')
                                        ->get()->pluck('product.mainProduct.name' ,'product_id')
                    )
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('stock')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if(auth()->user()->role == 'vendor') {
                    return $query->where('user_id', auth()->user()->id);
                }
                if (auth()->user()->role == 'seller') {
                    return $query->where('branch_id', auth()->user()->branch_id);
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable()
                    ->visible(auth()->user()->role != 'vendor'),
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('To Branch')
                    ->sortable()
                    ->searchable()
                    ->visible(auth()->user()->role != 'seller'),
                Tables\Columns\TextColumn::make('product.mainProduct.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected'
                    ])
                    ->disableOptionWhen(
                        //disable pending option to vendor and enable rejected or approved to other
                        fn (string $value) => $value == 'pending' || (($value == 'rejected' || $value == 'approved') && auth()->user()->role == 'vendor')
                    )
                    ->beforeStateUpdated(function ($record, $state) {

                        // dd($record);
                        $vendorProduct = VendorProduct::where('product_id', $record->product_id)
                                                       ->with('product.mainProduct')
                                                       ->where('user_id', $record->user_id)->first();

                        if($vendorProduct->stock < $record->stock) {
                            Notification::make()
                                ->danger()
                                ->body("You can not confirm this transfer bacause vendor's stock is not enough. The currently vendor stock is $vendorProduct->stock {$vendorProduct->product->mainProduct->name}")
                                ->color('danger')
                                ->persistent()
                                ->send();
                        }

                        if($state == 'approved') {
                            $vendorProduct->decrement('stock', $record->stock);

                            //add stock to branchProducts
                            $branchProduct = Product::where('id', $record->product_id)
                                                      ->where('branch_id', $record->branch_id)->first();
                            if($branchProduct) {
                                $branchProduct->increment('stock', $record->stock);

                            } else {
                                Product::create([
                                    'branch_id' => $record->branch_id,
                                    'main_product_id' => $vendorProduct->product->mainProduct->id,
                                    'stock' => $record->stock,
                                    'new_stock' => $record->stock,
                                    'damages' => 0,
                                    'stock_limit' => 0,
                                ]);
                            }
                        }

                        Notification::make()
                                ->success()
                                ->body("$state successfully.")
                                ->color('success')
                                ->send();
                    })
                    ->disablePlaceholderSelection(),
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
                //filter by branch and by user(vendor)
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->options(
                        Branch::all()->pluck('name', 'id')
                    )
                    ->native(false)
                    ->visible(auth()->user()->role == 'admin' || auth()->user()->role == 'superuser'),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Vendor')
                    ->options(
                        User::where('role', 'vendor')->get()->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->visible(auth()->user()->role == 'admin' || auth()->user()->role == 'superuser'),
                
                    //filter by status
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected'
                    ])
                    ->native(false)
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
            'index' => Pages\ListReturnStocks::route('/'),
            'create' => Pages\CreateReturnStock::route('/create'),
            'edit' => Pages\EditReturnStock::route('/{record}/edit'),
        ];
    }
}
