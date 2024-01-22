<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->columns(1)
                    ->schema([
                        Forms\Components\Repeater::make('orderItems')
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->searchable()
                                    ->live()
                                    ->options(fn (Product $query) => $query->where('branch_id', auth()->user()->branch_id)->with('mainProduct')->get()->pluck('mainProduct.name', 'id'))
                                    ->required(),
                
                                Forms\Components\Select::make('sell_by')
                                    ->options([
                                        'R' => 'Retail',
                                        'W' => 'Whole',
                                    ])
                                    ->live()
                                    ->afterStateUpdated( fn (Get $get, Set $set, ?string $state): int
                                        => $set('price', $state == 'R' ? Product::find($get('product_id'))->mainProduct?->retail_price?? 0 : 
                                                                        Product::find($get('product_id'))->mainProduct?->whole_price?? 0)
                                    )
                                    ->required(),
                
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->live(debounce: '2s')
                                    ->required(),
                
                                Forms\Components\TextInput::make('price')
                                    ->label('Unit Price')
                                    ->disabled()
                                    ->live()
                                    ->dehydrated()
                                    ->numeric(),
                
                                Forms\Components\Placeholder::make('total_price')
                                        ->label('Total Price')
                                        ->content(fn (Get $get): string => "Tsh " . number_format($get('quantity') * $get('price')))
                            ])
                            ->live()
                            ->afterStateUpdated(
                                fn (Get $get, Set $set) => static::updateTotals($get, $set)
                            )
                            ->deleteAction(
                                fn (Get $get, Set $set) => static::updateTotals($get, $set)
                            )
                            ->columns(5)
                            ->collapsible()
                            ->addActionLabel('Add Item'),
                    ]),


                    Forms\Components\Section::make()
                        ->columns(1)
                        ->maxWidth('1/2')
                        ->schema([
                            Forms\Components\TextInput::make('subtotal')
                                ->readOnly()
                                ->prefix('Tsh')
                                ->afterStateHydrated(fn (Get $get, Set $set) => static::updateTotals($get, $set)),

                            Forms\Components\TextInput::make('taxes')
                                ->suffix('%')
                                ->required()
                                ->numeric()
                                ->default(10)
                                ->live(debounce:'1s')
                                ->afterStateUpdated(fn (Get $get, Set $set) => static::updateTotals($get, $set)),

                            Forms\Components\TextInput::make('total')
                                ->readOnly()
                                ->prefix('Tsh')

                        ])
                ]);

    }


    public static function updateTotals(Get $get, Set $set): void
    {
        $selectedProducts = collect($get('orderItems'))->filter(fn($item) => !empty($item['product_id'] && !empty($item['quantity'])));

        $subtotal = $selectedProducts->reduce(fn ($subtotal, $product) => $subtotal + ($product['quantity'] * $product['price']), 0);

        $set('subtotal' , number_format($subtotal));
        $set('total', number_format($subtotal + ($subtotal * ($get('taxes') / 100))));

    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_id')
            ->columns([
                Tables\Columns\TextColumn::make('product_id'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
