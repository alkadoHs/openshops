<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationGroup = 'Point Of Sale';

     protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\Hidden::make('branch_id')
                                    ->default(auth()->user()->branch_id),
                                Forms\Components\TextInput::make('name')
                                    ->unique()
                                    ->required()
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('contact')
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpan(['lg' => fn (?Order $record) => $record === null ? 3 : 2]),

                    Forms\Components\Section::make()
                    ->columns(1)
                    ->schema([
                        Forms\Components\Repeater::make('orderItems')
                            ->relationship()
                            ->mutateRelationshipDataBeforeCreateUsing(
                                 function (array $data) {
                                    if(auth()->user()->role == 'vendor') {
                                        //decrement vendors stock
                                    } else {
                                        //decrement branch product stock

                                        $product = Product::find($data['product_id']);

                                        if($product->stock < $data['quantity']) {
                                            Notification::make()
                                                ->title('Order creation failed!')
                                                ->body(" {$product->mainProduct->name} stock available is $product->stock, you can sell only the stock available")
                                                ->color('danger')
                                                ->send();
                                            return null;
                                        } else {
                                            $product->decrement("stock", $data['quantity']);
                                        }

                                    }
                                    return $data;
                                 }
                                ) 
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->searchable()
                                    ->live()
                                    ->options(
                                        fn (Product $query) => $query->where('branch_id', auth()->user()->branch_id)
                                                                     ->where('stock', '!=', 0)
                                                                     ->with('mainProduct')
                                                                     ->get()
                                                                     ->pluck('mainProduct.name', 'id')
                                    )
                                    ->disableOptionWhen(
                                        fn ($value, $state, Get $get) => collect($get('../*.product_id'))
                                                                             ->reject(fn ($id) => $id == $state)
                                                                             ->filter()
                                                                             ->contains($value)
                                    )
                                    ->required(),
                
                                Forms\Components\Select::make('sell_by')
                                    ->options([
                                        'R' => 'Retail',
                                        'W' => 'Whole',
                                    ])
                                    ->native(false)
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
                                        ->content(fn (Get $get): string => "Tsh " . number_format($get('quantity') == ''? 0 : $get('quantity') * $get('price')))
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
                            ->addActionLabel('Add Item')
                            ->itemLabel(fn (array $state): ?string => Product::find($state['product_id'])->mainProduct->name ?? null),
                    ]),


                    Forms\Components\Section::make()
                        ->columns(1)
                        ->maxWidth('1/2')
                        ->schema([
                            Forms\Components\TextInput::make('subtotal')
                                ->label('Total')
                                ->readOnly()
                                ->dehydrated(false)
                                ->prefix('Tsh')
                                ->afterStateHydrated(fn (Get $get, Set $set) => static::updateTotals($get, $set)),

                            // Forms\Components\TextInput::make('taxes')
                            //     ->suffix('%')
                            //     ->required()
                            //     ->numeric()
                            //     ->default(10)
                            //     ->live(debounce:'1s')
                            //     ->afterStateUpdated(fn (Get $get, Set $set) => static::updateTotals($get, $set)),

                            Forms\Components\TextInput::make('paid')
                                ->numeric()
                                ->required()
                                ->prefix('Tsh')

                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid')
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        $selectedProducts = collect($get('orderItems'))->filter(fn($item) => !empty($item['product_id'] && !empty($item['quantity'])));

        foreach ($selectedProducts as $selectedProduct) {
            $product = Product::find($selectedProduct['product_id']);

            if($product->stock < $selectedProduct['quantity']) {
                Notification::make()
                    ->title('Stock is not enough!')
                    ->body("Your stock is $product->stock, update the quantity according to your stock balance")
                    ->danger()
                    ->color('danger')
                    ->send();
                return;
            }
        }

        $subtotal = $selectedProducts->reduce(fn ($subtotal, $product) => $subtotal + ($product['quantity'] * $product['price']), 0);

        $set('subtotal' , number_format($subtotal));
        $set('paid', $subtotal);
        // $set('total', number_format($subtotal + ($subtotal * ($get('taxes') / 100))));

    }
}
