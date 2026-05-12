<?php

namespace App\Filament\Resources\AuditChecklists;

use App\Enums\UserRole;
use App\Filament\Resources\AuditChecklists\Pages\EditAuditChecklist;
use App\Filament\Resources\AuditChecklists\Pages\ListAuditChecklists;
use App\Filament\Resources\AuditChecklists\Schemas\AuditChecklistForm;
use App\Filament\Resources\AuditChecklists\Tables\AuditChecklistsTable;
use App\Models\AuditChecklist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AuditChecklistResource extends Resource
{
    protected static ?string $model = AuditChecklist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Audit Checklists';

    protected static string|UnitEnum|null $navigationGroup = 'Audit';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === UserRole::AUDITOR;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return AuditChecklistForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuditChecklistsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['auditAssignment.application.puUser.businessProfile', 'site'])
            ->when(request()->query('assignment_id'), fn (Builder $query, string $assignmentId) => $query->where('audit_assignment_id', $assignmentId))
            ->whereHas('auditAssignment', fn (Builder $query) => $query->where('auditor_user_id', auth()->id()));
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditChecklists::route('/'),
            'edit' => EditAuditChecklist::route('/{record}/edit'),
        ];
    }
}
