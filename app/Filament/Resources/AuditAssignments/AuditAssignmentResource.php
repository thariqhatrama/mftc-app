<?php

namespace App\Filament\Resources\AuditAssignments;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Filament\Resources\AuditAssignments\Pages\ListAuditAssignments;
use App\Filament\Resources\AuditAssignments\Schemas\AuditAssignmentForm;
use App\Filament\Resources\AuditAssignments\Tables\AuditAssignmentsTable;
use App\Models\Application;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AuditAssignmentResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Audit Assignments';

    protected static string|UnitEnum|null $navigationGroup = 'Audit';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'id';

    public static function canAccess(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, [
            UserRole::SUPER_ADMIN,
            UserRole::AUDITOR,
        ], true);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return AuditAssignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuditAssignmentsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'puUser.businessProfile',
                'auditAssignment.auditor',
                'auditAssignment.checklists',
                'auditAssignment.nonConformities',
            ]);

        if (auth()->user()?->role === UserRole::AUDITOR) {
            return $query
                ->whereHas('auditAssignment', fn (Builder $q) => $q->where('auditor_user_id', auth()->id()))
                ->whereIn('status', [
                    ApplicationStatus::SCHEDULE_CONFIRMED,
                    ApplicationStatus::AUDIT_IN_PROGRESS,
                    ApplicationStatus::REVISION,
                    ApplicationStatus::REPORT_SUBMITTED,
                    ApplicationStatus::REPORT_REJECTED,
                ]);
        }

        return $query->whereIn('status', [
            ApplicationStatus::AUDIT_READY,
            ApplicationStatus::AUDITOR_ASSIGNED,
            ApplicationStatus::SCHEDULE_CONFIRMED,
            ApplicationStatus::AUDIT_IN_PROGRESS,
            ApplicationStatus::REVISION,
            ApplicationStatus::REPORT_SUBMITTED,
            ApplicationStatus::REPORT_REJECTED,
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditAssignments::route('/'),
        ];
    }
}
