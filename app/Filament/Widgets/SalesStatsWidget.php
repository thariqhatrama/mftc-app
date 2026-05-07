<?php

namespace App\Filament\Widgets;

use App\Enums\ApplicationStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->role === UserRole::SALES;
    }

    protected function getStats(): array
    {
        $submitted = Application::where('status', ApplicationStatus::SUBMITTED->value)->count();

        $invoicePending = Invoice::where('status', PaymentStatus::PENDING->value)->count();

        $overrideAwaiting = Invoice::where('override_needs_approval', true)->count();

        return [
            Stat::make('Submitted', $submitted)
                ->description('Pengajuan baru')
                ->color('info'),
            Stat::make('Invoice Pending', $invoicePending)
                ->description('Belum dibayar')
                ->color($invoicePending > 0 ? 'warning' : 'success'),
            Stat::make('Override Approval', $overrideAwaiting)
                ->description('Menunggu approval')
                ->color($overrideAwaiting > 0 ? 'danger' : 'success'),
        ];
    }
}
