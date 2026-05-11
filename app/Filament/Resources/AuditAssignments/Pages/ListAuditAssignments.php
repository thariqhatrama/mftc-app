<?php

namespace App\Filament\Resources\AuditAssignments\Pages;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Filament\Resources\AuditAssignments\AuditAssignmentResource;
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
            return [
                'aktif' => Tab::make('Tugas Aktif')
                    ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [
                        ApplicationStatus::SCHEDULE_CONFIRMED,
                        ApplicationStatus::AUDIT_IN_PROGRESS,
                        ApplicationStatus::REVISION,
                        ApplicationStatus::REPORT_REJECTED,
                    ])),
                'submitted' => Tab::make('Laporan Submitted')
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('status', ApplicationStatus::REPORT_SUBMITTED)),
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
