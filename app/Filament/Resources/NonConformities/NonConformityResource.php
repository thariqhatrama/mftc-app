<?php

namespace App\Filament\Resources\NonConformities;

use App\Enums\UserRole;
use App\Filament\Resources\NonConformities\Pages\CreateNonConformity;
use App\Filament\Resources\NonConformities\Pages\EditNonConformity;
use App\Filament\Resources\NonConformities\Pages\ListNonConformities;
use App\Filament\Resources\NonConformities\Schemas\NonConformityForm;
use App\Filament\Resources\NonConformities\Tables\NonConformitiesTable;
use App\Models\NonConformity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NonConformityResource extends Resource
{
    protected static ?string $model = NonConformity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $navigationLabel = 'Non-Conformities';

    protected static ?int $navigationSort = 51;

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === UserRole::AUDITOR;
    }

    public static function form(Schema $schema): Schema
    {
        return NonConformityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NonConformitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNonConformities::route('/'),
            'create' => CreateNonConformity::route('/create'),
            'edit' => EditNonConformity::route('/{record}/edit'),
        ];
    }
}
