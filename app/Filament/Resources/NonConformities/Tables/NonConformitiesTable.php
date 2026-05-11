<?php

namespace App\Filament\Resources\NonConformities\Tables;

use App\Models\NonConformity;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class NonConformitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->limit(60)
                    ->tooltip(fn (NonConformity $record): ?string => $record->description)
                    ->wrap(),
                TextColumn::make('severity')
                    ->badge()
                    ->colors([
                        'warning' => 'minor',
                        'danger' => 'major',
                    ])
                    ->sortable(),
                TextColumn::make('corrective_action_deadline')
                    ->label('Deadline')
                    ->date()
                    ->sortable(),
                TextColumn::make('pu_correction')
                    ->label('PU Correction')
                    ->limit(40)
                    ->tooltip(fn (NonConformity $record): ?string => $record->pu_correction)
                    ->placeholder('-'),
                IconColumn::make('verified_by_auditor')
                    ->label('Verified')
                    ->boolean(),
                TextColumn::make('closed_at')
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('severity')
                    ->options([
                        'minor' => 'Minor',
                        'major' => 'Major',
                    ]),
                TernaryFilter::make('verified_by_auditor')
                    ->label('Verified'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('verifyNc')
                    ->label('Verify NC')
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (NonConformity $record): bool => filled($record->pu_correction) && ! $record->verified_by_auditor)
                    ->action(function (NonConformity $record): void {
                        $record->update([
                            'verified_by_auditor' => true,
                            'closed_at' => now(),
                        ]);

                        $openCount = $record->auditAssignment
                            ->nonConformities()
                            ->whereNull('closed_at')
                            ->count();

                        Notification::make()
                            ->title($openCount === 0
                                ? 'Semua NC sudah diverifikasi. Anda sekarang dapat submit laporan.'
                                : 'NC verified and closed')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([]);
    }
}
