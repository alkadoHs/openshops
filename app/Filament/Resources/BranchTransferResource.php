<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchTransferResource\Pages;
use App\Filament\Resources\BranchTransferResource\RelationManagers;
use App\Models\Branch;
use App\Models\BranchTransfer;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BranchTransferResource extends Resource
{
    protected static ?string $model = BranchTransfer::class;

    protected static string $title = 'Branch Transfers';

    protected static ?string $navigationGroup = "Transfers";

    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('from_branch_id')
                    ->default(auth()->user()->branch_id),
                Forms\Components\Select::make('to_branch_id')
                    ->options(
                        Branch::where('id', '!=', auth()->user()->branch_id)->get()->pluck('name', 'id')
                    )
                    ->native(false)
                    ->required(),
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->options(
                        Product::where('branch_id', auth()->user()->branch_id)
                                        ->where('stock', '!=', 0)
                                        ->with('mainProduct')
                                        ->get()->pluck('mainProduct.name' ,'id')
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
            ->defaultGroup('updated_at')
            ->defaultSort('updated_at', 'desc')
            ->groups([
                Group::make('updated_at')
                    ->label('Day')
                    ->date(),
                Group::make('status'),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                if(auth()->user()->role != 'admin') {
                    return $query;
                }
                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('fromBranch.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('toBranch.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('receiver.name')
                    ->sortable(),
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
                        //disable pending users found to from branch and approved or rejected users found to to branch
                        fn ($record) => $record->from_branch_id == auth()->user()->branch_id && $record->status != 'pending'
                        

                        // } $value == 'pending' || (($value == 'rejected' || $value == 'approved') && auth()->user()->role != 'vendor')
                    )
                    ->disablePlaceholderSelection()
                    ->beforeStateUpdated(function ($record, $state) {
                        $product = Product::find($record->product_id);
                        if($state == 'approved') {
                            $product->decrement('stock', $record->stock);

                            //add stock to destination branch
                            $branchToProduct = Product::where('branch_id', $record->to_branch_id)
                                                    ->where('main_product_id', $product->main_product_id)
                                                    ->first();

                            if($branchToProduct) {
                                $branchToProduct->increment('stock', $record->stock);
                            } else {
                                Product::create([
                                    'branch_id'=> $record->to_branch_id,
                                    'main_product_id'=> $product->main_product_id,
                                    'stock' => $record->stock,
                                    'stock_limit' => 0,
                                    'new_stock' => $record->stock,
                                ]);
                            }
                        }

                        Notification::make()
                                ->success()
                                ->body("$state successfully.")
                                ->color('success')
                                ->send();
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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected'
                    ])
                    ->native(false)
                    ->label('Status'),
                Tables\Filters\SelectFilter::make('from_branch_id')
                    ->options(
                        Branch::get()->pluck('name', 'id')
                    )
                    ->native(false)
                    ->label('From Branch'),
                Tables\Filters\SelectFilter::make('to_branch_id')
                    ->options(
                        Branch::get()->pluck('name', 'id')
                    )
                    ->native(false)
                    ->label('To Branch'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
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
            'index' => Pages\ListBranchTransfers::route('/'),
            'create' => Pages\CreateBranchTransfer::route('/create'),
            'edit' => Pages\EditBranchTransfer::route('/{record}/edit'),
        ];
    }
}
