<?php

namespace App\Filament\Resources\SystemConfigs\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SystemConfigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('value')
                    ->limit(80)
                    ->tooltip(fn ($record): ?string => $record->value)
                    ->searchable(),
                TextColumn::make('description')
                    ->limit(60)
                    ->tooltip(fn ($record): ?string => $record->description)
                    ->placeholder('-'),
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('key', 'asc')
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
