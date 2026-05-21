<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\AuditLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class AuditLogsTable extends TableWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Audit Logs Terbaru';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->role === UserRole::SUPER_ADMIN;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => AuditLog::query()->with('user'))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('System'),
                TextColumn::make('action')
                    ->badge()
                    ->searchable(),
                TextColumn::make('entity_type')
                    ->label('Entity')
                    ->searchable(),
                TextColumn::make('entity_id')
                    ->label('Entity ID')
                    ->limit(12)
                    ->tooltip(fn (AuditLog $record): ?string => $record->entity_id),
                TextColumn::make('old_status')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-'),
                TextColumn::make('new_status')
                    ->badge()
                    ->color('info')
                    ->placeholder('-'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([])
            ->toolbarActions([])
            ->paginated([5]);
    }
}
