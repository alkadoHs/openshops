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
                ->badge(auth()->user()->role == 'admin' ? Product::query()->count() : Product::query()->where('branch_id', auth()->user()->branch_id)->count()),
            'in_stock' => Tab::make('InStock')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereColumn('stock', '>', 'stock_limit'))
                ->badge(auth()->user()->role == 'admin' ? Product::query()->whereColumn('stock', '>', 'stock_limit')->count() : Product::query()->whereColumn('stock', '>', 'stock_limit')->where('branch_id', auth()->user()->branch_id)->count())
                ->icon('heroicon-s-adjustments-vertical'),
            'low' => Tab::make('Low')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereColumn('stock','<', 'stock_limit'))
                ->badge(auth()->user()->role == 'admin' ? Product::query()->whereColumn('stock','<', 'stock_limit')->count() : Product::query()->whereColumn('stock','<', 'stock_limit')->where('branch_id', auth()->user()->branch_id)->count())
                ->badgeColor('warning')
                ->icon('heroicon-s-adjustments-vertical'),
            'damages' => Tab::make('Damages')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('damages', '!=', 0))
                ->badge(auth()->user()->role == 'admin' ? Product::query()->where('damages', '!=', 0)->count() : Product::query()->where('damages', '!=', 0)->where('branch_id', auth()->user()->branch_id)->count())
                ->badgeColor('danger')
                ->icon('heroicon-s-adjustments-vertical'),
            'empty' => Tab::make('Empty')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('stock', 0))
                ->badge(auth()->user()->role == 'admin' ? Product::query()->where('stock', 0)->count() : Product::query()->where('stock', 0)->where('branch_id', auth()->user()->branch_id)->count())
                ->badgeColor('danger')
                ->icon('heroicon-s-adjustments-vertical'),
        ];
    }
}
