<?php

namespace App\Filament\Resources\Certificates\Tables;

use App\Enums\ApplicationStatus;
use App\Enums\CertificationLevel;
use App\Enums\ScopeObject;
use App\Models\Certificate;
use App\Services\AuditLogService;
use App\Services\StatusTransitionService;
use App\Services\UploadService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CertificatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('certificate_number')
                    ->label('Certificate #')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('application.puUser.businessProfile.company_name')
                    ->label('Company')
                    ->placeholder('-'),
                TextColumn::make('application.scope')
                    ->label('Scope')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => ucwords(str_replace('_', ' ', $state instanceof ScopeObject ? $state->value : (string) $state))),
                TextColumn::make('level')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => ucwords(str_replace('_', ' ', $state instanceof CertificationLevel ? $state->value : (string) $state))),
                TextColumn::make('issued_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('valid_until')
                    ->date()
                    ->sortable()
                    ->color(fn (Certificate $record): string => $record->valid_until?->isPast() ? 'danger' : 'success'),
            ])
            ->defaultSort('issued_at', 'desc')
            ->filters([
                SelectFilter::make('level')
                    ->options(collect(CertificationLevel::cases())
                        ->mapWithKeys(fn (CertificationLevel $l) => [$l->value => ucwords(str_replace('_', ' ', $l->value))])
                        ->toArray()),
                Filter::make('expired')
                    ->label('Expired')
                    ->query(fn (Builder $query) => $query->where('valid_until', '<', now())),
            ])
            ->recordActions([
                Action::make('downloadPdf')
                    ->label('Download PDF')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('primary')
                    ->action(function (Certificate $record) {
                        if (! $record->certificate_pdf_url) {
                            Notification::make()
                                ->title('PDF tidak tersedia')
                                ->danger()
                                ->send();

                            return null;
                        }

                        return response()->redirectTo(
                            app(UploadService::class)->signedUrl($record->certificate_pdf_url, 300)
                        );
                    }),
                Action::make('failSurveillance')
                    ->label('Gagal Surveilans')
                    ->icon(Heroicon::OutlinedExclamationTriangle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Sertifikat (Gagal Surveilans)')
                    ->modalDescription('Sertifikat ini akan dicabut dan status aplikasinya menjadi Gagal Surveilans. Aksi ini tidak dapat dibatalkan.')
                    ->visible(fn (Certificate $record): bool => $record->level === CertificationLevel::THREE_STAR
                        && $record->application
                        && $record->application->status->value === ApplicationStatus::CERTIFIED->value)
                    ->schema([
                        Textarea::make('reason')
                            ->label('Alasan Kegagalan')
                            ->required(),
                    ])
                    ->action(function (Certificate $record, array $data) {
                        $application = $record->application;

                        if (! $application) {
                            return;
                        }

                        app(StatusTransitionService::class)->transition(
                            $application,
                            ApplicationStatus::SURVEILLANCE_FAILED->value,
                            auth()->user()
                        );

                        // Update certificate validation
                        $record->update([
                            'valid_until' => now()->subDay(), // Expire the certificate immediately
                        ]);

                        app(AuditLogService::class)->log(
                            action: 'surveillance_failed',
                            entityType: 'certificate',
                            entityId: $record->id,
                            oldStatus: 'certified',
                            newStatus: 'surveillance_failed',
                            actor: auth()->user(),
                        );

                        Notification::make()
                            ->title('Sertifikat dicabut')
                            ->body('Status aplikasi telah diubah menjadi Gagal Surveilans.')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([]);
    }
}
