<?php

namespace App\Filament\Resources\Certificates\Tables;

use App\Enums\CertificationLevel;
use App\Models\Certificate;
use App\Services\UploadService;
use Filament\Actions\Action;
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
                    ->searchable()
                    ->placeholder('-'),
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
                    ->icon(Heroicon::ArrowDownTray)
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
            ])
            ->toolbarActions([]);
    }
}
