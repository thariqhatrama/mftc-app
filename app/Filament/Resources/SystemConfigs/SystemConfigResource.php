<?php

namespace App\Filament\Resources\SystemConfigs;

use App\Enums\UserRole;
use App\Filament\Resources\SystemConfigs\Pages\CreateSystemConfig;
use App\Filament\Resources\SystemConfigs\Pages\EditSystemConfig;
use App\Filament\Resources\SystemConfigs\Pages\ListSystemConfigs;
use App\Filament\Resources\SystemConfigs\Schemas\SystemConfigForm;
use App\Filament\Resources\SystemConfigs\Tables\SystemConfigsTable;
use App\Models\SystemConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SystemConfigResource extends Resource
{
    protected static ?string $model = SystemConfig::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'System Config';

    protected static string|UnitEnum|null $navigationGroup = 'Konfigurasi';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->role === UserRole::SUPER_ADMIN;
    }

    public static function form(Schema $schema): Schema
    {
        return SystemConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SystemConfigsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSystemConfigs::route('/'),
            'create' => CreateSystemConfig::route('/create'),
            'edit' => EditSystemConfig::route('/{record}/edit'),
        ];
    }
}
