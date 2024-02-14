<?php

namespace App\Filament\Resources\BranchTransferResource\Pages;

use App\Filament\Resources\BranchTransferResource;
use App\Models\BranchTransfer;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBranchTransfers extends ListRecords
{
    protected static string $resource = BranchTransferResource::class;

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
                ->badge(BranchTransfer::query()->count()),
            'new_stock' => Tab::make('NewStock')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('to_branch_id', auth()->user()->branch_id)->where('status', 'pending'))
                ->badge(BranchTransfer::query()->where('to_branch_id', auth()->user()->branch_id)->where('status', 'pending')->count())
                ->badgeColor('success'),
            'sent_stock' => Tab::make('SentStock')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('from_branch_id', auth()->user()->branch_id)->where('status', 'pending'))
                ->badge(BranchTransfer::query()->where('from_branch_id', auth()->user()->branch_id)->where('status', 'pending')->count())
                ->badgeColor('warning'),
        ];
    }
}
