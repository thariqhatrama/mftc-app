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

class AuditChecklistResource extends Resource
{
    protected static ?string $model = AuditChecklist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?string $navigationLabel = 'Audit Checklists';

    protected static ?int $navigationSort = 50;

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
