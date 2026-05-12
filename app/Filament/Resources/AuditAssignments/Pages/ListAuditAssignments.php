<?php

namespace App\Filament\Resources\AuditAssignments\Pages;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Filament\Resources\AuditAssignments\AuditAssignmentResource;
use App\Models\AuditAssignment;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAuditAssignments extends ListRecords
{
    protected static string $resource = AuditAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        if (auth()->user()?->role === UserRole::AUDITOR) {
            $auditorId = auth()->id();
            $activeStatuses = [
                ApplicationStatus::SCHEDULE_CONFIRMED,
                ApplicationStatus::AUDIT_IN_PROGRESS,
                ApplicationStatus::REVISION,
            ];
            $submittedStatuses = [
                ApplicationStatus::REPORT_SUBMITTED,
                ApplicationStatus::REPORT_REJECTED,
                ApplicationStatus::APPROVED,
                ApplicationStatus::CERTIFIED,
            ];

            return [
                'active' => Tab::make('Tugas Aktif')
                    ->icon('heroicon-o-play-circle')
                    ->badge(AuditAssignment::where('auditor_user_id', $auditorId)
                        ->whereHas('application', fn (Builder $query) => $query->whereIn('status', $activeStatuses))
                        ->count() ?: null)
                    ->badgeColor('warning')
                    ->modifyQueryUsing(fn (Builder $query) => $query
                        ->whereHas('auditAssignment', fn (Builder $assignmentQuery) => $assignmentQuery->where('auditor_user_id', $auditorId))
                        ->whereIn('status', $activeStatuses)),
                'submitted' => Tab::make('Laporan Submitted')
                    ->icon('heroicon-o-document-check')
                    ->badge(AuditAssignment::where('auditor_user_id', $auditorId)
                        ->whereHas('application', fn (Builder $query) => $query->whereIn('status', $submittedStatuses))
                        ->count() ?: null)
                    ->badgeColor('success')
                    ->modifyQueryUsing(fn (Builder $query) => $query
                        ->whereHas('auditAssignment', fn (Builder $assignmentQuery) => $assignmentQuery->where('auditor_user_id', $auditorId))
                        ->whereIn('status', $submittedStatuses)),
            ];
        }

        return [
            'menunggu_assign' => Tab::make('Menunggu Assign')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ApplicationStatus::AUDIT_READY)),

            'sudah_diassign' => Tab::make('Sudah Di-assign')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [
                    ApplicationStatus::AUDITOR_ASSIGNED,
                    ApplicationStatus::SCHEDULE_CONFIRMED,
                    ApplicationStatus::AUDIT_IN_PROGRESS,
                    ApplicationStatus::REVISION,
                    ApplicationStatus::REPORT_SUBMITTED,
                    ApplicationStatus::REPORT_REJECTED,
                ])),
        ];
    }
}
