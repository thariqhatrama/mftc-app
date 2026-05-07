<?php

namespace App\Filament\Resources\AuditAssignments;

use App\Enums\UserRole;
use App\Filament\Resources\AuditAssignments\Pages\CreateAuditAssignment;
use App\Filament\Resources\AuditAssignments\Pages\EditAuditAssignment;
use App\Filament\Resources\AuditAssignments\Pages\ListAuditAssignments;
use App\Filament\Resources\AuditAssignments\Schemas\AuditAssignmentForm;
use App\Filament\Resources\AuditAssignments\Tables\AuditAssignmentsTable;
use App\Models\AuditAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AuditAssignmentResource extends Resource
{
    protected static ?string $model = AuditAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Audit Assignments';

    protected static ?int $navigationSort = 40;

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === UserRole::SUPER_ADMIN;
    }

    public static function form(Schema $schema): Schema
    {
        return AuditAssignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuditAssignmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAuditAssignments::route('/'),
            'create' => CreateAuditAssignment::route('/create'),
            'edit' => EditAuditAssignment::route('/{record}/edit'),
        ];
    }
}
