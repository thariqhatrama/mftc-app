<?php

namespace App\Filament\Resources\NonConformities\Tables;

use App\Models\NonConformity;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
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
                EditAction::make()
                    ->modalHeading('Edit Non-Conformity')
                    ->modalWidth('lg'),
                Action::make('viewAttachment')
                    ->label('Lihat File PU')
                    ->icon(Heroicon::OutlinedPaperClip)
                    ->color('info')
                    ->visible(fn (NonConformity $record): bool => ! empty($record->pu_correction_attachment_url))
                    ->modalHeading('File Perbaikan dari PU')
                    ->modalContent(fn (NonConformity $record) => view(
                        'filament.modals.nc-attachment',
                        ['nc' => $record]
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalWidth('lg'),
                Action::make('verifyNc')
                    ->label('Verifikasi Perbaikan')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Perbaikan PU')
                    ->modalDescription('Konfirmasi bahwa perbaikan PU sudah memadai dan NC ini dapat ditutup.')
                    ->visible(fn (NonConformity $record): bool => filled($record->pu_correction) && ! $record->verified_by_auditor)
                    ->action(function (NonConformity $record): void {
                        $record->update([
                            'verified_by_auditor' => true,
                            'closed_at' => now(),
                        ]);

                        $openCount = NonConformity::where('audit_assignment_id', $record->audit_assignment_id)
                            ->where('verified_by_auditor', false)
                            ->count();

                        if ($openCount === 0) {
                            Notification::make()
                                ->success()
                                ->title('Semua NC terverifikasi! Anda dapat submit laporan.')
                                ->persistent()
                                ->send();
                        } else {
                            Notification::make()
                                ->success()
                                ->title("NC ditutup. Sisa {$openCount} NC belum diverifikasi.")
                                ->send();
                        }
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading('Tambah Non-Conformity')
                    ->modalWidth('lg')
                    ->successNotificationTitle('NC berhasil ditambahkan'),
            ])
            ->toolbarActions([]);
    }
}
