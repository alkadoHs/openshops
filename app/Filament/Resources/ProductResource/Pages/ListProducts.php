<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->badge(Product::query()->count()),
            'in_stock' => Tab::make('InStock')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereColumn('stock', '>', 'stock_limit'))
                ->badge(Product::query()->whereColumn('stock', '>', 'stock_limit')->count())
                ->icon('heroicon-s-adjustments-vertical'),
            'low' => Tab::make('Low')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereColumn('stock','<', 'stock_limit'))
                ->badge(Product::query()->whereColumn('stock', '<', 'stock_limit')->count())
                ->badgeColor('warning')
                ->icon('heroicon-s-adjustments-vertical'),
            'damages' => Tab::make('Damages')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('damages', '!=', 0))
                ->badge(Product::query()->where('damages', '!=', 0)->count())
                ->badgeColor('danger')
                ->icon('heroicon-s-adjustments-vertical'),
            'empty' => Tab::make('Empty')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('stock', 0))
                ->badge(Product::query()->where('stock', 0)->count())
                ->badgeColor('danger')
                ->icon('heroicon-s-adjustments-vertical'),
        ];
    }
}
