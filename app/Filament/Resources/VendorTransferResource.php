<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorTransferResource\Pages;
use App\Filament\Resources\VendorTransferResource\RelationManagers;
use App\Models\Branch;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorProduct;
use App\Models\VendorTransfer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VendorTransferResource extends Resource
{
    protected static ?string $model = VendorTransfer::class;

    protected static string $title = 'Vendor Transfers';

    protected static ?string $navigationGroup = "Transfers";

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

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
            ->modifyQueryUsing(function (Builder $query) {
                if(auth()->user()->role == 'vendor') {
                    $query->where('user_id', auth()->user()->id)->where('status', 'pending')->orderBy('updated_at', 'desc');
                } elseif(auth()->user()->role == 'seller') {
                    $query->where('branch_id', auth()->user()->branch_id)->orderBy('updated_at', 'desc');
                }
            })
            ->groups([
                Group::make('updated_at')
                    ->label('Day')
                    ->date(),
                Group::make('status')
            ])
            ->defaultGroup('updated_at')
            ->columns([
                TextColumn::make('branch.name')
                    ->label('From Branch')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Vendor')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('product.mainProduct.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stock'),
                SelectColumn::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected'
                    ])
                    ->disableOptionWhen(
                        //disable pending option to all users and enable rejected or approved to vendors
                        fn (string $value) => $value == 'pending' || (($value == 'rejected' || $value == 'approved') && auth()->user()->role != 'vendor')
                        )
                    ->disablePlaceholderSelection()
                    ->beforeStateUpdated(function ($record, $state) {
                        $product = Product::find($record->product_id);
                        if($state == 'approved') {
                            $product->decrement('stock', $record->stock);

                            //add stock to vendorproduct
                            $vendorProduct = VendorProduct::where('product_id', $record->product_id)
                                                            ->where('user_id', $record->user_id)->first();
                            if($vendorProduct) {
                                $vendorProduct->increment('stock', $record->stock);
                            } else {
                                VendorProduct::create([
                                    'user_id'=> $record->user_id,
                                    'product_id'=> $record->product_id,
                                    'stock' => $record->stock,
                                ]);
                            }
                        }

                        Notification::make()
                                ->success()
                                ->body("$state successfully.")
                                ->color('success')
                                ->send();
                    })
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected'
                    ])
                    ->native(false)
                    ->label('Status'),
                Tables\Filters\SelectFilter::make('user_id')
                    ->options(User::where('role', 'vendor')->get()->pluck('name', 'id'))
                    ->native(false)
                    ->visible(fn () => auth()->user()->role == 'admin')
                    ->native(false)
                    ->label('Vendor'),
                Tables\Filters\SelectFilter::make('branch_id')
                    ->options(
                        Branch::get()->pluck('name', 'id')
                    )
                    ->native(false)
                    ->label('From Branch'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->role == 'admin' || auth()->user()->role == 'superuser'),
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
