<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WelcomeSeller extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('You are logged in as a', auth()->user()->role)
                ->description(__('Go to the sales area to start selling.'))
                ->descriptionIcon('heroicon-o-user-circle')
                ->color('success')
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->role != 'admin';
    }
}
