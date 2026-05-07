<?php

namespace App\Filament\Widgets;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\AuditAssignment;
use App\Models\NonConformity;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AuditorStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->role === UserRole::AUDITOR;
    }

    protected function getStats(): array
    {
        $auditorId = auth()->id();

        $activeAssignments = AuditAssignment::where('auditor_user_id', $auditorId)
            ->whereHas('application', fn ($q) => $q->whereIn('status', [
                ApplicationStatus::AUDITOR_ASSIGNED->value,
                ApplicationStatus::SCHEDULE_CONFIRMED->value,
                ApplicationStatus::AUDIT_IN_PROGRESS->value,
                ApplicationStatus::REVISION->value,
            ]))
            ->count();

        $thisWeek = AuditAssignment::where('auditor_user_id', $auditorId)
            ->whereBetween('scheduled_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereHas('application', fn ($q) => $q->whereNotIn('status', [
                ApplicationStatus::CANCELLED->value,
                ApplicationStatus::AUTO_CANCELLED->value,
                ApplicationStatus::EXPIRED->value,
            ]))
            ->count();

        $ncPendingVerification = NonConformity::where('verified_by_auditor', false)
            ->whereHas('auditAssignment', fn ($q) => $q->where('auditor_user_id', $auditorId))
            ->count();

        return [
            Stat::make('Tugas Aktif', $activeAssignments)
                ->description('Assignment yang sedang berjalan')
                ->color('primary'),
            Stat::make('Jadwal Minggu Ini', $thisWeek)
                ->description(now()->startOfWeek()->format('d M') . ' — ' . now()->endOfWeek()->format('d M'))
                ->color($thisWeek > 0 ? 'info' : 'gray'),
            Stat::make('NC Perlu Verifikasi', $ncPendingVerification)
                ->description('Menunggu verifikasi auditor')
                ->color($ncPendingVerification > 0 ? 'warning' : 'success'),
        ];
    }
}
